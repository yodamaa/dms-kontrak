# Document Management System (DMS) Kontrak
**Technical Skill Test | Versi 1.0**

Aplikasi web berbasis **Laravel 8** untuk mengelola data kontrak beserta dokumen pendukungnya secara terstruktur dengan kontrol akses berbasis peran (**Role-Based Access Control / RBAC**).

---

## 📌 Fitur Utama

### 1. Autentikasi & RBAC
- **Multi-Role**: Memisahkan hak akses antara **Staff** dan **Supervisor**.
- **Role Enforcement di Backend**: Ditegakkan menggunakan Middleware (`CheckRole`) dan Policy (`ContractPolicy`). Permintaan manipulasi via URL/API langsung akan ditolak (HTTP 403).
- **Throttling Login**: Pembatasan percobaan login (maksimal 5 kali percobaan per menit) untuk keamanan dari brute-force.
- **Quick Demo Login**: Tombol satu klik pada halaman login untuk pengujian cepat akun demo.

### 2. Manajemen Kontrak (Staff)
- **Nomor Kontrak Otomatis**: Format `INV/YYYY/MM/NNNN` dengan nomor urut yang mereset setiap bulan dan menggunakan *database transaction & lockForUpdate* untuk mencegah duplikasi pada request bersamaan (*concurrency-safe*).
- **Status Awal Otomatis**: Setiap kontrak baru otomatis berstatus `Pending`.
- **Upload Dokumen Pendukung**:
  - Validasi multi-file (1 s.d. 5 file per kontrak).
  - Format yang diizinkan: **PDF, JPG, JPEG, PNG**.
  - Ukuran maksimal: **5 MB** per file.
  - File disimpan pada *local storage* aman dengan hash acak untuk mencegah eksekusi skrip dan path traversal.
- **Edit Kontrak**: Staff hanya diizinkan mengedit kontrak berstatus `Pending`. Kontrak yang telah berstatus `Approved` atau `Rejected` otomatis terkunci dari pengeditan.

### 3. Workflow Persetujuan & Penghapusan (Supervisor)
- **Approve Kontrak**: Supervisor dapat menyetujui kontrak berstatus `Pending`. Sistem mencatat waktu persetujuan (`approved_at`, `decided_at`) dan user penyetuju (`approved_by`).
- **Reject Kontrak**: Supervisor dapat menolak kontrak berstatus `Pending` dengan mencantumkan **alasan penolakan** (`rejection_reason`). Sistem mencatat `rejected_by` dan `decided_at`.
- **Delete Kontrak**: Supervisor dapat menghapus kontrak melalui dialog konfirmasi dengan mekanisme **Soft Delete** untuk menjaga jejak audit.

### 4. Tampilan & Interaktivitas (UI/UX)
- **Bootstrap 5 & Icon**: Tampilan modern, bersih, dan responsif.
- **Dashboard Ringkasan**: Kartu statistik jumlah total kontrak, pending, disetujui, dan ditolak.
- **Filter & Search Bar**:
  - Pencarian nomor kontrak.
  - Filter status (Pending, Approved, Rejected).
  - Filter rentang tanggal aktivasi (*Date From* - *Date To*).
  - Pilihan pengurutan kolom (*created_at*, *activation_date*, *contract_number*, *status*) dengan arah ASC/DESC (*whitelist query parameter*).
  - Pilihan jumlah baris pagination (10, 25, 50 baris).
- **Detail Kontrak Tanpa Pindah Halaman**: Modal Bootstrap 5 via AJAX menampilkan detail kontrak, dokumen pendukung, dan riwayat aktivitas (*activity timeline*).
- **Preview & Unduh Dokumen**: Akses file terproteksi melalui endpoint terotorisasi dengan fitur inline preview untuk gambar dan PDF langsung di peramban.
- **Export CSV**: Ekspor seluruh atau hasil filter kontrak ke file CSV dengan format UTF-8 BOM yang kompatibel dengan Microsoft Excel.

---

## 👥 Matriks Hak Akses (RBAC)

| Tindakan | Staff | Supervisor |
|---|:---:|:---:|
| Login & Logout | ✅ | ✅ |
| Melihat Daftar Kontrak | ✅ | ✅ |
| Filter, Search, Sort, Pagination | ✅ | ✅ |
| Melihat Detail Kontrak (Modal & AJAX) | ✅ | ✅ |
| Preview & Unduh Dokumen Pendukung | ✅ | ✅ |
| Export Data Kontrak ke CSV | ✅ | ✅ |
| Membuat Kontrak Baru + Upload Dokumen | ✅ | ❌ |
| Mengedit Kontrak (Status: Pending) | ✅ | ❌ |
| Mengedit Kontrak (Status: Approved / Rejected) | ❌ | ❌ |
| Menyetujui (Approve) Kontrak | ❌ | ✅ |
| Menolak (Reject) Kontrak + Alasan | ❌ | ✅ |
| Menghapus Kontrak (Soft Delete) | ❌ | ✅ |

---

## 🔑 Kredensial Akun Demo

Seeder otomatis menyediakan dua akun pengujian sesuai spesifikasi:

| Role | Email | Password |
|---|---|---|
| **Supervisor** | `supervisor@mail.com` | `supervisor` |
| **Staff** | `staff@mail.com` | `staff123` |

---

## 🛠️ Persyaratan Sistem

- PHP: **>= 7.4.30** (Kompatibel hingga PHP 8.3)
- MySQL / MariaDB: **>= 5.7 / 10.3**
- Composer: **>= 2.x**

---

## 🚀 Panduan Instalasi & Menjalankan Aplikasi

### 1. Clone atau Ekstrak Repositori
```bash
git clone <url-repo> project-kontrak
cd project-kontrak
```

### 2. Install Dependency Composer
```bash
composer install
```

### 3. Salin Environment File
```bash
cp .env.example .env
```
*(Di Windows PowerShell: `copy .env.example .env`)*

### 4. Konfigurasi Database di `.env`
Buka file `.env` dan sesuaikan koneksi database MySQL Anda:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=dms_kontrak
DB_USERNAME=root
DB_PASSWORD=
```
> **Catatan:** Pastikan database `dms_kontrak` sudah dibuat di MySQL (`CREATE DATABASE dms_kontrak;`).

### 5. Generate Application Key
```bash
php artisan key:generate
```

### 6. Jalankan Migrasi & Seeder
```bash
php artisan migrate:fresh --seed
```
*Perintah ini akan membuat semua tabel database dan mengisi data akun demo serta contoh kontrak awal.*

### 7. Jalankan Server Lokal
```bash
php artisan serve
```
Aplikasi dapat diakses melalui browser di:
👉 **`http://localhost:8000`**

---

## 🧪 Menjalankan Automated Test (PHPUnit)

Proyek ini telah dilengkapi dengan rangkaian test komprehensif (22 test case) mencakup pengujian autentikasi, otorisasi RBAC, alur persetujuan, validasi file, dan ekspor CSV:

```bash
php artisan test
```

Hasil test mencakup:
- `AuthTest`: Pengujian login akun demo, proteksi tamu, validasi password salah, dan logout.
- `ContractTest`: Pengujian pembuatan kontrak, nomor otomatis, pembatasan edit/status, alur approve/reject supervisor, soft delete, otorisasi download/preview dokumen, dan ekspor CSV.

---

## 📁 Struktur Dokumen & File Penting

- `app/Http/Controllers/AuthController.php`: Controller autentikasi sesi dan login throttling.
- `app/Http/Controllers/ContractController.php`: Controller CRUD kontrak, filter, approve, reject, soft delete, export CSV.
- `app/Http/Controllers/ContractDocumentController.php`: Controller download dan preview dokumen terproteksi.
- `app/Http/Middleware/CheckRole.php`: Middleware otorisasi berbasis role.
- `app/Policies/ContractPolicy.php`: Policy otorisasi aksi kontrak.
- `app/Models/Contract.php`: Model kontrak dengan auto-generation nomor dan relasi Eloquent.
- `database/migrations/`: Migrasi tabel `users`, `contracts`, `contract_documents`, `contract_activities`.
- `database/seeders/`: Seeder akun demo dan data sampel awal.
- `resources/views/contracts/`: Blade view untuk daftar kontrak, modal AJAX, create, dan edit form.
- `tests/Feature/`: Automated Feature Tests.
