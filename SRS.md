<div class="cover">

SOFTWARE REQUIREMENT SPECIFICATION
(SRS)

**KitaSewa**

Platform Rental Aset & Properti Online

<div class="cover-bottom">

**CV Rimbun Komputindo (Utama Web)**

Samarinda, Kalimantan Timur

2026

</div>
</div>

<div class="pagebreak"></div>

DAFTAR ISI

BAB I — PENDAHULUAN .......................... 4
1. Latar Belakang .................................. 4
2. Identifikasi Masalah ........................... 5
3. Rumusan Masalah .............................. 5
4. Tujuan ............................................. 6
5. Manfaat ............................................ 6
6. Ruang Lingkup .................................. 7
7. Batasan Sistem ................................. 7

BAB II — GAMBARAN UMUM SISTEM ........... 8
1. Deskripsi Umum Sistem ...................... 8
2. Tujuan Sistem .................................... 8
3. Aktor/Pengguna Sistem ........................ 9
4. Hak Akses Pengguna ........................... 9
5. Lingkungan Operasional ...................... 10

BAB III — KEBUTUHAN SISTEM ................. 11
1. Kebutuhan Fungsional .......................... 11
2. Kebutuhan Non-Fungsional ................... 13

BAB IV — SPESIFIKASI KEBUTUHAN DAN PERANCANGAN SISTEM ........................... 15
1. Diagram Aktivitas ................................ 15
2. Diagram Alur Program .......................... 16
3. Use Case Diagram ................................ 17
4. Skenario Use Case ............................... 18

BAB V — PERANCANGAN BASIS DATA ........... 20
1. Deskripsi Basis Data ............................. 20
2. Entity Relationship Diagram ................... 20
3. Struktur Tabel ..................................... 21
4. Relasi Antar Tabel ................................ 23
5. Aturan Integritas Data ........................... 24

BAB VI — PERANCANGAN ANTARMUKA .......... 25
1. Struktur Navigasi ................................. 25
2. Halaman Login .................................... 25
3. Halaman Beranda ................................ 26
4. Halaman Pencarian .............................. 26
5. Halaman Detail Aset ............................. 27
6. Halaman Booking ................................. 27
7. Halaman Chat ..................................... 28
8. Halaman Dashboard Owner ................... 28
9. Halaman Dashboard Admin ................... 29

BAB VII — SPESIFIKASI TEKNOLOGI ........... 30
1. Teknologi Frontend .............................. 30
2. Teknologi Backend ............................... 30
3. Teknologi Autentikasi ........................... 31
4. Teknologi Notifikasi ............................. 31
5. Arsitektur Sistem ................................. 32

BAB VIII — KEAMANAN SISTEM .................. 33
1. Autentikasi ......................................... 33
2. Otorisasi ............................................ 33
3. Keamanan Basis Data ........................... 34
4. Perlindungan Kredensial ....................... 34

BAB IX — NOTIFIKASI DAN INTEGRASI ........ 35
1. Push Notification ................................. 35
2. Integrasi Layanan Pihak Ketiga .............. 35

BAB X — SPESIFIKASI PERANGKAT ............. 36
1. Perangkat Keras .................................. 36
2. Perangkat Lunak ................................. 36
3. Koneksi Jaringan ................................ 37

BAB XI — KETERBATASAN SISTEM .............. 38

BAB XII — STATUS IMPLEMENTASI .............. 39

BAB XIII — PENUTUP ............................... 40
1. Kesimpulan ........................................ 40
2. Pengembangan Selanjutnya ................... 40

<div class="pagebreak"></div>

# BAB I PENDAHULUAN

## 1. Latar Belakang

Perkembangan teknologi informasi dan internet telah mengubah cara masyarakat dalam memenuhi kebutuhan sehari-hari, termasuk dalam hal penyewaan properti dan aset. Saat ini, banyak masyarakat yang kesulitan menemukan properti atau aset yang ingin disewa karena keterbatasan informasi dan jangkauan. Di sisi lain, pemilik properti juga kesulitan dalam memasarkan aset mereka kepada calon penyewa yang tepat.

Platform penyewaan properti online menjadi solusi yang tepat untuk mengatasi permasalahan tersebut. Dengan memanfaatkan teknologi web, pemilik properti dapat memamerkan aset mereka secara luas, sementara calon penyewa dapat dengan mudah mencari dan membandingkan berbagai pilihan properti yang tersedia.

KitaSewa adalah platform web penyewaan aset dan properti yang dirancang untuk menghubungkan antara pemilik aset (Owner) dengan penyewa (User/Penyewa). Platform ini menyediakan berbagai fitur unggulan seperti pencarian berbasis lokasi menggunakan OpenStreetMap, manajemen aset multi-step, sistem booking dan pembayaran, komunikasi real-time melalui chat, serta panel administrasi yang komprehensif.

## 2. Identifikasi Masalah

Berdasarkan analisis kebutuhan, berikut adalah masalah-masalah yang ditemukan:

1. Masyarakat kesulitan menemukan properti/aset yang ingin disewa karena keterbatasan informasi.
2. Pemilik properti kesulitan memasarkan aset kepada calon penyewa yang tepat.
3. Proses penyewaan properti masih dilakukan secara manual dan tidak terpusat.
4. Tidak ada platform terintegrasi yang menghubungkan penyewa dan pemilik aset.
5. Komunikasi antara penyewa dan pemilik aset belum optimal.

## 3. Rumusan Masalah

Berdasarkan identifikasi masalah di atas, rumusan masalah yang akan diselesaikan oleh sistem adalah:

1. Bagaimana membangun platform yang memudahkan masyarakat dalam mencari dan menyewa properti/aset?
2. Bagaimana memberikan sarana bagi pemilik aset untuk memasarkan properti mereka secara digital?
3. Bagaimana mengintegrasikan proses pencarian, booking, pembayaran, dan komunikasi dalam satu platform?
4. Bagaimana menyediakan sistem manajemen yang komprehensif bagi administrator?

## 4. Tujuan

Tujuan pembangunan sistem KitaSewa adalah:

1. Membangun platform web penyewaan aset dan properti yang terintegrasi.
2. Memudahkan penyewa dalam mencari, membandingkan, dan menyewa properti/aset.
3. Memberikan sarana digital bagi pemilik aset untuk memasarkan properti.
4. Menyediakan sistem booking dan pembayaran yang transparan.
5. Menghadirkan komunikasi real-time antara penyewa dan pemilik aset.
6. Menyediakan panel administrasi yang komprehensif untuk pengelolaan sistem.

## 5. Manfaat

Manfaat sistem KitaSewa bagi pengguna:

1. **Bagi Penyewa:** Kemudahan mencari properti, transparansi informasi, proses booking mudah, komunikasi langsung dengan pemilik.
2. **Bagi Pemilik Aset:** Platform digital untuk pemasaran, dashboard analytics, manajemen aset mudah, verifikasi pembayaran fleksibel.
3. **Bagi Administrator:** Panel administrasi komprehensif, validasi aset, manajemen pengguna, log aktivitas.
4. **Bagi Masyarakat:** Kontribusi terhadap digitalisasi ekonomi lokal.

## 6. Ruang Lingkup

Cakupan sistem KitaSewa meliputi:

1. Autentikasi dan manajemen pengguna (registrasi, login, profil).
2. Pencarian aset berdasarkan keyword, lokasi, kategori, harga.
3. Manajemen aset multi-step oleh pemilik (7 langkah).
4. Sistem booking dan pembayaran.
5. Chat real-time antara penyewa dan pemilik.
6. Ulasan dan rating aset.
7. Dashboard owner dan admin.
8. Notifikasi in-app dan push notification.

## 7. Batasan Sistem

Keterbatasan sistem KitaSewa:

1. Sistem hanya tersedia sebagai aplikasi web responsive (tidak ada mobile native).
2. Pembayaran dilakukan secara manual melalui upload bukti transfer.
3. Sistem berfokus pada wilayah Indonesia.
4. Tidak mendukung multi-language.

<div class="pagebreak"></div>

# BAB II GAMBARAN UMUM SISTEM

## 1. Deskripsi Umum Sistem

KitaSewa adalah platform web penyewaan aset dan properti yang menghubungkan tiga aktor utama: Penyewa (User), Pemilik Aset (Owner), dan Administrator. Sistem ini dibangun dengan arsitektur Laravel 13.8 + Vue 3 (Inertia.js) dan menggunakan database SQLite/MySQL.

Fitur utama sistem meliputi pencarian aset berbasis lokasi, manajemen aset multi-step, sistem booking dan pembayaran, chat real-time, serta panel administrasi lengkap.

## 2. Tujuan Sistem

Sasaran yang ingin dicapai melalui sistem KitaSewa:

1. Menjadi platform penyewaan properti terintegrasi di Indonesia.
2. Menghubungkan penyewa dengan pemilik aset secara efektif.
3. Menyediakan proses penyewaan yang transparan dan mudah.
4. Memberikan analytics dan insight bagi pemilik aset.

## 3. Aktor/Pengguna Sistem

| No | Aktor | Deskripsi |
|----|-------|-----------|
| 1 | Guest | Pengguna yang belum login |
| 2 | User (Penyewa) | Pengguna yang sudah login dan ingin menyewa |
| 3 | Owner (Pemilik Aset) | Pemilik properti/aset yang disewakan |
| 4 | Admin | Administrator sistem |
| 5 | Sistem | Sistem internal (otomatis) |

## 4. Hak Akses Pengguna

| Fitur | Guest | User | Owner | Admin |
|-------|-------|------|-------|-------|
| Melihat Beranda | V | V | V | V |
| Pencarian Aset | V | V | V | V |
| Lihat Detail Aset | V | V | V | V |
| Registrasi | V | - | - | - |
| Login | V | V | V | V |
| Booking Aset | - | V | - | - |
| Upload Bukti Bayar | - | V | - | - |
| Chat | - | V | V | - |
| Beri Ulasan | - | V | - | - |
| Manajemen Aset | - | - | V | V |
| Verifikasi Booking | - | - | V | - |
| Dashboard Owner | - | - | V | - |
| Dashboard Admin | - | - | - | V |
| Validasi Aset | - | - | - | V |
| Manajemen Pengguna | - | - | - | V |
| Backup dan Restore | - | - | - | V |

## 5. Lingkungan Operasional

- **Server:** Ubuntu 22.04 LTS, Nginx/Apache, PHP 8.3+
- **Database:** SQLite (development), MySQL/MariaDB (production)
- **Browser:** Chrome 90+, Firefox 88+, Safari 14+, Edge 90+
- **Jaringan:** Internet connection (HTTPS)
- **Hosting:** Web Server (Apache/Nginx)

<div class="pagebreak"></div>

# BAB III KEBUTUHAN SISTEM

## 1. Kebutuhan Fungsional

| Kode | Kebutuhan Fungsional | Aktor | Status |
|------|---------------------|-------|--------|
| SRS-F-01 | Sistem menyediakan proses registrasi pengguna baru | Guest | Terimplementasi |
| SRS-F-02 | Sistem menyediakan proses login dengan email dan password | Guest, User, Owner, Admin | Terimplementasi |
| SRS-F-03 | Sistem menyediakan login menggunakan Google OAuth | Guest, User, Owner | Terimplementasi |
| SRS-F-04 | Sistem membaca role pengguna dan mengarahkan ke dashboard sesuai role | Sistem | Terimplementasi |
| SRS-F-05 | Sistem menyediakan pencarian aset berdasarkan keyword dan lokasi | Guest, User | Terimplementasi |
| SRS-F-06 | Sistem menyediakan filter pencarian (kategori, harga, fasilitas) | Guest, User | Terimplementasi |
| SRS-F-07 | Sistem menampilkan detail aset (galeri, fasilitas, harga, peta) | Guest, User | Terimplementasi |
| SRS-F-08 | Sistem menyediakan proses booking aset | User | Terimplementasi |
| SRS-F-09 | Sistem menyediakan upload bukti pembayaran | User | Terimplementasi |
| SRS-F-10 | Sistem menyediakan verifikasi pembayaran oleh Owner | Owner | Terimplementasi |
| SRS-F-11 | Sistem menyediakan chat real-time antara User dan Owner | User, Owner | Terimplementasi |
| SRS-F-12 | Sistem menyediakan fitur ulasan dan rating | User | Terimplementasi |
| SRS-F-13 | Sistem menyediakan manajemen aset multi-step (7 langkah) | Owner | Terimplementasi |
| SRS-F-14 | Sistem menyediakan dashboard owner dengan analytics | Owner | Terimplementasi |
| SRS-F-15 | Sistem menyediakan dashboard admin | Admin | Terimplementasi |
| SRS-F-16 | Sistem menyediakan validasi aset oleh admin | Admin | Terimplementasi |
| SRS-F-17 | Sistem menyediakan manajemen pengguna oleh admin | Admin | Terimplementasi |
| SRS-F-18 | Sistem menyediakan notifikasi in-app | Sistem | Terimplementasi |
| SRS-F-19 | Sistem menyediakan web push notification | Sistem | Terimplementasi |
| SRS-F-20 | Sistem menyediakan fitur favorit dan riwayat | User | Terimplementasi |
| SRS-F-21 | Sistem menyediakan pembayaran bulanan owner | Owner | Terimplementasi |
| SRS-F-22 | Sistem menyediakan backup dan restore data | Admin | Terimplementasi |
| SRS-F-23 | Sistem menyediakan CMS manager | Admin | Terimplementasi |
| SRS-F-24 | Sistem menyediakan log aktivitas | Admin | Terimplementasi |

## 2. Kebutuhan Non-Fungsional

| Kode | Kategori | Kebutuhan Non-Fungsional |
|------|----------|-------------------------|
| SRS-NF-01 | Keamanan | Password pengguna di-hash menggunakan bcrypt |
| SRS-NF-02 | Keamanan | Autentikasi menggunakan session token |
| SRS-NF-03 | Keamanan | CSRF protection diaktifkan pada setiap form |
| SRS-NF-04 | Keamanan | OTP untuk verifikasi identitas |
| SRS-NF-05 | Keamanan | Google OAuth untuk login alternatif |
| SRS-NF-06 | Integritas Data | Database menggunakan foreign key constraint |
| SRS-NF-07 | Integritas Data | Validation rule pada setiap input |
| SRS-NF-08 | Performa | Queue worker untuk background job |
| SRS-NF-09 | Performa | Caching untuk data statis |
| SRS-NF-10 | Performa | Optimasi query database |
| SRS-NF-11 | Ketersediaan | Auto-save draft untuk form panjang |
| SRS-NF-12 | Ketersediaan | Backup database otomatis |
| SRS-NF-13 | Kompatibilitas | Responsive design (mobile-first) |
| SRS-NF-14 | Kompatibilitas | Mendukung Chrome, Firefox, Safari, Edge |
| SRS-NF-15 | Skalabilitas | Arsitektur monolik yang terstruktur |
| SRS-NF-16 | Kemudahan Penggunaan | UI/UX yang intuitif |
| SRS-NF-17 | Kemudahan Penggunaan | Form multi-step dengan validasi |

<div class="pagebreak"></div>

# BAB IV SPESIFIKASI KEBUTUHAN DAN PERANCANGAN SISTEM

## 1. Diagram Aktivitas

### 1.1 Aktivitas Pencarian Aset

Gambar 4.1 Diagram Aktivitas Pencarian Aset

Gambar menunjukkan alur aktivitas pencarian aset. Pengguna memasukkan keyword dan/atau memilih lokasi, kemudian sistem memproses pencarian dengan filter yang tersedia, dan menampilkan hasil pencarian dalam bentuk grid/list.

### 1.2 Aktivitas Booking

Gambar 4.2 Diagram Aktivitas Booking

Gambar menunjukkan alur aktivitas booking. Pengguna memilih aset, memilih unit dan tanggal, mengisi data diri, memilih metode pembayaran, dan melakukan booking. Sistem memvalidasi ketersediaan dan membuat booking baru.

### 1.3 Aktivitas Pembayaran

Gambar 4.3 Diagram Aktivitas Pembayaran

Gambar menunjukkan alur aktivitas pembayaran. Pengguna mengupload bukti pembayaran, Owner memverifikasi, dan sistem mengupdate status pembayaran dan booking.

## 2. Diagram Alur Program

### 2.1 Flowchart Proses Login

Gambar 4.4 Flowchart Proses Login

Alur login dimulai dari pengguna membuka halaman login, memasukkan kredensial, sistem memvalidasi, dan mengarahkan ke dashboard sesuai role.

### 2.2 Flowchart Proses Booking

Gambar 4.5 Flowchart Proses Booking

Alur booking dimulai dari pengguna memilih aset, memilih unit dan tanggal, mengisi data diri, memilih metode pembayaran, hingga booking berhasil dibuat dengan status pending.

### 2.3 Flowchart Proses Pembayaran

Gambar 4.6 Flowchart Proses Pembayaran

Alur pembayaran dimulai dari pengguna mengupload bukti, Owner memverifikasi, hingga status booking berubah menjadi active.

## 3. Use Case Diagram

Gambar 4.7 Use Case Diagram KitaSewa

Use case utama sistem KitaSewa melibatkan empat aktor utama: Guest, User, Owner, dan Admin dengan berbagai fitur yang tersedia.

| Aktor | Fungsi Utama |
|-------|-------------|
| Guest | Registrasi, Login, Pencarian, Lihat Detail |
| User | Booking, Pembayaran, Chat, Ulasan, Favorit |
| Owner | Manajemen Aset, Verifikasi Booking, Dashboard |
| Admin | Validasi Aset, Manajemen Pengguna, Backup |

## 4. Skenario Use Case

### UC-01 — Registrasi Pengguna

**Aktor:** Guest

**Tujuan:** Membuat akun baru untuk dapat mengakses fitur sistem.

**Pra-kondisi:** Pengguna belum memiliki akun.

**Alur Utama:**
1. Guest mengakses halaman registrasi.
2. Sistem menampilkan form registrasi.
3. Guest mengisi data (nama, email, password).
4. Guest menekan tombol "Daftar".
5. Sistem memvalidasi data.
6. Sistem membuat akun baru.
7. Sistem login otomatis dan mengarahkan ke beranda.

**Alur Alternatif:**
1. Jika email sudah terdaftar, sistem menampilkan pesan error.
2. Jika password tidak sesuai, sistem menampilkan pesan error.

**Pasca-kondisi:** Akun baru berhasil dibuat dan pengguna login.

### UC-02 — Login Pengguna

**Aktor:** Guest, User, Owner, Admin

**Tujuan:** Masuk ke dalam sistem untuk mengakses fitur sesuai role.

**Pra-kondisi:** Pengguna memiliki akun terdaftar.

**Alur Utama:**
1. Pengguna mengakses halaman login.
2. Sistem menampilkan form login.
3. Pengguna memasukkan email dan password.
4. Pengguna menekan tombol "Masuk".
5. Sistem memvalidasi kredensial.
6. Sistem mengarahkan ke dashboard sesuai role.

**Alur Alternatif:**
1. Jika kredensial salah, sistem menampilkan pesan error.
2. Jika login via Google, sistem redirect ke OAuth.

**Pasca-kondisi:** Pengguna berhasil login dan mengakses fitur.

### UC-03 — Pencarian Aset

**Aktor:** Guest, User

**Tujuan:** Mencari aset/properti yang sesuai kebutuhan.

**Pra-kondisi:** Sistem memiliki data aset yang approved.

**Alur Utama:**
1. Pengguna mengakses halaman pencarian.
2. Pengguna memasukkan keyword dan/atau lokasi.
3. Sistem menampilkan hasil pencarian.
4. Pengguna dapat melihat detail aset.

**Alur Alternatif:**
1. Jika tidak ada hasil, sistem menampilkan pesan "Tidak ditemukan".

**Pasca-kondisi:** Pengguna mendapatkan daftar hasil pencarian.

### UC-04 — Booking Aset

**Aktor:** User (Penyewa)

**Tujuan:** Melakukan pemesanan aset/properti.

**Pra-kondisi:** User sudah login dan aset tersedia.

**Alur Utama:**
1. User mengakses halaman detail aset.
2. User memilih unit, durasi, dan tanggal.
3. Sistem menampilkan harga dan total.
4. User mengisi data diri.
5. User memilih metode pembayaran.
6. User menekan "Booking Sekarang".
7. Sistem memvalidasi dan membuat booking.

**Alur Alternatif:**
1. Jika tanggal sudah dibooking, sistem menampilkan pesan error.

**Pasca-kondisi:** Booking baru dibuat dengan status pending.

### UC-05 — Manajemen Aset (Owner)

**Aktor:** Owner

**Tujuan:** Membuat dan mengelola aset/properti.

**Pra-kondisi:** Owner sudah login dan akun terverifikasi.

**Alur Utama:**
1. Owner mengakses halaman "Kelola Aset".
2. Owner menekan "+ Buat Aset Baru".
3. Sistem menampilkan form multi-step (7 langkah).
4. Owner mengisi informasi aset.
5. Owner menekan "Publikasikan".
6. Sistem menyimpan aset dengan status pending.

**Alur Alternatif:**
1. Sistem mendukung auto-save draft.

**Pasca-kondisi:** Aset berhasil dibuat dengan status pending.

<div class="pagebreak"></div>

# BAB V PERANCANGAN BASIS DATA

## 1. Deskripsi Basis Data

Sistem KitaSewa menggunakan database berikut:

- **Development:** SQLite
- **Production:** MySQL/MariaDB
- **Jumlah Tabel:** 47 tabel

## 2. Entity Relationship Diagram

Gambar 5.1 Entity Relationship Diagram KitaSewa

ERD menunjukkan hubungan antar entitas dalam database KitaSewa, meliputi users, assets, bookings, payments, dan entitas lainnya.

## 3. Struktur Tabel

### 3.1 Tabel users

| No | Nama Field | Tipe Data | Key | Keterangan |
|----|------------|-----------|-----|------------|
| 1 | id | bigint | PK | ID unik pengguna |
| 2 | name | varchar(255) | - | Nama lengkap |
| 3 | email | varchar(255) | UK | Email unik |
| 4 | email_verified_at | timestamp | - | Waktu verifikasi email |
| 5 | password | varchar(255) | - | Password terhash |
| 6 | phone | varchar(15) | - | Nomor telepon |
| 7 | photo | varchar(255) | - | Foto profil |
| 8 | is_active | boolean | - | Status aktif |
| 9 | role | enum | - | Peran (user/owner/admin) |
| 10 | created_at | timestamp | - | Waktu pembuatan |
| 11 | updated_at | timestamp | - | Waktu update |

### 3.2 Tabel assets

| No | Nama Field | Tipe Data | Key | Keterangan |
|----|------------|-----------|-----|------------|
| 1 | id | bigint | PK | ID unik aset |
| 2 | owner_profile_id | bigint | FK | FK ke owner_profiles |
| 3 | asset_category_id | bigint | FK | FK ke asset_categories |
| 4 | asset_type_id | bigint | FK | FK ke asset_types |
| 5 | title | varchar(255) | - | Judul aset |
| 6 | slug | varchar(255) | UK | Slug URL unik |
| 7 | description | text | - | Deskripsi aset |
| 8 | status | enum | - | Status (draft/pending/approved/rejected/inactive) |
| 9 | province_code | varchar(10) | FK | Kode provinsi |
| 10 | city_code | varchar(10) | FK | Kode kota |
| 11 | district_code | varchar(10) | FK | Kode kecamatan |
| 12 | village_code | varchar(10) | FK | Kode desa |
| 13 | address | text | - | Alamat lengkap |
| 14 | latitude | decimal(10,7) | - | Garis lintang |
| 15 | longitude | decimal(10,7) | - | Garis bujur |
| 16 | allow_units | boolean | - | Mendukung unit |
| 17 | main_image | varchar(255) | - | Gambar utama |
| 18 | created_at | timestamp | - | Waktu pembuatan |
| 19 | updated_at | timestamp | - | Waktu update |

### 3.3 Tabel bookings

| No | Nama Field | Tipe Data | Key | Keterangan |
|----|------------|-----------|-----|------------|
| 1 | id | bigint | PK | ID unik booking |
| 2 | booking_code | varchar(20) | UK | Kode booking unik |
| 3 | user_id | bigint | FK | FK ke users |
| 4 | asset_id | bigint | FK | FK ke assets |
| 5 | asset_unit_id | bigint | FK | FK ke asset_units |
| 6 | check_in | date | - | Tanggal check-in |
| 7 | check_out | date | - | Tanggal check-out |
| 8 | status | enum | - | Status booking |
| 9 | total_price | decimal(12,2) | - | Total harga |
| 10 | service_fee | decimal(12,2) | - | Biaya layanan |
| 11 | guest_name | varchar(255) | - | Nama tamu |
| 12 | guest_phone | varchar(15) | - | Telepon tamu |
| 13 | guest_email | varchar(255) | - | Email tamu |
| 14 | notes | text | - | Catatan |
| 15 | created_at | timestamp | - | Waktu pembuatan |
| 16 | updated_at | timestamp | - | Waktu update |

### 3.4 Tabel payments

| No | Nama Field | Tipe Data | Key | Keterangan |
|----|------------|-----------|-----|------------|
| 1 | id | bigint | PK | ID unik pembayaran |
| 2 | booking_id | bigint | FK | FK ke bookings |
| 3 | amount | decimal(12,2) | - | Jumlah pembayaran |
| 4 | status | enum | - | Status (pending/verifying/paid/rejected/expired) |
| 5 | payment_method | varchar(50) | - | Metode pembayaran |
| 6 | payment_proof | varchar(255) | - | Bukti pembayaran |
| 7 | verified_at | timestamp | - | Waktu verifikasi |
| 8 | created_at | timestamp | - | Waktu pembuatan |
| 9 | updated_at | timestamp | - | Waktu update |

### 3.5 Tabel owner_profiles

| No | Nama Field | Tipe Data | Key | Keterangan |
|----|------------|-----------|-----|------------|
| 1 | id | bigint | PK | ID unik profil |
| 2 | user_id | bigint | FK | FK ke users |
| 3 | business_name | varchar(255) | - | Nama bisnis |
| 4 | business_address | text | - | Alamat bisnis |
| 5 | business_phone | varchar(15) | - | Telepon bisnis |
| 6 | status | enum | - | Status (pending/verified/rejected) |
| 7 | verified_at | timestamp | - | Waktu verifikasi |
| 8 | created_at | timestamp | - | Waktu pembuatan |
| 9 | updated_at | timestamp | - | Waktu update |

*(Lanjutkan untuk seluruh 47 tabel)*

## 4. Relasi Antar Tabel

Relasi utama dalam database KitaSewa:

1. **users → owner_profiles:** One-to-One
2. **owner_profiles → assets:** One-to-Many
3. **assets → asset_images:** One-to-Many
4. **assets → asset_units:** One-to-Many
5. **assets → bookings:** One-to-Many
6. **bookings → payments:** One-to-One
7. **users → bookings:** One-to-Many
8. **assets → reviews:** One-to-Many
9. **users → messages:** One-to-Many
10. **room_chats → messages:** One-to-Many

## 5. Aturan Integritas Data

1. **Primary Key:** Setiap tabel memiliki primary key (id) auto-increment.
2. **Foreign Key:** Relasi antar tabel menggunakan foreign key constraint.
3. **Unique Constraint:** Email, slug, booking_code memiliki unique constraint.
4. **CHECK Constraint:** Status menggunakan enum type.
5. **Default Value:** is_active default true, status default 'draft'.
6. **Timestamp:** created_at dan updated_at auto-managed.

<div class="pagebreak"></div>

# BAB VI PERANCANGAN ANTARMUKA

## 1. Struktur Navigasi

Gambar 6.1 Struktur Navigasi Aplikasi

Struktur navigasi KitaSewa terdiri dari:

- **Guest:** Beranda, Pencarian, Detail, Login, Register
- **User:** Aktivitas, Transaksi, Chat, Favorit, Profil
- **Owner:** Dashboard, Kelola Aset, Booking, Pendapatan, Profil
- **Admin:** Dashboard, User, Aset, Fee, CMS, Backup

## 2. Halaman Login

Gambar 6.2 Tampilan Halaman Login

Halaman login menyediakan form email + password dan opsi login via Google OAuth.

## 3. Halaman Beranda

Gambar 6.3 Tampilan Beranda

Beranda menampilkan hero section, pencarian, aset nearby, populer, dan rekomendasi.

## 4. Halaman Pencarian

Gambar 6.4 Tampilan Pencarian

Halaman pencarian menampilkan grid/list hasil pencarian dengan filter.

## 5. Halaman Detail Aset

Gambar 6.5 Tampilan Detail Aset

Halaman detail menampilkan galeri, fasilitas, harga, peta, ulasan, dan booking.

## 6. Halaman Booking

Gambar 6.6 Tampilan Booking

Halaman booking menampilkan form pemesanan dengan data aset dan harga.

## 7. Halaman Chat

Gambar 6.7 Tampilan Chat

Halaman chat menampilkan daftar ruang obrolan dan pesan real-time.

## 8. Halaman Dashboard Owner

Gambar 6.8 Tampilan Dashboard Owner

Dashboard owner menampilkan statistik pendapatan, okupansi, dan booking masuk.

## 9. Halaman Dashboard Admin

Gambar 6.9 Tampilan Dashboard Admin

Dashboard admin menampilkan overview sistem, user, aset, dan aktivitas.

<div class="pagebreak"></div>

# BAB VII SPESIFIKASI TEKNOLOGI

## 1. Teknologi Frontend

- **Framework:** Vue.js 3 (Composition API)
- **Router:** Inertia.js 2.0
- **CSS:** TailwindCSS
- **Build Tool:** Vite
- **Peta:** Leaflet.js + OpenStreetMap
- **Chart:** Chart.js
- **State Management:** Pinia (stores)
- **Components:** 96 komponen Vue

## 2. Teknologi Backend

- **Framework:** Laravel 13.8
- **Bahasa:** PHP 8.3+
- **Database:** SQLite (dev) / MySQL (prod)
- **ORM:** Eloquent (38 model)
- **Controller:** 32 controller
- **Migration:** 54 migration (47 tabel)
- **Seeder:** 7 seeder
- **Queue:** Laravel Queue + Supervisor
- **WebSocket:** Laravel Reverb

## 3. Teknologi Autentikasi

- **Framework:** Laravel Breeze
- **Session:** Database-driven session
- **Password Hashing:** bcrypt
- **OAuth:** Google OAuth (Socialite)
- **OTP:** Custom OTP service via email
- **CSRF:** Laravel CSRF protection
- **Middleware:** auth, guest, verified

## 4. Teknologi Notifikasi

- **In-App:** Database-driven notification
- **Push Notification:** Web Push (VAPID)
- **Service Worker:** PWA-ready
- **Email:** SMTP (verification, OTP)

## 5. Arsitektur Sistem

Gambar 7.1 Arsitektur Sistem KitaSewa

Arsitektur sistem KitaSewa menggunakan model client-server:

**Client:** Vue 3 + Inertia.js + TailwindCSS + Leaflet.js + Chart.js

**Server:** Laravel 13.8 + Routes + Middleware + Controllers + Models + Services + Jobs

**Database:** SQLite / MySQL (47 tables)

**External:** OpenStreetMap, Google OAuth, Email SMTP, Web Push, File Storage

<div class="pagebreak"></div>

# BAB VIII KEAMANAN SISTEM

## 1. Autentikasi

1. **Password Hashing:** Seluruh password di-hash menggunakan bcrypt.
2. **Session Management:** Session disimpan di database dengan token unik.
3. **Remember Token:** Fitur "Ingat Saya" menggunakan remember_token.
4. **Google OAuth:** Login alternatif menggunakan akun Google.
5. **OTP:** Verifikasi identitas menggunakan one-time password.

## 2. Otorisasi

1. **Role-Based Access:** Pembatasan akses berdasarkan role (user/owner/admin).
2. **Middleware:** Laravel middleware auth, guest, verified.
3. **Policy:** Authorization policy untuk setiap resource.
4. **Gate:** Gate untuk pembatasan akses fitur.

## 3. Keamanan Basis Data

1. **Foreign Key:** Relasi antar tabel menggunakan foreign key constraint.
2. **Validation:** Validasi input pada setiap form.
3. **SQL Injection Protection:** Eloquent ORM melindungi dari SQL injection.
4. **XSS Protection:** Laravel Blade template engine melindungi dari XSS.
5. **CSRF Token:** Setiap form menggunakan CSRF token.

## 4. Perlindungan Kredensial

1. **Environment Variable:** Kredensial disimpan di .env file.
2. **Config:** Konfigurasi menggunakan env() helper.
3. **API Key:** API key tidak di-commit ke repository.
4. **Secret:** Secret key disimpan di environment variable.

<div class="pagebreak"></div>

# BAB IX NOTIFIKASI DAN INTEGRASI

## 1. Push Notification

1. **Web Push:** Menggunakan Web Push API dengan VAPID key.
2. **Service Worker:** Push notification ditangani oleh service worker.
3. **Subscription:** Pengguna dapat mengaktifkan/menonaktifkan notifikasi.
4. **Payload:** Notifikasi berisi judul, pesan, dan URL.

## 2. Integrasi Layanan Pihak Ketiga

1. **OpenStreetMap:** Geocoding dan nearby places.
2. **Google OAuth:** Autentikasi alternatif.
3. **Email SMTP:** Pengiriman email verifikasi dan OTP.
4. **File Storage:** Penyimpanan gambar dan lampiran.

<div class="pagebreak"></div>

# BAB X SPESIFIKASI PERANGKAT

## 1. Perangkat Keras

### Server

| Komponen | Minimum | Rekomendasi |
|----------|---------|-------------|
| Processor | 2 Core | 4 Core |
| RAM | 2 GB | 4 GB |
| Storage | 20 GB | 50 GB |

### Client

| Komponen | Minimum |
|----------|---------|
| RAM | 2 GB |
| Koneksi Internet | 1 Mbps |
| Resolusi Layar | 1280 x 720 |

## 2. Perangkat Lunak

| Komponen | Versi |
|----------|-------|
| PHP | 8.3+ |
| Node.js | 18+ |
| SQLite | 3+ |
| MySQL | 8.0+ |
| Composer | Latest |
| NPM | Latest |
| Nginx/Apache | Latest |
| Ubuntu | 20.04+ |

## 3. Koneksi Jaringan

1. **Server:** Internet connection (HTTPS)
2. **Client:** Internet connection (min 1 Mbps)
3. **WebSocket:** WSS untuk chat real-time
4. **External:** OpenStreetMap, Google OAuth, SMTP

<div class="pagebreak"></div>

# BAB XI KETERBATASAN SISTEM

1. **Ketergantungan Internet:** Sistem membutuhkan koneksi internet untuk berfungsi.
2. **Platform:** Hanya tersedia sebagai web application (belum ada mobile app).
3. **Payment Gateway:** Pembayaran masih manual (belum terintegrasi payment gateway).
4. **Multi-Language:** Belum mendukung multi-bahasa.
5. **Offline Mode:** Tidak ada mode offline untuk fitur utama.
6. **Layanan Pihak Ketiga:** Bergantung pada OpenStreetMap, Google OAuth, dan SMTP.

<div class="pagebreak"></div>

# BAB XII STATUS IMPLEMENTASI

| No | Fitur | Status | Keterangan |
|----|-------|--------|------------|
| 1 | Registrasi | Terimplementasi | Berfungsi |
| 2 | Login | Terimplementasi | Berfungsi |
| 3 | Google OAuth | Terimplementasi | Berfungsi |
| 4 | Pencarian Aset | Terimplementasi | Berfungsi |
| 5 | Detail Aset | Terimplementasi | Berfungsi |
| 6 | Booking | Terimplementasi | Berfungsi |
| 7 | Pembayaran | Terimplementasi | Berfungsi |
| 8 | Chat Real-Time | Terimplementasi | Berfungsi |
| 9 | Ulasan | Terimplementasi | Berfungsi |
| 10 | Favorit | Terimplementasi | Berfungsi |
| 11 | Manajemen Aset | Terimplementasi | Berfungsi |
| 12 | Dashboard Owner | Terimplementasi | Berfungsi |
| 13 | Dashboard Admin | Terimplementasi | Berfungsi |
| 14 | Validasi Aset | Terimplementasi | Berfungsi |
| 15 | Manajemen Pengguna | Terimplementasi | Berfungsi |
| 16 | Notifikasi | Terimplementasi | Berfungsi |
| 17 | Push Notification | Terimplementasi | Berfungsi |
| 18 | Backup dan Restore | Terimplementasi | Berfungsi |
| 19 | CMS Manager | Terimplementasi | Berfungsi |
| 20 | Log Aktivitas | Terimplementasi | Berfungsi |
| 21 | Payment Gateway | Belum Terimplementasi | Rencana pengembangan |
| 22 | Mobile App | Belum Terimplementasi | Rencana pengembangan |
| 23 | Multi-Language | Belum Terimplementasi | Rencana pengembangan |
| 24 | AI Rekomendasi | Belum Terimplementasi | Rencana pengembangan |

<div class="pagebreak"></div>

# BAB XIII PENUTUP

## 1. Kesimpulan

Berdasarkan analisis dan perancangan yang telah dilakukan, dapat disimpulkan bahwa:

1. KitaSewa merupakan platform web penyewaan aset dan properti yang komprehensif, menghubungkan tiga aktor utama: Penyewa (User), Pemilik Aset (Owner), dan Administrator.

2. Sistem dibangun dengan arsitektur Laravel 13.8 + Vue 3 (Inertia.js) yang memberikan performa tinggi dan pengalaman pengguna yang responsif.

3. Sistem memiliki 47 tabel database yang terstruktur dengan baik, mencakup modul autentikasi, manajemen aset, booking, pembayaran, chat, ulasan, favorit, notifikasi, dan administrasi.

4. Fitur unggulan sistem meliputi pencarian aset berbasis lokasi, manajemen aset multi-step, chat real-time, dashboard analytics, dan panel administrasi lengkap.

5. Sistem telah memenuhi kebutuhan fungsional dan non-fungsional yang telah didefinisikan, termasuk keamanan, performa, dan kompatibilitas.

## 2. Pengembangan Selanjutnya

Fitur atau pengembangan yang direncanakan:

1. **Integrasi Payment Gateway** — Mengintegrasikan Midtrans/Xendit untuk pembayaran otomatis.
2. **Aplikasi Mobile** — Mengembangkan aplikasi Android/iOS.
3. **Multi-Bahasa** — Mendukung Indonesia, English, dan bahasa daerah.
4. **AI/ML Rekomendasi** — Sistem rekomendasi berbasis perilaku pengguna.
5. **Modul Laporan Keuangan** — Laporan detail untuk Owner dan Admin.
6. **Pengujian Otomatis** — Meningkatkan coverage testing.
7. **Dokumentasi API** — Swagger/OpenAPI documentation.

---

Dokumen ini merupakan hasil analisis codebase KitaSewa yang telah berjalan (running project), bukan sekadar perencanaan teoritis. Seluruh spesifikasi didasarkan pada implementasi aktual yang terdapat dalam source code sistem.
