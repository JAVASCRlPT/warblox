# 📚 Perpustakaan Kampus Online

Aplikasi web perpustakaan kampus modern dengan fitur manajemen buku, sistem peminjaman otomatis, dan dashboard interaktif.

## ✨ Fitur Utama

### 1. **Authentication & Authorization**
- ✅ Login dan Register dengan validasi email unik
- ✅ Password hashing otomatis (Laravel bcrypt)
- ✅ Middleware authentikasi untuk protected routes
- ✅ Role-based access control (Admin & Mahasiswa)
- ✅ Persistent session management

### 2. **Role Management & Authorization**

#### Admin Panel:
- ✅ CRUD Buku (Create, Read, Update, Delete) dengan cover image upload
- ✅ CRUD Kategori Buku
- ✅ Kelola User (Mahasiswa & Admin) dengan NIM validation
- ✅ Dashboard Admin dengan statistik lengkap
- ✅ Lihat semua transaksi (peminjaman fisik & eBook)
- ✅ Approve/Reject individual borrow requests
- ✅ **Approve/Reject ALL borrow requests untuk satu mahasiswa sekaligus**
- ✅ Manajemen denda dan pembayaran denda
- ✅ Tracking revenue eBook
- ✅ Admin dapat membayarkan denda atas nama mahasiswa

#### Mahasiswa:
- ✅ Lihat katalog buku lengkap dengan cover image
- ✅ Cari buku real-time (judul, penulis, kategori)
- ✅ Filter buku berdasarkan kategori
- ✅ **Beli eBook (single purchase atau batch via cart)**
- ✅ **Tambah eBook ke keranjang dengan qty adjustment**
- ✅ **Checkout keranjang eBook dengan QRIS QR Code**
- ✅ **Konfirmasi pembayaran eBook dan auto-download**
- ✅ **Download eBook yang sudah dibayar**
- ✅ Pinjam buku fisik dengan request approval
- ✅ Kembalikan buku dengan approval (pending_return)
- ✅ Lihat riwayat peminjaman lengkap
- ✅ Dashboard dengan notifikasi tenggat pengembalian
- ✅ Tracking denda dan history pembayaran

### 3. **Manajemen Buku**
- ✅ Database lengkap: judul, penulis, penerbit, tahun, ISBN, stok, harga
- ✅ Kategori buku (relasi many-to-many)
- ✅ Upload cover buku dengan image storage terstruktur
- ✅ **Upload file eBook (PDF/EPUB) dengan storage management**
- ✅ **Set harga eBook untuk sistem penjualan**
- ✅ Validasi stok berkurang/bertambah otomatis
- ✅ Low stock alert di dashboard admin
- ✅ Soft delete support untuk archived books

### 4. **Sistem Peminjaman Buku Fisik**
- ✅ Tabel transaksi terstruktur dengan relasi ForeignKey
- ✅ Status: pending_borrow, dipinjam, pending_return, kembali, terlambat
- ✅ Validasi stok otomatis saat peminjaman (stock > 0)
- ✅ **Grouped pending borrow requests di admin dashboard (by user)**
- ✅ Update stok saat approval/rejection peminjaman
- ✅ Update stok saat pengembalian buku
- ✅ Tracking keterlambatan dengan Carbon
- ✅ Approval workflow untuk security

### 5. **Sistem Penjualan eBook**
- ✅ **Pembelian eBook single dengan QRIS QR Code**
- ✅ **Cart untuk multiple eBook purchases**
- ✅ **Bulk checkout dengan satu QR Code untuk semua items**
- ✅ **Quantity management di keranjang**
- ✅ **QR Code generation dengan secure format**
- ✅ **Auto-expiry QR Code (5 minutes)**
- ✅ **Refresh QR Code functionality**
- ✅ **Konfirmasi pembayaran dengan status tracking (pending → paid)**
- ✅ **eBook download history tracking**
- ✅ **Revenue tracking per transaction dan summary**
- ✅ **Invoice code generation untuk setiap transaksi**
- ✅ Auto cart cleanup setelah successful payment

### 6. **Sistem Denda Otomatis**
- ✅ Perhitungan keterlambatan otomatis dengan Carbon
- ✅ Denda per hari: **Rp 5.000** (configurable di logic)
- ✅ Auto-update status menjadi "terlambat" jika melewati due_date
- ✅ Admin dapat membayarkan denda atas nama mahasiswa
- ✅ Tracking denda per transaksi
- ✅ Dashboard statistik total denda & users dengan denda

### 7. **Search & Filter**
- ✅ Search real-time multi-field (judul, penulis, kategori)
- ✅ Filter berdasarkan kategori buku
- ✅ Pagination otomatis untuk list view
- ✅ Search pada transaksi admin (by nama, NIM, email)
- ✅ Filter transaksi by fine status (denda/lunas)

### 8. **Dashboard System**

#### Admin Dashboard:
- ✅ Total buku, mahasiswa, peminjaman, kategori
- ✅ Statistik: buku dipinjam, terlambat, pending confirmations
- ✅ **Alert denda & total revenue**
- ✅ **Pending borrow requests grouped by mahasiswa**
- ✅ **Pending return requests with approval/rejection actions**
- ✅ Low stock alert (< 5 copy)
- ✅ Quick actions untuk approve/reject
- ✅ **Bulk approve/reject all borrows per user dengan confirmation dialog**
- ✅ Transaction history table (physical & eBook)
- ✅ eBook revenue summary

#### Mahasiswa Dashboard:
- ✅ Buku yang sedang dipinjam dengan due date countdown
- ✅ Notifikasi tenggat pengembalian (color-coded: hijau/kuning/merah)
- ✅ Riwayat peminjaman lengkap dengan status tracking
- ✅ Total denda yang harus dibayar
- ✅ **Section riwayat pembelian eBook**
- ✅ **Download links untuk eBook yang sudah dibayar**

### 9. **UI/UX Modern & Responsive**
- ✅ Bootstrap 5 responsive design
- ✅ Gradient navbar dengan floating effect on scroll
- ✅ Sidebar navigation untuk admin panel
- ✅ Card-based layout dengan visual hierarchy
- ✅ SweetAlert2 confirmation dialogs untuk action-action penting
- ✅ Toast notifications untuk user feedback
- ✅ Mobile-optimized navbar dengan toggle
- ✅ **Dropdown cart preview dengan cart count badge**
- ✅ **Floating badge di keranjang icon**
- ✅ Bootstrap Icons integration (50+ icons)
- ✅ WCAG compliant, keyboard navigation support

### 10. **Database Structure & Relations**
```
Tables:
- users (id, name, nim, email, password, role)
- categories (id, name, description)
- books (id, title, author, publisher, year, isbn, stock, price, file_ebook, cover_image, category_id)
- transactions (id, user_id, book_id, borrow_date, due_date, return_date, status, fine)
- ebook_transactions (id, user_id, book_id, invoice_code, checkout_id, qr_code, amount, qty, status, expires_at)
- cart (id, user_id, book_id, qty)

Relations:
- User hasMany Transactions
- User hasMany EbookTransactions
- User hasMany Cart
- Book hasMany Transactions
- Book hasMany EbookTransactions
- Category hasMany Books
- Cart belongsTo User & Book
```

### 11. **Security Features**
- ✅ Password hashing bcrypt
- ✅ CSRF token protection di setiap form
- ✅ Middleware authentication untuk protected routes
- ✅ Role-based middleware (is_admin)
- ✅ Policy-based authorization
- ✅ SQL injection protection (Laravel ORM/prepared statements)
- ✅ Input validation dengan FormRequest classes
- ✅ QRIS payload encoding untuk secure payment flow
- ✅ QR Code expiry validation (5 minutes)
- ✅ User dapat hanya akses data pribadi mereka

### 12. **API/AJAX Features**
- ✅ **AJAX approve/reject borrow requests tanpa page reload**
- ✅ **AJAX approve/reject ALL borrows dengan SweetAlert confirmation**
- ✅ **AJAX add/remove cart items**
- ✅ **AJAX refresh QR Code untuk checkout**
- ✅ **AJAX payment confirmation**
- ✅ JSON response handling dengan error management
- ✅ Dynamic table updates setelah action

### 13. **File Management**
- ✅ Cover buku storage di `storage/app/public/covers/`
- ✅ **eBook file storage di `storage/app/private/ebooks/`**
- ✅ **QR Code SVG storage di `storage/app/public/ebook_qr/`**
- ✅ Symbolic link otomatis dengan `php artisan storage:link`
- ✅ Auto delete file saat update/delete buku
- ✅ Secure eBook download dengan authentication check
- ✅ File validation (image max 2MB, eBook allowed formats)

### 14. **Validasi Input**
- ✅ Form Request Laravel untuk setiap action critical
- ✅ Validasi file upload (image max 2MB, eBook format validation)
- ✅ Validasi unique email, NIM
- ✅ Validasi relasi foreign keys
- ✅ Validasi qty dalam cart (min 1)
- ✅ Validasi stok cukup sebelum approval
- ✅ CSRF protection otomatis di semua forms

### 15. **Admin Transaction Management**
- ✅ List view semua transaksi (physical & eBook) dengan pagination
- ✅ Search mahasiswa by name/NIM/email
- ✅ Filter by fine status (denda/lunas)
- ✅ **Detailed view per mahasiswa dengan grouped transactions**
- ✅ **Separate section untuk eBook purchases (paid status)**
- ✅ Quick action buttons (approve, reject, approve return, reject return)
- ✅ **Mark attendance/approve fine payments**
- ✅ Status badge color-coding untuk visual clarity
- ✅ Statistics panel (total, borrowed, pending, late, revenue)

### 16. **Struktur Kode Clean & Maintainable**
- ✅ Controllers terpisah per modul (BookController, TransactionController, EbookController, DashboardController)
- ✅ FormRequest classes untuk validation logic separation
- ✅ Models dengan relationships & query scopes
- ✅ Blade templates dengan layout reusability
- ✅ Service-like logic dalam controllers
- ✅ Seeders untuk dummy data dan testing
- ✅ Factory classes untuk test data generation
- ✅ Proper naming conventions (camelCase, snake_case)
- ✅ Comments dan documentation

### 17. **Bonus & Special Features**
- ✅ Pagination di semua list views
- ✅ SweetAlert2 confirmation untuk destructive actions
- ✅ Toast notifications (success/error/info)
- ✅ Dummy data: 20+ buku, 15+ mahasiswa, 30+ transactions
- ✅ Factory & Seeder untuk automated testing
- ✅ QR Code SVG generation untuk payment
- ✅ **Carbon date calculations untuk due dates & fines**
- ✅ **Transaction grouping & aggregation**
- ✅ **Numeric formatting untuk currency display (Rp)**
- ✅ **Responsive image handling dengan storage**


## 🚀 Quick Start

### Prerequisites
- PHP 8.1+
- MySQL/MariaDB
- Composer
- Node.js (optional, untuk asset compilation)

### Installation

1. **Clone & Setup**
```bash
cd c:\laragon\www\webpustaka
composer install
cp .env.example .env
php artisan key:generate
```

2. **Database Setup**
```bash
php artisan migrate:fresh --seed
php artisan storage:link
```

3. **Run Server**
```bash
php artisan serve
# Akses: http://127.0.0.1:8000
```

### Demo Credentials

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@perpustakaan.test | password |
| Mahasiswa | budi@student.test | password |

## 📁 Project Structure

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── AuthController.php (Login/Register)
│   │   ├── BookController.php (CRUD buku, search, filter)
│   │   ├── CategoryController.php (CRUD kategori)
│   │   ├── TransactionController.php (Peminjaman, approval, return, fine)
│   │   ├── EbookController.php (Cart, checkout, payment, download)
│   │   ├── DashboardController.php (Admin & Mahasiswa dashboard)
│   │   ├── AdminUserController.php (User management)
│   │   └── Auth/
│   │       ├── AuthenticatedSessionController.php
│   │       └── RegisteredUserController.php
│   ├── Requests/
│   │   ├── StoreBookRequest.php (Validation: title, author, stock, price, etc)
│   │   ├── StoreCategoryRequest.php
│   │   ├── StoreUserRequest.php (Validation: NIM unique, email)
│   │   └── Auth/
│   │       ├── LoginRequest.php
│   │       └── RegisterRequest.php
│   └── Middleware/
│       ├── IsAdmin.php (Check admin role)
│       ├── RoleMiddleware.php (Generic role check)
│       └── Authenticate.php (Built-in Laravel)
├── Models/
│   ├── User.php (hasMany transactions, carts, ebookTransactions)
│   ├── Book.php (hasMany transactions, ebookTransactions; belongsTo category)
│   ├── Category.php (hasMany books)
│   ├── Transaction.php (belongsTo user, book; track physical book borrowing)
│   ├── EbookTransaction.php (belongsTo user, book; track eBook purchases)
│   └── Cart.php (belongsTo user, book; temporary ebook cart)
└── Policies/
    └── AdminPolicy.php (Authorization policies)

database/
├── migrations/
│   ├── create_users_table.php (role, nim, email, password)
│   ├── create_categories_table.php
│   ├── create_books_table.php (price, file_ebook, cover_image, category_id)
│   ├── create_transactions_table.php (borrow_date, due_date, return_date, status, fine)
│   ├── create_ebook_transactions_table.php (invoice_code, checkout_id, qr_code, amount, qty, status, expires_at)
│   └── create_cart_table.php (user_id, book_id, qty)
├── factories/
│   ├── UserFactory.php
│   ├── BookFactory.php
│   ├── CategoryFactory.php
│   ├── TransactionFactory.php
│   └── CartFactory.php
└── seeders/
    ├── DatabaseSeeder.php (Main seeder)
    ├── UserSeeder.php (Admin + 15 Mahasiswa)
    ├── CategorySeeder.php (8 Categories)
    ├── BookSeeder.php (20+ Books with cover & eBook file)
    ├── TransactionSeeder.php (Sample transactions various statuses)
    └── CartSeeder.php (Sample cart items for demo)

resources/views/
├── layouts/
│   └── app.blade.php (Master layout with navbar, sidebar for admin)
├── auth/
│   ├── login.blade.php
│   ├── register.blade.php
│   └── forgot-password.blade.php
├── books/
│   ├── index.blade.php (Search, filter, pagination)
│   ├── show.blade.php (Book detail, borrow button, buy eBook button)
│   ├── create.blade.php (Admin)
│   └── edit.blade.php (Admin)
├── categories/
│   ├── index.blade.php (Admin list)
│   ├── create.blade.php (Admin)
│   └── edit.blade.php (Admin)
├── admin/
│   ├── dashboard.blade.php (Comprehensive admin stats & pending confirmations)
│   ├── books/
│   │   └── index.blade.php
│   ├── users/
│   │   ├── index.blade.php
│   │   ├── create.blade.php
│   │   └── edit.blade.php
│   └── transactions/
│       ├── index.blade.php (Search, filter, eBook & physical transactions)
│       └── show.blade.php (Per user detail view)
├── transactions/
│   └── history.blade.php (Mahasiswa borrow history)
├── ebook/
│   ├── cart.blade.php (Cart items with qty adjust & remove)
│   ├── checkout.blade.php (QR Code display, pending & paid history)
│   ├── buy.blade.php (Single eBook purchase QR Code)
│   └── download.blade.php (eBook management & download links)
├── dashboard.blade.php (Mahasiswa dashboard)
└── emails/
    └── verification.blade.php (Email verification template)

routes/
├── web.php (Main routes)
├── auth.php (Auth routes - Laravel Breeze)
└── api.php (Optional API routes)

storage/
├── app/
│   ├── public/
│   │   ├── covers/ (Book cover images)
│   │   └── ebook_qr/ (Generated QR codes)
│   └── private/
│       └── ebooks/ (eBook files - protected)
└── logs/
    └── laravel.log

config/
├── app.php
├── auth.php
├── database.php
├── filesystems.php (Storage config)
├── mail.php
├── queue.php
├── session.php
└── services.php (3rd party services)

tests/
├── Feature/
│   ├── BookTest.php
│   ├── TransactionTest.php
│   └── EbookTest.php
└── Unit/
    └── UserTest.php
```

## 🔑 Key Features Deep Dive

### Sistem Peminjaman Buku Fisik
- Durasi default: **7 hari**
- Denda keterlambatan: **Rp 5.000/hari** (dapat dikonfigurasi)
- Workflow: Mahasiswa request → Admin approve/reject → Status dipinjam → Mahasiswa return request → Admin approve/reject return
- Validasi stok otomatis saat approval
- Update status otomatis saat kembali
- Tracking keterlambatan dengan Carbon
- **Admin dapat approve/reject semua pending borrows satu mahasiswa sekaligus dengan 1 klik**

### Sistem Penjualan eBook
```php
// Single eBook purchase
GET /ebook/beli/{bookId}
POST /ebook/beli/{bookId} → Generates QRIS QR Code → Redirects to buy page

// Add to cart
POST /ebook/cart/{bookId} → Redirect with toast
DELETE /ebook/cart/{bookId}

// Checkout cart
GET /ebook/checkout → Show cart items + pending transactions + paid history
POST /ebook/checkout → Generate QRIS for all cart items → Redirect to checkout view

// Payment confirmation
POST /ebook/confirm/{checkoutId} → Update status pending → paid → Clear cart → Redirect to dashboard

// Download eBook
GET /ebook/download/{bookId} → Check payment status → Serve PDF/EPUB file
```

Features:
- Unique invoice codes per transaction
- QR Code generation with secure payload
- Auto-expiry after 5 minutes
- Refresh QR functionality untuk expired codes
- Download tracking untuk purchased eBooks
- Revenue tracking per transaction & summary

### Admin Transaction Management
- Full list view dengan search & filter
- Grouped view per mahasiswa
- Separate sections untuk:
  - Pending borrow requests (dengan bulk approve/reject)
  - Pending return requests (dengan approve/reject individual)
  - Borrowed books (ongoing)
  - Returned books (history)
  - Late books (with fines)
  - eBook purchases (with download status)
- Quick action buttons dengan confirmation dialogs
- Fine payment management
- Statistics dashboard

### Search & Filter Implementation
```php
// Search across multiple fields
$books = Book::where('title', 'like', "%{$search}%")
            ->orWhere('author', 'like', "%{$search}%")
            ->get();

// Filter transactions
if ($fineStatus === 'denda') {
    $query->where('fine', '>', 0);
}
```


### Authorization & Access Control
```php
// Check admin role
if (auth()->user()->isAdmin()) {
    // Tampilkan admin panel & features
}

// Check mahasiswa role
if (auth()->user()->isMahasiswa()) {
    // Tampilkan mahasiswa features
}

// Route middleware protection
Route::middleware(['auth', 'is_admin'])->group(function () {
    // Admin-only routes
});

// Policy-based authorization
$this->authorize('admin'); // Throws 403 if not admin

// Data access control
public function show(User $user) {
    // Mahasiswa hanya bisa lihat transaksi mereka sendiri
    if (!auth()->user()->isAdmin() && auth()->id() !== $user->id) {
        abort(403, 'Unauthorized');
    }
}
```

## 🎨 Design & UX

- **Color Scheme**: Professional blue (#3498db) & dark gray (#2c3e50)
- **Layout**: Responsive grid dengan sidebar untuk admin
- **Components**: Card-based, modal, pagination
- **Icons**: Bootstrap Icons (25+ icons)
- **Notifications**: SweetAlert2 notifications
- **Accessibility**: WCAG compliant, keyboard navigation

## 🧪 Testing

Database sudah pre-populated dengan:
- ✅ 1 Admin user
- ✅ 5 Mahasiswa sample + 10 random
- ✅ 8 Kategori buku
- ✅ 20 Buku dengan relasi kategori
- ✅ 6 Sample transactions dengan berbagai status

Coba fitur:
1. Login sebagai admin → Akses admin panel
2. Login sebagai mahasiswa → Pinjam buku
3. Lihat riwayat peminjaman → Status & denda
4. Search & filter buku → Test pencarian

## 📝 Important Notes & Implementation Details

### Buku Fisik (Peminjaman)
- Setiap peminjaman otomatis mengurangi stok buku saat approval
- Pengembalian otomatis menambah stok buku saat return approval
- Keterlambatan otomatis dihitung dari `due_date` ke `return_date`
- Denda dihitung saat return approval jika ada keterlambatan
- Approval workflow: pending_borrow → dipinjam (approved) atau rejected
- Return workflow: pending_return (user request) → kembali (approved) atau rejected
- Admin dapat reject dengan alasan, mahasiswa akan notified

### eBook (Penjualan Digital)
- Setiap transaksi mendapat invoice code & checkout ID unik
- QR Code auto-expired setelah 5 menit
- Admin dapat refresh QR jika expired (extend 5 min lagi)
- Payment status: pending → paid → dapat didownload
- File eBook disimpan di `storage/app/private/ebooks/` (protected)
- Download hanya tersedia untuk user yang sudah membayar
- Cart items otomatis dihapus setelah successful payment
- Revenue tracking otomatis per transaksi dan summary di dashboard

### Admin Dashboard
- Statistik: total buku, mahasiswa, peminjaman, kategori, pending confirmations
- Pending borrow requests **grouped by mahasiswa** untuk review mudah
- Alert denda & low stock dengan visual highlighting
- Quick actions dengan AJAX (approve/reject) tanpa reload
- **Bulk approve/reject ALL pending borrows satu mahasiswa sekaligus**
- eBook revenue summary & transaction history

### Mahasiswa Dashboard  
- Countdown due date dengan color-coding (hijau/kuning/merah)
- eBook purchases dengan download status & links
- Transaksi history lengkap dengan search & filter
- Total outstanding denda

### Data Privacy & Security
- Mahasiswa hanya bisa lihat data mereka sendiri (enforced via authorization)
- eBook files dilindungi dengan authentication check
- QR Code payload encrypted dengan timestamp
- Password hashed dengan bcrypt (Laravel default)
- CSRF token di semua state-changing requests
- SQL injection protection via ORM & prepared statements

### Performa & Optimization
- Query optimization: eager loading dengan `with()` untuk N+1 prevention
- Pagination di semua list views (15-20 items per page)
- Caching dimungkinkan untuk dashboard stats (future improvement)
- Image optimization untuk cover uploads
- QR Code generated on-demand, cached di storage

### Testing & Demo
- Database pre-populated dengan dummy data:
  - 1 Admin user (admin@perpustakaan.test / password)
  - 15 Mahasiswa sample (budi@student.test / password, dll)
  - 20+ Buku dengan cover image & file eBook
  - 8 Kategori buku
  - 30+ Sample transactions (various statuses & fines)
  - 10 Sample cart items
- Factory & Seeder untuk automated testing
- Sample QR Code URLs untuk testing payment flow

### Future Enhancement Ideas
- [ ] Invoice PDF generation & email
- [ ] SMS notification untuk keterlambatan
- [ ] Real QRIS payment gateway integration
- [ ] eBook preview (limited pages)
- [ ] Wishlist feature
- [ ] Book review & rating
- [ ] Reservation system untuk buku populer
- [ ] Analytics dashboard (usage patterns, most borrowed, etc)
- [ ] Multi-language support (EN/ID)
- [ ] API endpoint untuk mobile app
- Cover image disimpan di `storage/app/public/covers/`
- Semua password di-hash dengan bcrypt otomatis

## 🔒 Security Features

- ✅ CSRF protection di setiap form
- ✅ Password hashing bcrypt
- ✅ SQL injection protection
- ✅ Authorization middleware
- ✅ Input validation di Form Request
- ✅ Secure file upload dengan unique names

## 🚀 Performance Tips

- Pagination default: 12 (public), 15 (admin)
- Eager loading relasi dengan `with()`
- Index di foreign keys otomatis
- Image optimization untuk cover

## 📞 Support

Untuk development lebih lanjut:
- Config denda di controller atau env
- Tambah dashboard filter (tanggal range)
- Export PDF laporan
- Email notification untuk tenggat
- SMS alert untuk mahasiswa

---

**Created with ❤️ using Laravel + Bootstrap 5**
