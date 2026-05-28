<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBookRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'publisher' => 'required|string|max:255',
            'year' => 'required|integer|min:1900|max:' . date('Y'),
            'stock' => 'required|integer|min:0',
            'loan_duration' => 'required|integer|min:1|max:365',
            'price' => 'nullable|numeric|min:0',
            'category_id' => 'required|exists:categories,id',
            'cover' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'file_ebook' => 'nullable|mimes:pdf,epub|max:10240',
            'description' => 'nullable|string',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Judul buku harus diisi',
            'author.required' => 'Penulis harus diisi',
            'publisher.required' => 'Penerbit harus diisi',
            'year.required' => 'Tahun terbit harus diisi',
            'stock.required' => 'Stok harus diisi',
            'loan_duration.required' => 'Durasi peminjaman harus diisi',
            'loan_duration.min' => 'Durasi peminjaman minimal 1 hari',
            'loan_duration.max' => 'Durasi peminjaman maksimal 365 hari',
            'category_id.required' => 'Kategori harus dipilih',
            'category_id.exists' => 'Kategori tidak ditemukan',
            'cover.image' => 'File harus berupa gambar',
            'cover.max' => 'Ukuran gambar maksimal 2MB',
        ];
    }
}
