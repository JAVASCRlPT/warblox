@extends('layouts.app')

@section('title', 'Preview eBook - ' . $book->title)

@section('content')
<div class="page-header">
    <h1><i class="bi bi-book-half"></i> Preview eBook</h1>
    <p class="text-muted mb-0">Pratinjau PDF secara langsung untuk <strong>{{ $book->title }}</strong>.</p>
</div>

<div class="card mt-4">
    <div class="card-body p-0 position-relative" style="min-height: 80vh;">
        <div id="pdf-viewer" class="pdf-viewer w-100 h-100 bg-light" style="min-height: 80vh; overflow-y: auto;">
            <div id="pdf-loader" class="d-flex justify-content-center align-items-center" style="min-height: 80vh;">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        </div>
        <div class="preview-note position-absolute bottom-0 start-50 translate-middle-x mb-4 text-center text-white px-3 py-2 rounded bg-dark bg-opacity-75">
            Dua halaman pertama terlihat. Halaman selanjutnya diburamkan untuk preview.
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/pdf2.min.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const previewUrl = '{{ $previewUrl }}';
        const viewer = document.getElementById('pdf-viewer');
        const loader = document.getElementById('pdf-loader');

        viewer.addEventListener('contextmenu', function (event) {
            event.preventDefault();
        });

        pdfjsLib.GlobalWorkerOptions.workerSrc = '{{ asset('js/pdf2.worker.min.js') }}';

        (async function loadAndRender() {
            if (typeof pdfjsLib === 'undefined') {
                console.error('pdfjsLib tidak ditemukan');
                loader.innerHTML = '<div class="text-danger p-4 text-center">Viewer tidak tersedia (pdfjs tidak dimuat).</div>';
                return;
            }

            try {
                console.log('Fetching preview URL:', previewUrl);
                const response = await fetch(previewUrl, {
                    cache: 'no-store',
                    credentials: 'same-origin',
                    mode: 'same-origin',
                    headers: {
                        'X-PDF-Preview': '1',
                    },
                });

                console.log('Preview response:', response.status, response.statusText);

                if (!response.ok) {
                    if (response.status === 401 || response.status === 403) {
                        throw new Error('Akses ditolak. Silakan login dan coba lagi.');
                    }
                    let text = '';
                    try { text = await response.text(); } catch (e) { /* ignore */ }
                    throw new Error('Gagal memuat preview (HTTP ' + response.status + '). ' + (text ? text : ''));
                }

                const contentType = response.headers.get('content-type') || '';
                console.log('Content-Type:', contentType);
                if (!contentType.includes('application/pdf')) {
                    throw new Error('Response bukan file PDF. (Content-Type: ' + contentType + ')');
                }

                const buffer = await response.arrayBuffer();
                const pdf = await pdfjsLib.getDocument({ data: buffer }).promise;

                loader.remove();
                renderPDF(pdf);
            } catch (err) {
                console.error('Preview error:', err);
                const msg = (err && err.message) ? err.message : 'Terjadi kesalahan saat memuat preview.';
                loader.innerHTML = '<div class="text-danger p-4 text-center">' + msg + '<br><small>Jika masalah berlanjut, periksa console (F12) untuk detail.</small></div>' +
                    '<div class="text-center mt-3"><a href="' + '{{ route('books.show', $book) }}' + '" class="btn btn-sm btn-outline-secondary">Kembali ke detail buku</a></div>';
            }
        })();

        function renderPDF(pdf) {
            const visiblePages = 2;
            for (let pageNumber = 1; pageNumber <= pdf.numPages; pageNumber++) {
                pdf.getPage(pageNumber).then(page => {
                    const scale = 1.2;
                    const viewport = page.getViewport({ scale });
                    const pageWrapper = document.createElement('div');
                    pageWrapper.className = 'pdf-page mb-4 position-relative';
                    if (pageNumber > visiblePages) {
                        pageWrapper.classList.add('blur-page');
                    }

                    const label = document.createElement('div');
                    label.className = 'page-label position-absolute top-0 end-0 m-2 px-2 py-1 rounded text-white bg-dark bg-opacity-75';
                    label.textContent = 'Halaman ' + pageNumber;
                    pageWrapper.appendChild(label);

                    const canvas = document.createElement('canvas');
                    canvas.width = viewport.width;
                    canvas.height = viewport.height;
                    pageWrapper.appendChild(canvas);

                    const context = canvas.getContext('2d');
                    const renderContext = {
                        canvasContext: context,
                        viewport: viewport
                    };

                    page.render(renderContext).promise.catch(() => {
                        pageWrapper.innerHTML = '<div class="text-danger p-4 text-center">Gagal merender halaman ' + pageNumber + '.</div>';
                    });

                    viewer.appendChild(pageWrapper);
                });
            }
        }
    });
</script>
<style>
    .pdf-viewer {
        min-height: 80vh;
        padding: 1rem;
    }

    .pdf-page {
        background: white;
        border: 1px solid rgba(0, 0, 0, 0.08);
        border-radius: 0.5rem;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        overflow: hidden;
        max-width: 960px;
        margin: 0 auto;
    }

    .pdf-page canvas {
        display: block;
        width: 100%;
        height: auto;
    }

    .blur-page {
        filter: blur(8px);
        pointer-events: none;
    }

    .blur-page::after {
        content: 'Preview terbatas';
        position: absolute;
        bottom: 1rem;
        left: 50%;
        transform: translateX(-50%);
        padding: 0.4rem 0.8rem;
        background: rgba(0, 0, 0, 0.65);
        color: white;
        border-radius: 0.4rem;
        font-size: 0.85rem;
    }

    .page-label {
        font-size: 0.85rem;
    }
</style>
@endpush
