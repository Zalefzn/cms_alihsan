# Al-Ihsan CMS

CMS berbasis Laravel + [Filament](https://filamentphp.com) untuk mengelola konten website Al-Ihsan Islamic School. CMS ini berdiri sendiri (terpisah dari project `alihsanislamicsch`) dan menyediakan REST API yang bisa dikonsumsi oleh website React yang sudah ada.

## Fitur

- **Manajemen Halaman** — satu entri per halaman situs (Beranda, Tentang, Guru, Kontak, dst), langsung terdaftar sebagai sub-menu di sidebar "Halaman" (klik langsung ke halamannya, tanpa lewat tabel dulu).
- **Blok Konten drag & drop** — setiap halaman disusun dari beberapa "blok" (Hero, Teks, Galeri, Video, FAQ, Tim/Guru, Testimoni, Info Kontak, Statistik, Daftar Fitur) yang bisa ditambah, dihapus, dan **diurutkan ulang lewat drag & drop** di panel admin.
- **Menu Navbar** — atur menu navigasi website React (label, link, urutan, submenu dropdown) secara terpisah dari konten halaman, lewat sidebar "Navigasi Website". Mendukung drag & drop untuk menu utama maupun sub-menu.
- **Upload gambar & video** langsung dari form blok, tanpa perlu tools eksternal.
- **Dashboard ringkas** — statistik jumlah halaman, blok konten, dan menu langsung terlihat saat login.
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

**Mengedit konten halaman**

1. Login di `/admin`.
2. Di sidebar, grup **Halaman** langsung menampilkan semua halaman — klik salah satu untuk masuk ke editornya (atau **Semua Halaman** untuk tabel lengkap + buat halaman baru).
3. Di bagian **Blok Konten**:
   - **Tambah Blok** untuk menambah bagian baru (pilih tipenya — form akan menyesuaikan otomatis).
   - **Edit** untuk mengubah isi tiap blok, termasuk upload gambar/video.
   - **Drag baris** pada tabel Blok Konten untuk mengubah urutan tampil di halaman.
   - Toggle **Tampil** untuk menyembunyikan blok tanpa menghapusnya.

**Mengatur menu navbar**

1. Di sidebar, buka grup **Navigasi Website** → **Menu Navbar**.
2. Tambah/urutkan menu utama (drag baris untuk urutan). Kosongkan **Link** jika menu itu hanya induk dropdown (contoh: "Tentang", "Akademik").
3. Klik **Kelola** pada satu menu untuk mengatur **Sub Menu**-nya (dropdown) — juga bisa drag & drop.

## REST API

Semua endpoint bersifat publik (read-only) dan hanya mengembalikan halaman/blok yang berstatus terbit & tampil.

| Method | Endpoint            | Keterangan                                                |
| ------ | ------------------- | ---------------------------------------------------------- |
| GET    | `/api/pages`        | Daftar semua halaman terbit (slug, judul, meta description) |
| GET    | `/api/pages/{slug}` | Detail satu halaman beserta blok-bloknya, terurut, dengan URL media yang sudah lengkap |
| GET    | `/api/menu`         | Struktur menu navbar (menu utama + sub-menu dropdown), terurut |

Contoh respons `/api/menu`:

```json
{
  "data": [
    { "label": "Beranda", "url": "/", "open_in_new_tab": false, "children": [] },
    {
      "label": "Tentang",
      "url": null,
      "open_in_new_tab": false,
      "children": [
        { "label": "Tentang Kami", "url": "/about", "open_in_new_tab": false, "children": [] }
      ]
    }
  ]
}
```

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
- `menu_items` — satu baris per item navbar (`label`, `url`, `parent_id`, `order`, `is_visible`). Item dengan `parent_id` null adalah menu utama; item dengan `parent_id` terisi adalah sub-menu dropdown.

Tipe blok yang tersedia didefinisikan di `app/Support/BlockDefinitions.php` — tambahkan entri baru di sana untuk menambah tipe blok baru (form admin akan otomatis mengikuti).

## Menghubungkan ke website React

Website `alihsanislamicsch` bisa mengambil konten dari CMS ini dengan `fetch`/`axios`:
- `NEXT_CMS_URL/api/pages/{slug}` → konten halaman (blok-bloknya), render sesuai `type`.
- `NEXT_CMS_URL/api/menu` → struktur navbar, ganti link hardcoded di `navbar.tsx` dengan hasil ini.

Langkah ini belum diimplementasikan di sisi React — CMS-nya sudah siap dipakai begitu integrasinya dikerjakan.

## Keamanan

⚠️ Akun admin awal dibuat dengan password contoh saat setup. **Segera ganti password** melalui panel admin atau `php artisan make:filament-user` sebelum digunakan di lingkungan produksi. Jangan commit file `.env` atau `database/database.sqlite`.
