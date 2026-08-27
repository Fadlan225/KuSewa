# Software Requirements Specification (SRS)
## KitaSewa — Platform Rental Aset & Properti Online

**Dokumen:** Spesifikasi Kebutuhan Perangkat Lunak
**Penyusun:** Tim PKL — CV Rimbun Komputindo (Utama Web)
**Institusi:** SMK TI Airlangga — Kelas XII PPLG — Semester 1
**Tanggal:** 27 Agustus 2026
**Versi:** 1.0 (Draft)

---

## 1. Pendahuluan

### 1.1 Tujuan
Dokumen ini menyajikan spesifikasi kebutuhan perangkat lunak untuk **KitaSewa**, sebuah platform web penyewaan aset/properti (kost, villa, gedung, alat, dll.) yang menghubungkan **Penyewa (User)**, **Pemilik Aset (Owner)**, dan **Administrator**. Tujuannya adalah mendokumentasikan kebutuhan fungsional dan non-fungsional sebagai acuan pengembangan dan pengujian.

### 1.2 Lingkup Produk
KitaSewa adalah aplikasi web monolik berbasis Laravel dengan antarmuka Vue 3 (Inertia.js). Sistem menyediakan:
- Pencarian & penjelajahan aset berbasis lokasi (OpenStreetMap/Leaflet).
- Manajemen aset dan unit oleh Owner (dengan draft, auto-save, galeri, fasilitas, FAQ, kebijakan).
- Alur pemesanan (booking) dan pembayaran (termasuk iuran bulanan Owner).
- Komunikasi real-time antar pengguna (chat) dan notifikasi (in-app + web push).
- Ulasan & rating, favorit, riwayat pencarian, dan riwayat "terakhir dilihat".
- Panel Admin untuk moderasi, validasi aset, manajemen pengguna, promo, service fee, CMS, dan backup.

### 1.3 Definisi, Akronim, dan Singkatan
| Istilah | Arti |
|---------|------|
| SRS | Software Requirements Specification |
| Owner | Pemilik aset yang menyewakan properti/alat |
| User/Penyewa | Pengguna yang mencari & menyewa aset |
| Admin | Pengelola platform (superuser) |
| Aset | Properti/alat yang dapat disewa |
| Unit | Varian/spesifikasi dari sebuah aset |
| Booking | Proses pemesanan aset |
| OTP | One-Time Password (verifikasi telepon) |
| Inertia | Library penghubung Laravel & Vue tanpa API terpisah |
| WebPush | Notifikasi push browser |

### 1.4 Referensi
- ERD KitaSewa (`Doc/ERD KitaSewa.drawio.pdf`)
- DFD KitaSewa (`Doc/DFD KitaSewa.drawio.pdf`)
- Struktur kode: `routes/web.php`, `app/Http/Controllers`, `app/Models`, `resources/js/Pages`

---

## 2. Deskripsi Umum

### 2.1 Perspektif Produk
Sistem berupa aplikasi web tunggal. Interaksi terjadi melalui browser; Laravel menangani logika & data, Vue 3 (Inertia) menangani tampilan. Komunikasi real-time menggunakan Laravel Echo + Reverb/Pusher dan WebPush.

### 2.2 Fungsi Utama (Ikhtisar)
1. Autentikasi & profil (email, Google OAuth, OTP telepon).
2. Pencarian aset (keyword, lokasi, filter) + saran & riwayat.
3. Detail aset, galeri, fasilitas, peta lokasi, nearby places.
4. Booking & pembayaran + bukti & verifikasi pembayaran.
5. Dashboard Owner (kelola aset, booking masuk, iuran bulanan, pendapatan).
6. Chat real-time & notifikasi.
7. Ulasan, favorit, aktivitas, riwayat dilihat.
8. Panel Admin (moderasi, validasi, promo, service fee, CMS, backup).

### 2.3 Karakteristik Pengguna
| Peran | Deskripsi |
|-------|-----------|
| Guest | Pengunjung tanpa login; hanya bisa melihat & mencari. |
| User (Penyewa) | Bisa booking, chat, ulasan, favorit, kelola profil. |
| Owner | User dengan verifikasi; bisa publish aset & terima booking. |
| Admin | Akses penuh ke moderasi & konfigurasi sistem. |

### 2.4 Lingkungan Operasi
- **Server:** PHP ≥ 8.1, Laravel ≥ 10, MySQL/MariaDB.
- **Client:** Browser modern (Chrome, Firefox, Edge, Safari) dengan dukungan Service Worker & Geolocation.
- **Build:** Node.js + Vite (frontend), Composer (backend).

### 2.5 Batasan
- Tidak ada aplikasi mobile native (web-responsive saja).
- Pembayaran menggunakan upload bukti manual / verifikasi admin (tidak ada payment gateway otomatis dari kode yang ada).
- Notifikasi push hanya pada browser yang mendukung.

### 2.6 Asumsi & Ketergantungan
- Koneksi internet stabil untuk peta & push.
- Layanan OpenStreetMap/Nominatim tersedia untuk geocoding.
- Server mendukung queue worker untuk job (mis. `FetchNearbyPlacesJob`).

---

## 3. Kebutuhan Fungsional

### 3.1 Autentikasi & Manajemen Pengguna
- **FR-AUTH-01** Sistem memungkinkan registrasi dengan email + password.
- **FR-AUTH-02** Sistem mendukung login via Google OAuth (`GoogleAuthController`).
- **FR-AUTH-03** Sistem mengirim & memverifikasi OTP telepon (`OTPService`, `AuthService`).
- **FR-AUTH-04** Pengguna dapat mereset password via email.
- **FR-AUTH-05** Pengguna dapat memperbarui profil, foto, password, dan menghapus akun.
- **FR-AUTH-06** Pengguna dapat mengelola informasi bisnis & rekening bank (`OwnerBilling`, `bank_account`).

### 3.2 Pencarian & Penjelajahan
- **FR-SEARCH-01** Sistem menampilkan hasil pencarian aset berdasarkan keyword & lokasi (`HomeController::search`).
- **FR-SEARCH-02** Sistem memberikan saran otomatis (`/search/suggest`).
- **FR-SEARCH-03** Sistem menyimpan riwayat pencarian (`search_log`) dan memungkinkan penghapusan.
- **FR-SEARCH-04** Sistem menampilkan aset di dekat lokasi pengguna (`/api/home/nearby-assets`).
- **FR-SEARCH-05** Filter lokasi bertingkat: provinsi → kota → kecamatan → desa (`LocationController`).

### 3.3 Manajemen Aset (Owner)
- **FR-ASSET-01** Owner dapat membuat, mengedit, dan menghapus aset (`OwnerAssetController`).
- **FR-ASSET-02** Sistem mendukung draft dengan auto-save (`asset/auto-save`).
- **FR-ASSET-03** Owner dapat mengunggah & mengelola galeri gambar aset.
- **FR-ASSET-04** Owner dapat menambahkan unit/sewaan, fasilitas, FAQ, dan kebijakan.
- **FR-ASSET-05** Owner dapat mengatur status aktif/nonaktif aset (`toggle-status`).
- **FR-ASSET-06** Sistem menampilkan preview tempat terdekat (NearbyPlace via job).

### 3.4 Booking & Pembayaran
- **FR-BOOK-01** User dapat melakukan pemesanan aset (pilih tanggal, durasi, unit).
- **FR-BOOK-02** Sistem menghitung subtotal, service fee, dan total (`booking` model).
- **FR-BOOK-03** User dapat membatalkan booking.
- **FR-PAY-01** Sistem mencatat pembayaran & bukti (`payment` model).
- **FR-PAY-02** Owner memverifikasi pembayaran (`bookings.verify-payment`).
- **FR-PAY-03** Owner dapat mengonfirmasi, menolak, atau menyelesaikan booking.
- **FR-PAY-04** Owner membayar iuran bulanan (`MonthlyPaymentController`).
- **FR-PAY-05** Sistem menampilkan ringkasan pendapatan Owner (`IncomeController`).

### 3.5 Komunikasi & Notifikasi
- **FR-CHAT-01** User & Owner dapat memulai & mengirim pesan chat real-time (`ChatController`).
- **FR-CHAT-02** Pesan mendukung lampiran & status "read".
- **FR-NOTIF-01** Sistem menampilkan notifikasi in-app (daftar, unread count, mark read).
- **FR-NOTIF-02** Sistem mengirim WebPush via subscription (`PushSubscriptionController`).

### 3.6 Ulasan, Favorit & Aktivitas
- **FR-REV-01** User dapat memberikan ulasan & rating pada aset (`ReviewController`).
- **FR-REV-02** Ulasan dapat memiliki tag (`review_tag`, `review_tag_item`).
- **FR-FAV-01** User dapat menambah/menghapus aset dari favorit.
- **FR-ACT-01** Sistem menyediakan hub aktivitas: transaksi, pencarian, ulasan, favorit, terakhir dilihat.

### 3.7 Panel Administrator
- **FR-ADM-01** Admin mengelola akun user & administrator.
- **FR-ADM-02** Admin memvalidasi pengajuan aset (`validasi-aset`).
- **FR-ADM-03** Admin mengelola promo/diskon, service fee & sanksi.
- **FR-ADM-04** Admin mengelola CMS, kategori fasilitas, dan pengajuan akun Owner.
- **FR-ADM-05** Admin memantau laporan pengguna & log aktivitas.
- **FR-ADM-06** Admin dapat melakukan backup & restore data.
- **FR-ADM-07** Admin mengelola sistem pembayaran & notifikasi sistem.

### 3.8 Dukungan
- **FR-SUP-01** Sistem menyediakan Pusat Bantuan & Hubungi Kami.

---

## 4. Kebutuhan Non-Fungsional

| Kode | Kebutuhan | Target |
|------|-----------|--------|
| NFR-PERF | Waktu respons pencarian < 2 detik | < 2s |
| NFR-SEC | Password di-hash (bcrypt); OTP & token login aman | Wajib |
| NFR-AVAIL | Ketersediaan sistem ≥ 99% (dalam jam operasional) | ≥ 99% |
| NFR-USAB | Antarmuka responsif (mobile & desktop) | Wajib |
| NFR-SCAL | Mendukung queue job untuk tugas berat (nearby places) | Wajib |
| NFR-COMP | Kompatibel dengan Chrome, Firefox, Edge, Safari terbaru | Wajib |
| NFR-BACK | Fitur backup/restore tersedia untuk Admin | Wajib |
| NFR-LOCALE | Dukungan bahasa Indonesia & English (`lang/`) | Wajib |

---

## 5. Model Data (Ikhtisar Entitas)

| Entitas | Relasi Utama |
|---------|--------------|
| User | hasMany Booking, Review, Favorite, Asset (sebagai Owner) |
| asset | belongsTo User(Owner); hasMany asset_units, facility, faq, policy, image |
| booking | belongsTo asset, asset_units, user; hasOne payment, review |
| payment | belongsTo booking |
| review | belongsTo asset, user, booking; hasMany review_tag_item |
| favorite | belongsTo user, asset |
| message / room_chat | komunikasi antar user |
| province / city / district / village | hierarki lokasi |
| OwnerBilling / bank_account | data rekening Owner |
| service_fee / promo (implisit) | konfigurasi biaya & diskon |
| search_log / asset_view | riwayat & "terakhir dilihat" |
| notification / push_subscription | notifikasi |

---

## 6. Alur Proses Utama (DFD Ringkas)

1. **Cari → Lihat → Booking → Bayar → Verifikasi → Selesai**
   Guest/User mencari aset → buka detail → buat booking → unggah bukti → Owner verifikasi → booking selesai → User mengulas.
2. **Owner Onboarding**
   User daftar Owner (step 1–3) → verifikasi → kelola aset → terima booking → bayar iuran bulanan → lihat pendapatan.
3. **Admin Moderasi**
   Pantau pengajuan → validasi aset → kelola promo/fee → tangani laporan → backup.

---

## 7. Kebutuhan Antarmuka

### 7.1 Antarmuka Pengguna
- Halaman publik: Beranda, Pencarian, Detail Aset, Auth (Login/Register/Owner), Bantuan.
- Halaman User: Aktivitas, Booking, Payment, Chat, Profil, Settings, Favorit.
- Halaman Owner: Dashboard, Kelola Aset, Booking masuk, Iuran, Pendapatan, Profil.
- Halaman Admin: Dashboard, manajemen user/akun, validasi aset, promo, service fee, CMS, backup, log.

### 7.2 Antarmuka Perangkat Keras
- Browser dengan akses kamera (untuk webcam capture pada verifikasi).
- GPS/Geolocation untuk pencarian "terdekat".

### 7.3 Antarmuka Perangkat Lunak Eksternal
- OpenStreetMap / Nominatim (geocoding & peta).
- Google OAuth (login sosial).
- Laravel Echo / Reverb / Pusher (real-time).
- WebPush (notifikasi).

---

## 8. Kebutuhan Keamanan
- Autentikasi berbasis session (Laravel) + token login (`login_token`).
- OTP untuk verifikasi nomor telepon.
- Middleware `auth` pada seluruh fitur berbatas login.
- Role-based access: `owner` & `admin` prefix terproteksi.
- Penyimpanan rahasia via `.env` (tidak ter-commit).

---

## 9. Kebutuhan Pengujian (Rencana)
- **Unit:** model & service (OTP, Auth, OpenStreetMap).
- **Feature/Integration:** alur booking & pembayaran, chat.
- **UI:** komponen Vue (AuthModal, SearchBar, dsb.) via `tests/`.
- **Manual:** verifikasi peran Admin & Owner.

---

## 10. Lampiran
- ERD: `Doc/ERD KitaSewa.drawio.pdf`
- DFD: `Doc/DFD KitaSewa.drawio.pdf`
- Struktur rute: `routes/web.php`
- Kontroler: `app/Http/Controllers/**`
- Model: `app/Models/**`
- Komponen UI: `resources/js/**`
