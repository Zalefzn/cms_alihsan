# Al-Ihsan CMS

CMS berbasis Laravel + [Filament](https://filamentphp.com) untuk mengelola konten website Al-Ihsan Islamic School. CMS ini berdiri sendiri (terpisah dari project `alihsanislamicsch`) dan menyediakan REST API yang bisa dikonsumsi oleh website React yang sudah ada.

## Fitur

- **Manajemen Halaman** — satu entri per halaman situs (Beranda, Tentang, Guru, Kontak, dst), langsung terdaftar sebagai sub-menu di sidebar "Halaman" (klik langsung ke halamannya, tanpa lewat tabel dulu).
- **Blok Konten drag & drop** — setiap halaman disusun dari beberapa "blok" (Hero, Teks, Galeri, Video, FAQ, Tim/Guru, Testimoni, Info Kontak, Statistik, Daftar Fitur) yang bisa ditambah, dihapus, dan **diurutkan ulang lewat drag & drop** di panel admin.
- **Menu Navbar** — atur menu navigasi website React (label, link, urutan, submenu dropdown) secara terpisah dari konten halaman, lewat sidebar "Navigasi Website". Mendukung drag & drop untuk menu utama maupun sub-menu.
- **Upload gambar & video** langsung dari form blok, dengan pratinjau langsung (thumbnail gambar/video, bukan cuma nama file).
- **Konten dwibahasa (Indonesia/Inggris)** — tiap field teks (judul halaman, isi blok, label menu, dll) punya pasangan field Inggris yang **otomatis terisi draft terjemahan** begitu field Indonesia-nya selesai diketik, plus tombol "Terjemahkan" untuk memicu ulang kapan saja. Lihat bagian [Konten dwibahasa](#konten-dwibahasa) di bawah.
- **Dashboard informatif** — statistik jumlah halaman/blok/menu, grafik sebaran tipe blok, dan tabel halaman yang baru diperbarui.
- **Notifikasi** — lonceng notifikasi di navbar, mengabari admin lain saat ada halaman baru dibuat atau status terbit sebuah halaman berubah.
- **Manajemen Peran & Pengguna** — buat akun untuk tim (misal Editor konten) dan atur lewat UI persis apa yang boleh mereka kelola, tanpa coding. Lihat bagian [Peran & Pengguna](#peran--pengguna) di bawah.
- **REST API publik** untuk diambil oleh frontend (lihat di bawah).

## Menjalankan secara lokal

Butuh PHP 8.2+, Composer, dan ekstensi PHP: `pdo_sqlite`, `sqlite3`, `fileinfo`, `gd`, `zip`, `intl`, `exif` (aktifkan di `php.ini` jika belum).

Fitur auto-translate memanggil API eksternal lewat HTTPS (lihat [Konten dwibahasa](#konten-dwibahasa)) — di Windows, PHP sering belum punya CA bundle terpasang sehingga permintaan HTTPS gagal dengan error `SSL certificate ... unable to get local issuer certificate`. Kalau itu terjadi, unduh [cacert.pem](https://curl.se/ca/cacert.pem) lalu tambahkan ke `php.ini`:

```ini
curl.cainfo="C:\path\ke\cacert.pem"
openssl.cafile="C:\path\ke\cacert.pem"
```

```bash
composer install
cp .env.example .env   # kalau .env belum ada
php artisan key:generate
touch database/database.sqlite
php artisan migrate
php artisan storage:link
php artisan make:filament-user   # buat akun admin pertama Anda
php artisan db:seed              # isi konten contoh + peran super_admin untuk akun di atas
php artisan serve
```

Urutannya penting: `RoleSeeder` (dijalankan lewat `db:seed`) memberi peran `super_admin` ke pengguna **pertama** yang ada — jadi `make:filament-user` harus dijalankan lebih dulu, baru `db:seed`.

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

## Peran & Pengguna

Diaktifkan lewat [Filament Shield](https://github.com/bezhanSalleh/filament-shield) + [spatie/laravel-permission](https://spatie.be/docs/laravel-permission) — semua pengecekan izin ditegakkan di server (bukan cuma menyembunyikan menu), lewat sidebar **Pengguna & Peran**:

- **Pengguna** — buat akun baru (nama, email, kata sandi) dan pilih satu atau lebih peran untuknya.
- **Peran & Izin** — buat peran baru atau ubah peran yang ada; setiap resource (Halaman, Menu Navbar, dst) muncul sebagai daftar checkbox per aksi (Lihat, Buat, Ubah, Hapus, dll) yang tinggal dicentang — tidak perlu coding untuk mengubah siapa boleh apa.

Dua peran bawaan (dari `database/seeders/RoleSeeder.php`):

| Peran | Bisa apa |
| --- | --- |
| `super_admin` | Semua fitur, termasuk mengelola Pengguna & Peran itu sendiri. |
| `editor` | Lihat, buat, ubah, dan urutkan **Halaman** beserta Blok Kontennya saja — tidak bisa menghapus halaman, dan tidak bisa menyentuh Menu Navbar, Pengguna, atau Peran. |

Beri peran tambahan (atau buat peran baru, mis. "Editor Menu") kapan saja lewat **Peran & Izin** — perubahan izin langsung berlaku, tidak perlu deploy ulang.

## Konten dwibahasa

Setiap field teks yang tampil ke pengunjung (judul halaman, deskripsi SEO, isi tiap blok, label menu navbar) punya dua versi: Indonesia (field biasa, wajib diisi) dan Inggris (field kedua berlabel "... (Inggris)", opsional).

- **Otomatis**: begitu Anda selesai mengetik di field Indonesia dan pindah ke field lain, field Inggris yang **masih kosong** akan otomatis terisi draft terjemahan (via [MyMemory Translation API](https://mymemory.translated.net), gratis tanpa API key).
- **Manual/ulang**: klik tombol **Terjemahkan** di sebelah field Inggris kapan saja untuk menerjemahkan ulang dari isi field Indonesia terkini — berguna kalau Anda mengubah teks Indonesia setelah field Inggris sudah pernah diisi.
- Field Inggris **tidak pernah ditimpa otomatis** kalau sudah ada isinya (baik dari auto-fill maupun ketikan manual) — edit manual Anda selalu aman, kecuali Anda sengaja klik tombol Terjemahkan lagi.
- Teks berformat (isi blok "Teks / Paragraf") **tidak diterjemahkan otomatis** — HTML-nya bisa rusak kalau diterjemahkan mentah. Tulis versi Inggrisnya manual.
- Field non-teks (link, nomor telepon, email, nama orang) sengaja tidak punya pasangan Inggris karena tidak butuh diterjemahkan.
- Kalau field Inggris kosong (belum diterjemahkan sama sekali), API otomatis mengembalikan teks Indonesia sebagai fallback — situs React tidak akan pernah menampilkan field kosong.
- Layanan terjemahan bisa gagal/tidak bisa diakses (mis. tidak ada internet) — ini tidak menghalangi simpan; field Inggris cukup dibiarkan kosong dan diisi manual nanti.

## REST API

Semua endpoint bersifat publik (read-only) dan hanya mengembalikan halaman/blok yang berstatus terbit & tampil. Tambahkan `?lang=en` untuk versi Inggris (fallback ke Indonesia untuk field yang belum diterjemahkan); tanpa parameter ini defaultnya Indonesia.

| Method | Endpoint                    | Keterangan                                                |
| ------ | ---------------------------- | ---------------------------------------------------------- |
| GET    | `/api/pages?lang=id\|en`     | Daftar semua halaman terbit (slug, judul, meta description) |
| GET    | `/api/pages/{slug}?lang=id\|en` | Detail satu halaman beserta blok-bloknya, terurut, dengan URL media yang sudah lengkap |
| GET    | `/api/menu?lang=id\|en`      | Struktur menu navbar (menu utama + sub-menu dropdown), terurut |

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

- `pages` — satu baris per halaman (`slug`, `title` + `title_en`, `meta_description` + `meta_description_en`, `is_published`).
- `blocks` — banyak baris per halaman (`type`, `order`, `is_visible`, `data` JSON). Urutan blok ditentukan kolom `order`, diubah lewat drag & drop di admin. Setiap field teks dalam `data` punya pasangan `{field}_en` (mis. `heading` + `heading_en`) — lihat [Konten dwibahasa](#konten-dwibahasa).
- `menu_items` — satu baris per item navbar (`label` + `label_en`, `url`, `parent_id`, `order`, `is_visible`). Item dengan `parent_id` null adalah menu utama; item dengan `parent_id` terisi adalah sub-menu dropdown.

Tipe blok yang tersedia didefinisikan di `app/Support/BlockDefinitions.php` — tambahkan entri baru di sana untuk menambah tipe blok baru (form admin akan otomatis mengikuti). Untuk field teks yang perlu versi Inggris, gunakan `App\Filament\Support\TranslatableField::text()` / `::textarea()` / `::richEditor()` alih-alih komponen Filament biasa — ini otomatis membuatkan pasangan field `{nama}` + `{nama}_en` lengkap dengan auto-translate.

## Menghubungkan ke website React

Website `alihsanislamicsch` bisa mengambil konten dari CMS ini dengan `fetch`/`axios`:
- `NEXT_CMS_URL/api/pages/{slug}` → konten halaman (blok-bloknya), render sesuai `type`.
- `NEXT_CMS_URL/api/menu` → struktur navbar, ganti link hardcoded di `navbar.tsx` dengan hasil ini.
- Tambahkan `?lang=en` saat visitor memilih bahasa Inggris (baca dari state i18next yang sudah ada di `app/i18n.ts`) — CMS-nya sudah mengembalikan field yang sesuai, React tidak perlu logika terjemahan sendiri untuk konten dari CMS.

Langkah ini belum diimplementasikan di sisi React — CMS-nya sudah siap dipakai begitu integrasinya dikerjakan.

## Keamanan

⚠️ Akun admin awal dibuat dengan password contoh saat setup. **Segera ganti password** melalui panel admin atau `php artisan make:filament-user` sebelum digunakan di lingkungan produksi. Jangan commit file `.env` atau `database/database.sqlite`.
