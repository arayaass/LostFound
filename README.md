# TemuKembali

Aplikasi Lost & Found modern berbasis Laravel 12, MySQL, Blade, Tailwind CSS, serta adapter opsional untuk Google OAuth, Firebase/Firestore, dan AI.

## Fitur

- Register/login email, Google OAuth, logout, profil pengguna
- Laporan barang hilang/ditemukan, foto, GPS, Google Maps
- Beranda, pencarian nama/lokasi/status, pagination
- Chat antarpengguna, online status, last seen
- Notifikasi database dan rekomendasi kecocokan barang
- Dashboard admin, statistik, moderasi spam, hapus laporan
- Data Tables admin dengan pencarian, filter, sorting, pagination, dan ekstrak PDF/Excel/CSV
- Alur serah terima: pengajuan, verifikasi pelapor, jadwal pertemuan, konfirmasi kedua pihak, dan riwayat proses
- Kategori barang dan filter kategori untuk Elektronik, Kunci, Dompet, Tas, Dokumen/Kartu, Kendaraan, Hewan, Aksesori, Pakaian, dan Lainnya
- Halaman Barang Selesai; laporan otomatis hilang dari beranda setelah dikonfirmasi kedua pihak
- UI responsif dengan sidebar desktop dan bottom navigation mobile

## Instalasi

1. Buat database MySQL bernama `projeklostandfound`.
2. Salin `.env.example` menjadi `.env`, lalu sesuaikan kredensial MySQL.
3. Jalankan:

```bash
composer install
php artisan key:generate
php artisan storage:link
php artisan migrate --seed
npm install
npm run build
php artisan serve
```

Akun admin hasil seeder: `admin@temukembali.id` dengan password default factory `password`.

## Integrasi Eksternal

Isi nilai berikut di `.env` bila ingin mengaktifkan provider:

```env
GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
FIREBASE_PROJECT_ID=
FIREBASE_API_KEY=
OPENAI_API_KEY=
```

Callback Google OAuth lokal: `http://localhost:8000/auth/google/callback`.

Untuk mengaktifkan Google Login, buat OAuth Client bertipe **Web application** di Google Cloud Console. Masukkan nilai Client ID dan Client Secret ke `.env`, lalu daftarkan nilai `GOOGLE_REDIRECT_URI` sebagai **Authorized redirect URI**. URL callback harus sama persis, termasuk domain, port, dan protokol.

Konfigurasi lokal yang direkomendasikan:

```env
APP_URL=http://localhost:8000
GOOGLE_REDIRECT_URI=http://localhost:8000/auth/google/callback
```

MySQL tetap menjadi sumber data utama. `FirebaseService` menyinkronkan laporan ke Firestore saat kredensial tersedia. Chat memiliki implementasi database yang langsung berjalan dan dapat ditingkatkan ke listener Firestore melalui adapter yang sama. Sistem pencocokan lokal tetap berjalan tanpa API AI, sehingga aplikasi tidak berhenti ketika API key belum tersedia.

Ekspor PDF menggunakan vendor `barryvdh/laravel-dompdf`. Ekspor Excel menggunakan format SpreadsheetML `.xls` agar tetap kompatibel dengan Microsoft Excel tanpa membutuhkan ekstensi PHP `gd` dan `zip`.

## Struktur Utama

- `app/Models`: model domain
- `app/Http/Controllers`: alur HTTP per fitur
- `app/Services`: integrasi dan logika pencocokan
- `resources/views/components`: komponen UI reusable
- `resources/views/layouts`: shell responsif
- `database/migrations`: skema database
