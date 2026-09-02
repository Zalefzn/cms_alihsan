# Al-Ihsan CMS

CMS berbasis Laravel + [Filament](https://filamentphp.com) untuk mengelola konten website Al-Ihsan Islamic School. CMS ini berdiri sendiri (terpisah dari project `alihsanislamicsch`) dan menyediakan REST API yang bisa dikonsumsi oleh website React yang sudah ada.

## Fitur

- **Manajemen Halaman** — satu entri per halaman situs (Beranda, Tentang, Guru, Kontak, dst).
- **Blok Konten drag & drop** — setiap halaman disusun dari beberapa "blok" (Hero, Teks, Galeri, Video, FAQ, Tim/Guru, Testimoni, Info Kontak, Statistik, Daftar Fitur) yang bisa ditambah, dihapus, dan **diurutkan ulang lewat drag & drop** di panel admin.
- **Upload gambar & video** langsung dari form blok, tanpa perlu tools eksternal.
- **REST API publik** untuk diambil oleh frontend (lihat di bawah).

## Menjalankan secara lokal

Butuh PHP 8.2+, Composer, dan ekstensi PHP: `pdo_sqlite`, `sqlite3`, `fileinfo`, `gd`, `zip`, `intl`, `exif` (aktifkan di `php.ini` jika belum).

```bash
composer install
cp .env.example .env   # kalau .env belum ada
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
php artisan storage:link
php artisan make:filament-user   # buat akun admin pertama Anda
php artisan serve
```

Panel admin bisa diakses di `http://localhost:8000/admin`.

## Cara pakai panel admin

1. Login di `/admin`.
2. Buka menu **Halaman**, klik **Kelola Konten** pada halaman yang ingin diedit.
3. Di bagian **Blok Konten**:
   - **Tambah Blok** untuk menambah bagian baru (pilih tipenya — form akan menyesuaikan otomatis).
   - **Edit** untuk mengubah isi tiap blok, termasuk upload gambar/video.
   - **Drag baris** pada tabel Blok Konten untuk mengubah urutan tampil di halaman.
   - Toggle **Tampil** untuk menyembunyikan blok tanpa menghapusnya.

## REST API

Semua endpoint bersifat publik (read-only) dan hanya mengembalikan halaman/blok yang berstatus terbit & tampil.

| Method | Endpoint            | Keterangan                                                |
| ------ | ------------------- | ---------------------------------------------------------- |
| GET    | `/api/pages`        | Daftar semua halaman terbit (slug, judul, meta description) |
| GET    | `/api/pages/{slug}` | Detail satu halaman beserta blok-bloknya, terurut, dengan URL media yang sudah lengkap |

Contoh respons `/api/pages/home`:

```json
{
  "data": {
    "slug": "home",
    "title": "Beranda",
    "meta_description": "...",
    "blocks": [
      {
        "id": 1,
        "type": "hero",
        "order": 0,
        "data": {
          "heading": "Pendidikan Terbaik untuk Anak Anda",
          "subheading": "...",
          "cta_text": "Pendaftaran 2025 Dibuka",
          "cta_link": "/penerimaan",
          "image": "http://localhost:8000/storage/blocks/xxxx.jpg"
        }
      }
    ]
  }
}
```

Struktur `data` berbeda-beda tergantung `type` blok — lihat `app/Support/BlockDefinitions.php` untuk daftar lengkap field per tipe.

CORS sudah diaktifkan untuk semua origin pada path `/api/*` (lihat `config/cors.php`), sehingga bisa langsung dipanggil dari domain frontend manapun.

## Struktur konten

- `pages` — satu baris per halaman (`slug`, `title`, `meta_description`, `is_published`).
- `blocks` — banyak baris per halaman (`type`, `order`, `is_visible`, `data` JSON). Urutan blok ditentukan kolom `order`, diubah lewat drag & drop di admin.

Tipe blok yang tersedia didefinisikan di `app/Support/BlockDefinitions.php` — tambahkan entri baru di sana untuk menambah tipe blok baru (form admin akan otomatis mengikuti).

## Menghubungkan ke website React

Website `alihsanislamicsch` bisa mengambil konten dari CMS ini dengan `fetch`/`axios` ke `NEXT_CMS_URL/api/pages/{slug}` dan me-render blok-bloknya sesuai `type`. Langkah ini belum diimplementasikan di sisi React — CMS-nya sudah siap dipakai begitu integrasinya dikerjakan.

## Keamanan

⚠️ Akun admin awal dibuat dengan password contoh saat setup. **Segera ganti password** melalui panel admin atau `php artisan make:filament-user` sebelum digunakan di lingkungan produksi. Jangan commit file `.env` atau `database/database.sqlite`.
