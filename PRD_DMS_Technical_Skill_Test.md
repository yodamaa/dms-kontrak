# PRD — Document Management System (DMS)
**Technical Skill Test | Versi 1.0 | 12 Oktober 2026**

## 1. Ringkasan Produk

Document Management System (DMS) adalah aplikasi web untuk mengelola data kontrak dan dokumen pendukungnya. Staff dapat membuat serta mengubah kontrak selama belum diputuskan. Supervisor dapat melihat kontrak, menyetujui atau menolak kontrak, dan menghapus kontrak sesuai kebutuhan operasional.

Aplikasi dibuat dengan Laravel 8, PHP 7.4.30, dan MySQL. Implementasi harus menggunakan migration dan seeder Laravel.

## 2. Tujuan

- Mengelola data kontrak dalam satu aplikasi.
- Menyediakan alur persetujuan kontrak yang jelas.
- Membatasi tindakan berdasarkan role pengguna (RBAC).
- Memudahkan pencarian, filter, sorting, dan navigasi data kontrak.
- Menyediakan akses ke detail dan dokumen pendukung tanpa berpindah halaman.

## 3. Target Pengguna

1. **Staff** — membuat kontrak dan mengedit kontrak yang belum diputuskan.
2. **Supervisor** — melihat kontrak, melakukan approve/reject, dan menghapus kontrak.

## 4. Tech Stack dan Batasan

| Bagian | Teknologi |
|---|---|
| Backend | Laravel 8 |
| Runtime PHP | PHP 7.4.30 |
| Database | MySQL |
| Frontend | Blade, Bootstrap 5, JavaScript/jQuery atau Fetch API |
| Interaksi modal | Bootstrap Modal |
| Validasi | Laravel Form Request / Validator |
| Autentikasi | Laravel session authentication |
| Authorization | Middleware dan policy/gate berbasis role |
| File storage | Laravel Storage, disk `public` atau private storage dengan endpoint terotorisasi |
| Testing | PHPUnit bawaan Laravel |

**Catatan kompatibilitas:** pilih versi dependency yang masih kompatibel dengan PHP 7.4 dan Laravel 8. Hindari memasang versi paket terbaru tanpa mengecek kebutuhan PHP/Laravel.

## 5. Ruang Lingkup MVP

### Termasuk
- Login dan logout.
- Role Staff dan Supervisor.
- Daftar kontrak dengan status.
- Search, filter, sorting, dan pagination.
- Detail kontrak di modal tanpa membuka halaman baru.
- Create kontrak oleh Staff.
- Edit kontrak oleh Staff selama status masih menunggu keputusan.
- Upload satu atau lebih dokumen pendukung PDF/JPG/JPEG/PNG.
- Approve atau reject oleh Supervisor.
- Hapus kontrak oleh Supervisor.
- Nomor kontrak otomatis.
- Migration, seeder, validasi, pesan sukses/error, dan penanganan akses.
- Audit informasi pembuat dan pemberi keputusan.

### Fitur nilai tambah jika waktu cukup
- Live refresh daftar/status setelah aksi tanpa reload penuh (AJAX).
- Riwayat aktivitas kontrak (dibuat, diubah, disetujui, ditolak, dihapus).
- Preview gambar/PDF dari modal detail.
- Dashboard ringkas jumlah kontrak per status.
- Export daftar kontrak ke CSV.

### Tidak termasuk MVP
- Tanda tangan digital.
- Integrasi email/WhatsApp.
- Multi-level approval.
- OCR atau pencarian isi dokumen.
- Integrasi penyimpanan cloud eksternal.

## 6. Role dan Matriks Hak Akses

| Aksi | Staff | Supervisor |
|---|:---:|:---:|
| Login/logout | Ya | Ya |
| Melihat daftar kontrak | Ya | Ya |
| Mencari, filter, sorting, pagination | Ya | Ya |
| Melihat detail kontrak | Ya | Ya |
| Membuat kontrak | Ya | Tidak |
| Mengedit kontrak berstatus Pending | Ya | Tidak |
| Approve kontrak | Tidak | Ya |
| Reject kontrak | Tidak | Ya |
| Menghapus kontrak | Tidak | Ya |
| Melihat informasi pembuat dan keputusan | Ya | Ya |

**Aturan penting:** pembatasan role wajib diterapkan di backend melalui middleware/policy, bukan hanya menyembunyikan tombol di UI. Permintaan langsung ke URL atau endpoint tetap harus ditolak jika role/status tidak sesuai.

## 7. Akun Demo Seeder

Seeder membuat role dan akun berikut:

| Role | Email | Password awal |
|---|---|---|
| Supervisor | `supervisor@mail.com` | `supervisor` |
| Staff | `staff@mail.com` | `staff123` |

Password harus disimpan menggunakan hashing Laravel (`Hash::make`), bukan plain text. Akun ini hanya untuk kebutuhan demo/technical test; pada deployment nyata, ganti password awal dan jangan gunakan kredensial demo.

## 8. Workflow dan Status Kontrak

Status MVP:

- **Pending** — kontrak baru dan menunggu keputusan Supervisor.
- **Approved** — kontrak disetujui Supervisor.
- **Rejected** — kontrak ditolak Supervisor.

Alur utama:

1. Staff login dan membuat kontrak.
2. Sistem membuat nomor kontrak otomatis dan menetapkan status `Pending`.
3. Staff dapat mengedit kontrak selama status masih `Pending`.
4. Supervisor membuka detail kontrak, memeriksa data dan dokumen, lalu memilih `Approve` atau `Reject`.
5. Sistem menyimpan status, waktu keputusan, dan pengguna yang mengambil keputusan.
6. Setelah status `Approved` atau `Rejected`, Staff tidak dapat mengedit kontrak.
7. Supervisor dapat menghapus kontrak. Penghapusan harus melalui konfirmasi.

**Keputusan MVP:** kontrak yang ditolak tetap terkunci agar sesuai aturan “edit sebelum di-approve/reject”. Fitur revisi/resubmit kontrak yang ditolak dapat ditambahkan sebagai nilai plus jika disepakati.

## 9. Aturan Nomor Kontrak

Nomor kontrak dibuat oleh server dan tidak dapat ditentukan atau diedit oleh pengguna.

Format yang disarankan: **`INV/YYYY/MM/NNNN`**, contoh `INV/2026/10/0001`.

- `INV` adalah prefix yang bisa diubah melalui konfigurasi.
- `YYYY/MM` menunjukkan tahun dan bulan pembuatan.
- `NNNN` adalah urutan yang dimulai dari `0001` setiap bulan.
- Nomor harus unik dan disimpan dengan unique index.
- Pembuatan nomor harus aman terhadap request bersamaan menggunakan transaksi database dan mekanisme penguncian/sequence yang sesuai.
- Jika format persis `INV/08/01` diwajibkan penguji, format dapat disesuaikan; format tersebut perlu diklarifikasi karena tahun tidak terlihat dan potensi nomor berulang perlu diatasi.

Tanggal aktivasi dan tanggal approval adalah dua hal berbeda. Tanggal aktivasi diisi Staff; tanggal approval (`approved_at`) diisi sistem ketika Supervisor memutuskan kontrak. Untuk keputusan `Rejected`, waktu keputusan tetap dicatat sebagai `decided_at`, sedangkan `approved_at` dibiarkan kosong.

## 10. Data dan Rancangan Database

### 10.1 `users`
Kolom utama:
- `id`
- `name`
- `email` (unique)
- `password`
- `role` atau relasi ke tabel roles
- `created_at`, `updated_at`

Untuk scope dua role, implementasi sederhana dapat memakai kolom `role` dengan nilai terbatas `staff` atau `supervisor`. Jika ingin RBAC yang lebih fleksibel, gunakan tabel `roles` dan relasi. Pilih satu pendekatan dan terapkan konsisten; MVP tidak perlu memasang package RBAC jika implementasi middleware/policy sudah memadai.

### 10.2 `contracts`
Kolom utama:
- `id`
- `contract_number` (unique)
- `activation_date` (date)
- `status` (pending/approved/rejected)
- `created_by` (FK ke `users.id`)
- `approved_by` (nullable FK ke `users.id`)
- `approved_at` (nullable datetime)
- `decided_at` (nullable datetime; waktu keputusan approve/reject)
- `rejected_by` (nullable FK ke `users.id`, opsional namun disarankan)
- `rejection_reason` (nullable text; diwajibkan saat reject jika fitur alasan penolakan diterapkan)
- `created_at`, `updated_at`

Tanggal aktivasi wajib diisi. Tanggal approve tidak diinput manual dan hanya ditetapkan sistem ketika approve. `created_by` berasal dari user yang sedang login, bukan dari input form.

### 10.3 `contract_documents`
Kolom utama:
- `id`
- `contract_id` (FK ke `contracts.id`)
- `original_name`
- `file_path`
- `mime_type`
- `file_size`
- `uploaded_by` (FK ke `users.id`)
- `created_at`, `updated_at`

Relasi: satu kontrak dapat memiliki beberapa dokumen pendukung. Dokumen dihapus ketika kontrak dihapus sesuai kebijakan MVP, atau memakai soft delete jika dibutuhkan untuk audit.

### 10.4 Opsional: `contract_activities`
- `id`
- `contract_id` (nullable jika kontrak telah dihapus)
- `user_id`
- `action` (created/updated/approved/rejected/deleted)
- `description` atau metadata JSON
- `created_at`

Tabel ini merupakan nilai tambah untuk audit trail.

### Indeks dan integritas
- Unique index untuk `contract_number` dan `users.email`.
- Index untuk `status`, `activation_date`, `created_by`, dan `created_at` jika relevan.
- Foreign key yang sesuai.
- Gunakan transaksi database untuk perubahan status dan pembuatan nomor kontrak.

## 11. Spesifikasi Fitur

### 11.1 Autentikasi
- User dapat login menggunakan email dan password.
- User yang belum login diarahkan ke halaman login.
- Logout mengakhiri sesi.
- Setelah login, menu dan aksi disesuaikan dengan role.

### 11.2 Daftar Kontrak
Tabel menampilkan:
- Nomor kontrak
- Tanggal aktivasi
- Status
- Created By
- Approved/Rejected By (jika ada)
- Tanggal keputusan/approval
- Tanggal dibuat
- Kolom aksi

Fitur tabel:
- Search berdasarkan nomor kontrak.
- Filter status dan rentang tanggal aktivasi.
- Sorting berdasarkan nomor, tanggal aktivasi, status, dan tanggal dibuat.
- Pagination dengan pilihan jumlah baris, misalnya 10/25/50.
- Default sorting: data terbaru lebih dahulu.
- Query parameter divalidasi dan sorting menggunakan whitelist kolom untuk mencegah SQL injection.
- Empty state saat data tidak ditemukan.

### 11.3 Detail Kontrak
- Tombol `Detail` membuka modal di halaman daftar.
- Tidak melakukan navigasi ke halaman baru.
- Menampilkan seluruh data kontrak, status, pembuat, informasi keputusan, dan daftar dokumen.
- Dokumen dapat dibuka/diunduh hanya oleh user yang telah login dan berhak.
- PDF dapat ditampilkan melalui preview browser jika didukung; gambar dapat ditampilkan sebagai preview.
- Modal menampilkan indikator loading dan pesan error jika data gagal dimuat.

### 11.4 Create Kontrak — Staff
Field:
- Tanggal aktivasi (wajib).
- Dokumen pendukung (PDF/JPG/JPEG/PNG; minimal satu file jika aturan ini dipilih untuk MVP).

Sistem mengisi otomatis:
- Nomor kontrak.
- Created By.
- Status `Pending`.
- Timestamp.

Validasi yang disarankan:
- Tanggal aktivasi harus berupa tanggal valid.
- Maksimum ukuran file: 5 MB per file.
- MIME type/ekstensi hanya PDF, JPG, JPEG, PNG.
- Maksimum jumlah dokumen: tentukan, misalnya 5 file per kontrak.
- Nama file disimpan dengan nama acak; jangan memakai nama asli sebagai path penyimpanan.
- Validasi file dilakukan di server, bukan hanya di frontend.

### 11.5 Edit Kontrak — Staff
- Hanya dapat mengedit kontrak berstatus `Pending`.
- Tanggal aktivasi dan dokumen dapat diperbarui.
- Nomor kontrak dan Created By tidak dapat diubah.
- Sistem mengecek status dan role kembali saat request diterima.
- Kontrak yang sudah Approved/Rejected mengembalikan respons penolakan (misalnya HTTP 403/422).

### 11.6 Approve — Supervisor
- Hanya Supervisor yang dapat approve kontrak berstatus `Pending`.
- Minta konfirmasi sebelum menyimpan keputusan.
- Sistem menetapkan status `Approved`, `approved_by`, `approved_at`, dan `decided_at`.
- Keputusan dan identitas user disimpan dalam transaksi.
- Aksi berulang pada kontrak yang sudah diputuskan harus ditolak.

### 11.7 Reject — Supervisor
- Hanya Supervisor yang dapat reject kontrak berstatus `Pending`.
- Disarankan menyediakan kolom alasan penolakan agar keputusan jelas.
- Sistem menetapkan status `Rejected`, `rejected_by`, dan `decided_at`; `approved_at` tetap null.
- Aksi berulang pada kontrak yang sudah diputuskan harus ditolak.

### 11.8 Delete — Supervisor
- Hanya Supervisor yang dapat menghapus kontrak.
- Konfirmasi wajib sebelum penghapusan.
- File terkait harus dibersihkan sesuai kebijakan penyimpanan.
- Untuk menjaga audit, pertimbangkan soft delete dan log aktivitas. Jika memilih hard delete untuk MVP, jelaskan perilakunya dan pastikan file terkait tidak tertinggal.

## 12. UI/UX dan Halaman

1. **Login** — email, password, tombol login, validasi pesan.
2. **Dashboard/Daftar Kontrak** — ringkasan status opsional, filter, tabel, pagination.
3. **Form Create/Edit** — field tanggal aktivasi, upload dokumen, validasi.
4. **Modal Detail** — data kontrak dan preview/link dokumen.
5. **Dialog keputusan** — konfirmasi approve/reject; alasan reject bila diterapkan.
6. **Feedback** — flash message/toast untuk berhasil/gagal, loading saat request berjalan.

Desain harus responsif untuk desktop dan layar kecil, konsisten, dan tidak menampilkan tombol yang tidak relevan dengan role.

## 13. Keamanan dan Validasi

- Hash password dengan mekanisme Laravel.
- Gunakan CSRF protection untuk form/request yang mengubah data.
- Terapkan autentikasi dan authorization pada route/controller.
- Validasi semua input di server.
- Gunakan Eloquent/Query Builder dengan parameter binding.
- Batasi upload berdasarkan MIME type, ekstensi, dan ukuran.
- Simpan file di lokasi yang tidak dapat dieksekusi sebagai script.
- Jangan mengandalkan nama file dari pengguna.
- Batasi akses file melalui endpoint terotorisasi bila dokumen bersifat privat.
- Jangan menerima `created_by`, `approved_by`, `status`, atau `contract_number` mentah dari form sebagai sumber kebenaran.
- Catat error secara aman dan jangan menampilkan stack trace pada production.
- Tambahkan throttling login jika waktu memungkinkan.

## 14. Acceptance Criteria

MVP dianggap selesai apabila:

- [ ] Aplikasi dapat dijalankan pada Laravel 8, PHP 7.4.30, dan MySQL yang kompatibel.
- [ ] Migration dapat dijalankan dari database kosong.
- [ ] Seeder membuat akun Supervisor dan Staff dengan password ter-hash.
- [ ] User dapat login/logout.
- [ ] Kedua role dapat melihat daftar kontrak dan status.
- [ ] Search, filter, sorting, dan pagination berfungsi.
- [ ] Detail kontrak muncul dalam modal tanpa pindah halaman.
- [ ] Staff dapat membuat kontrak dengan nomor otomatis dan dokumen yang valid.
- [ ] Staff dapat mengedit kontrak Pending, tetapi tidak dapat mengedit kontrak yang sudah diputuskan.
- [ ] Supervisor dapat approve/reject kontrak Pending.
- [ ] Waktu dan user pengambil keputusan tercatat dengan benar.
- [ ] Supervisor dapat menghapus kontrak melalui konfirmasi.
- [ ] Role yang tidak berhak tidak dapat melakukan aksi melalui URL/request langsung.
- [ ] File invalid/terlalu besar ditolak dengan pesan yang jelas.
- [ ] README berisi langkah instalasi, konfigurasi `.env`, migrate/seed, dan akun demo.
- [ ] Source code dapat dikemas menjadi file ZIP tanpa folder `vendor`, `node_modules`, cache, atau secret yang tidak diperlukan; sertakan `.env.example`.

## 15. Rencana Pengerjaan 7 Hari

| Hari | Fokus | Hasil |
|---|---|---|
| 1 | Setup Laravel, konfigurasi MySQL, desain tabel, migration dan seeder | Project berjalan, akun demo tersedia |
| 2 | Login, role/middleware/policy, layout dasar | Autentikasi dan RBAC berfungsi |
| 3 | CRUD create/edit, nomor kontrak, upload dokumen | Staff dapat mengelola kontrak Pending |
| 4 | Approve/reject/delete dan aturan transisi status | Workflow Supervisor selesai |
| 5 | Tabel, search, filter, sorting, pagination, modal detail | UI utama lengkap |
| 6 | Validasi keamanan, testing manual/otomatis, perbaikan bug | Acceptance criteria diuji |
| 7 | README, data demo, final regression test, packaging ZIP | Source code siap dikirim |

**Prioritas jika waktu menipis:** selesaikan autentikasi/RBAC, data kontrak dan nomor otomatis, workflow approval, upload aman, serta daftar/detail terlebih dahulu. Live update, dashboard, activity log, dan export adalah fitur tambahan setelah MVP stabil.

## 16. Strategi Testing

Skenario minimum:
1. Login dengan masing-masing akun demo.
2. Staff membuat kontrak; nomor dan pembuat terisi otomatis.
3. Upload PDF dan gambar valid berhasil; file tidak valid atau terlalu besar ditolak.
4. Staff mengedit kontrak Pending berhasil.
5. Staff mencoba mengedit kontrak Approved/Rejected ditolak.
6. Staff mencoba approve/delete melalui request langsung ditolak.
7. Supervisor approve kontrak Pending; status, user, dan waktu tersimpan.
8. Supervisor reject kontrak Pending; alasan dan waktu keputusan tersimpan bila fitur alasan aktif.
9. Kontrak yang sudah diputuskan tidak dapat diputuskan kembali.
10. Filter, sorting, search, dan pagination bekerja bersama.
11. Detail modal menampilkan data dan dokumen yang tepat.
12. Penghapusan kontrak menangani file terkait sesuai kebijakan.
13. Nomor kontrak tetap unik ketika beberapa request dibuat berdekatan.

## 17. Definisi Selesai dan Pengiriman

Artefak yang dikirim:
- Source code Laravel.
- Migration dan seeder.
- `.env.example` tanpa credential rahasia.
- `README.md` untuk instalasi dan menjalankan aplikasi.
- File SQL tidak wajib jika migration/seed sudah mencukupi, kecuali diminta penguji.
- File ZIP bersih dan siap diekstrak.

Sebelum ZIP dibuat, jalankan migration dan seeder dari database kosong, lakukan smoke test menggunakan kedua akun demo, dan pastikan tidak ada password produksi, token, atau file `.env` rahasia yang ikut terkirim.

---
**Catatan keputusan:** format nomor kontrak, apakah dokumen wajib minimal satu, apakah kontrak Rejected dapat direvisi/resubmit, serta hard delete vs soft delete perlu dipastikan. PRD ini memilih default yang sederhana dan aman untuk menyelesaikan technical test dalam 7 hari.
