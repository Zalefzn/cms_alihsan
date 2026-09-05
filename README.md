# Al-Ihsan CMS

CMS berbasis Laravel + [Filament](https://filamentphp.com) untuk mengelola konten website Al-Ihsan Islamic School. CMS ini berdiri sendiri (terpisah dari project `alihsanislamicsch`) dan menyediakan REST API yang bisa dikonsumsi oleh website React yang sudah ada.

## Fitur

- **Manajemen Halaman** — satu entri per halaman situs (Beranda, Tentang, Guru, Kontak, dst), langsung terdaftar sebagai sub-menu di sidebar "Halaman" (klik langsung ke halamannya, tanpa lewat tabel dulu).
- **Desain Halaman (visual page builder)** — editor drag & drop bergaya WordPress: sidebar berisi susunan blok halaman (tambah/hapus/duplikat/urutkan/sembunyikan), form edit tiap blok muncul langsung di sana, dan di sampingnya ada **kanvas pratinjau langsung** — iframe situs React sungguhan yang ikut berubah setiap kali Anda mengetik, lengkap dengan pilihan lebar Desktop/Tablet/Mobile serta **Undo/Redo** (`Ctrl+Z` / `Ctrl+Shift+Z`). Tabel "Kelola Konten" lama tetap ada sebagai alternatif. Lihat [Desain Halaman](#desain-halaman-page-builder) di bawah.
- **25 tipe blok konten**, masing-masing punya beberapa **varian tampilan** dengan pratinjau visual (bukan cuma nama) langsung di form edit — lihat [Tipe blok yang tersedia](#tipe-blok-yang-tersedia).
- **Menu Navbar + Editor Design** — atur menu navigasi website (label, link, urutan, submenu dropdown) lewat tabel biasa, **atau** lewat builder visual serupa Desain Halaman (tombol **Editor Design**) dengan kanvas pratinjau navbar asli. Setiap menu dengan sub-menu bisa memilih **gaya dropdown** (Daftar Sederhana / Grid 2 Kolom / Kartu dengan Ikon). Lihat [Menu Navbar](#menu-navbar--editor-design).
- **Pengaturan Situs** — logo, nama & tagline situs, pesan+kontak di topbar, dan isi footer (deskripsi, teks hak cipta, link sosial media) — semua dari satu halaman, tanpa sentuh kode.
- **Pengaturan SEO** — deskripsi meta & kata kunci bawaan (dipakai kalau sebuah halaman belum diisi sendiri), gambar bagikan (Open Graph) bawaan, domain resmi untuk link canonical, kode verifikasi Google Search Console, dan sakelar izinkan/blokir pengindeksan situs.
- **Pelanggan Buletin** — daftar email yang mendaftar lewat form "Berlangganan Buletin Sekolah" di footer situs, bisa dilihat dan dihapus dari admin.
- **Upload gambar & video** langsung dari form blok, dengan pratinjau langsung (thumbnail gambar/video, bukan cuma nama file).
- **Konten dwibahasa (Indonesia/Inggris)** — tiap field teks (judul halaman, isi blok, label menu, dll) punya pasangan field Inggris yang **otomatis terisi draft terjemahan** begitu field Indonesia-nya selesai diketik, plus tombol "Terjemahkan" untuk memicu ulang kapan saja. Lihat bagian [Konten dwibahasa](#konten-dwibahasa) di bawah.
- **Dashboard informatif** — statistik jumlah halaman/blok/menu, grafik sebaran tipe blok, dan tabel halaman yang baru diperbarui.
- **Pencarian global & notifikasi** — kotak cari di navbar atas untuk lompat langsung ke sebuah halaman by judul/slug, dan lonceng notifikasi mengabari admin lain saat ada halaman baru dibuat atau status terbit berubah.
- **Manajemen Peran & Pengguna** — buat akun untuk tim (misal Editor konten) dan atur lewat UI persis apa yang boleh mereka kelola, tanpa coding. Ganti nama/email/kata sandi akun sendiri lewat menu **Profil** (ikon gerigi di kartu akun, pojok kiri bawah sidebar). Lihat bagian [Peran & Pengguna](#peran--pengguna) di bawah.
- **REST API publik** untuk diambil oleh frontend — **sudah terintegrasi penuh** ke website React `alihsanislamicsch` (lihat di bawah).

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

Sidebar (putih, dengan status aktif/hover warna ungu) punya tiga grup: **Pengguna & Peran**, **Navigasi Website**, dan **Halaman**. Kartu di paling bawah sidebar menampilkan akun yang sedang login (nama, peran, tombol **Keluar**) dan ikon gerigi ke halaman **Profil** (ganti nama/email/kata sandi). Navbar atas hanya berisi kotak **Cari** (pencarian global ke semua halaman), lonceng notifikasi, dan tombol lipat/lebarkan sidebar.

### Desain Halaman (page builder)

1. Login di `/admin`.
2. Di sidebar, grup **Halaman** langsung menampilkan semua halaman — klik salah satu untuk masuk ke tabel **Kelola Konten**-nya, atau **Semua Halaman** untuk tabel lengkap + buat halaman baru.
3. Dari tabel **Semua Halaman** (atau tombol yang sama di tabel Blok Konten lama), klik **Desain Halaman** untuk membuka page builder visual:
   - **+ Tambah Blok** memilih tipe blok baru dari daftar (lihat [Tipe blok yang tersedia](#tipe-blok-yang-tersedia)).
   - Klik nama sebuah blok di sidebar untuk membuka form edit-nya langsung di situ — termasuk pilihan **Varian Tampilan** dengan kartu pratinjau visual untuk tiap gaya.
   - **Geser** (ikon titik enam) untuk mengurutkan blok lewat drag & drop; ikon mata untuk sembunyikan/tampilkan; ikon salin untuk duplikat; ikon tempat sampah untuk hapus.
   - Kanvas di sebelah kanan adalah situs React sungguhan, berubah **langsung** saat Anda mengetik — pilih lebar **Lebar Penuh / Tablet / Mobile** untuk memeriksa tampilan responsif.
   - **Urungkan/Ulangi** (`Ctrl+Z` / `Ctrl+Shift+Z`) untuk membatalkan perubahan sebelum disimpan.
   - Tidak ada yang tersimpan ke database sampai Anda klik **Simpan Semua Perubahan**.
   - Tabel **Kelola Konten** (drag & drop baris biasa, tanpa pratinjau langsung) tetap tersedia lewat link "← Kembali ke Editor Tabel (alternatif lama)" di pojok kiri atas builder.

### Menu Navbar & Editor Design

1. Di sidebar, buka grup **Navigasi Website** → **Menu Navbar**.
2. **Cara cepat (tabel)**: tambah/urutkan menu utama (drag baris). Kosongkan **Link** jika menu itu hanya induk dropdown (contoh: "Tentang", "Akademik"). Klik **Kelola** pada satu menu untuk mengatur **Sub Menu**-nya, pilih **Gaya Dropdown**-nya (Daftar Sederhana / Grid 2 Kolom / Kartu dengan Ikon — hanya berlaku kalau menu itu punya sub menu), dan atur "Buka di tab baru" bila link-nya eksternal.
3. **Cara visual**: klik tombol **Editor Design** di pojok kanan atas tabel Menu Navbar untuk builder yang sama konsepnya dengan Desain Halaman — sidebar berisi susunan menu (drag & drop menu utama maupun sub menu, tambah/hapus, sembunyikan), form edit muncul langsung saat sebuah menu diklik, dan kanvas di sebelah kanan menampilkan navbar situs asli yang ikut berubah live. Klik **Simpan Semua Perubahan** untuk menerapkannya.

### Pengaturan Situs & Pengaturan SEO

- **Navigasi Website → Pengaturan Situs**: logo, nama & tagline situs, pesan selamat datang + telepon/email di topbar, dan isi footer (deskripsi singkat, teks hak cipta, link Website/Facebook/Twitter). Kosongkan field apa pun untuk memakai nilai bawaan (sama seperti tampilan awal situs).
- **Navigasi Website → Pengaturan SEO**: deskripsi meta & kata kunci bawaan (dipakai kalau sebuah halaman belum mengisi "Deskripsi Meta (SEO)" sendiri di form Kelola Konten-nya), gambar bagikan (Open Graph) bawaan untuk pratinjau link di WhatsApp/Facebook, username Twitter/X, domain resmi situs (untuk link canonical), kode verifikasi Google Search Console, dan sakelar "Izinkan mesin pencari mengindeks situs ini" (matikan sementara saat situs masih tahap pengembangan).

### Pelanggan Buletin

**Navigasi Website → Pelanggan Buletin** menampilkan setiap email yang mendaftar lewat form "Berlangganan Buletin Sekolah" di footer situs (email, bahasa saat mendaftar, tanggal) — tidak ada form tambah manual di sini karena baris hanya pernah masuk lewat pendaftaran publik di situs; hapus baris lewat tombol **Hapus** bila diperlukan.

## Tipe blok yang tersedia

Setiap blok punya beberapa **varian tampilan** (biasanya 4–5) yang bisa dibandingkan lewat kartu pratinjau visual di form edit-nya — isi kontennya tetap sama, cuma tata letak/gayanya yang beda.

| Blok | Kegunaan |
| --- | --- |
| Hero (Banner Utama) | Banner besar paling atas halaman — judul, sub-judul, tombol, gambar latar. |
| Teks / Paragraf | Paragraf teks bebas, satu atau dua kolom. |
| Galeri Gambar | Grid foto dengan lightbox; dua blok galeri berurutan otomatis jadi satu galeri bertab. |
| Video | Video YouTube/Vimeo atau file unggahan, dengan thumbnail asli. |
| Video + Teks | Video di satu sisi, judul/teks/tombol di sisi lain. |
| Foto + Teks | Sama seperti Video + Teks tapi dengan foto. |
| CTA (Ajakan Bertindak) | Banner ajakan dengan satu tombol besar (mis. "Daftar Sekarang"). |
| FAQ (Tanya Jawab) | Daftar pertanyaan yang bisa dibuka/tutup (akordeon). |
| Tim / Guru | Kartu foto + nama + jabatan untuk susunan guru/staf/pengurus. |
| Testimoni | Kutipan dari orang tua/alumni, dengan foto & nama. |
| Info Kontak | Alamat, telepon, email, dan peta Google Maps tertanam. |
| Peta Lokasi | Peta lokasi berdiri sendiri (tanpa info kontak di sampingnya). |
| Statistik / Angka | Angka pencapaian besar (jumlah siswa, guru, tahun berdiri, dst). |
| Angka Berjalan (Counter) | Sama seperti Statistik, dengan animasi angka berjalan naik. |
| Daftar Fitur / Program | Grid keunggulan/program, masing-masing dengan ikon, teks, dan (opsional) tombol/link sendiri. |
| Tentang + Visi & Misi | Bagian gabungan cerita sekolah dengan visi & misi. |
| Kartu Program | Kartu unit/program (mis. TK, SD, Kober) dengan foto, deskripsi, dan tombol WhatsApp. |
| Daftar Berita | Ringkasan berita/artikel terbaru dalam bentuk grid atau list. |
| Konten Tab / Akordeon | Konten panjang dipecah jadi beberapa tab atau akordeon. |
| Tabel Harga / Biaya | Rincian biaya/paket dalam bentuk tabel atau kartu harga. |
| Hitung Mundur | Timer hitung mundur ke sebuah tanggal (mis. penutupan pendaftaran). |
| Logo Partner / Akreditasi | Deretan logo mitra/akreditasi, tiap logo bisa jadi link sendiri. |
| Kutipan Tunggal | Satu kutipan besar berdiri sendiri (bukan testimoni). |
| Unduhan Dokumen | Daftar file yang bisa diunduh pengunjung (formulir, brosur, dst). |
| Tombol Scroll to Top | Tombol mengambang "kembali ke atas" — pilih warna, posisi, dan salah satu dari 5 gaya (lingkaran, kotak, pil berlabel, dst). Cukup satu blok ini per halaman. |

Definisi lengkap tiap tipe (termasuk field & varian) ada di `app/Support/BlockDefinitions.php`.

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
| GET    | `/api/pages?lang=id\|en`     | Daftar semua halaman terbit (slug, judul, meta description) — belum dipakai frontend, disediakan untuk kebutuhan lain (mis. sitemap). |
| GET    | `/api/pages/{slug}?lang=id\|en` | Detail satu halaman beserta blok-bloknya, terurut, dengan URL media yang sudah lengkap |
| GET    | `/api/menu?lang=id\|en`      | Struktur menu navbar (menu utama + sub-menu dropdown), terurut, termasuk `dropdown_style` per menu utama |
| GET    | `/api/settings?lang=id\|en`  | Pengaturan Situs (logo, nama/tagline, topbar, footer, sosmed) + Pengaturan SEO (dalam field `seo`) |
| POST   | `/api/newsletter?lang=id\|en` | Mendaftarkan satu email ke Pelanggan Buletin — body JSON `{ "email": "..." }`, dibatasi 5 percobaan/menit per IP |

Contoh respons `/api/menu`:

```json
{
  "data": [
    { "label": "Beranda", "url": "/", "open_in_new_tab": false, "dropdown_style": "simple", "children": [] },
    {
      "label": "Tentang",
      "url": null,
      "open_in_new_tab": false,
      "dropdown_style": "cards",
      "children": [
        { "label": "Tentang Kami", "url": "/about", "open_in_new_tab": false, "dropdown_style": null, "children": [] }
      ]
    }
  ]
}
```

Contoh respons `/api/settings`:

```json
{
  "data": {
    "logo": null,
    "site_name": "Al-Ihsan Islamic School",
    "site_tagline": "National & Singapore - Based Curriculum Integrated With Islamic Values",
    "topbar_message": "Selamat datang di SD Al-Ihsan Islamic School",
    "topbar_phone": "+62 813-2097-5696",
    "topbar_email": "administrasi@alihsanislamicsch.co.id",
    "footer_description": "...",
    "footer_copyright": "© {year} Al-Ihsan Islamic School — Hak cipta dilindungi.",
    "social_website": null,
    "social_facebook": null,
    "social_twitter": null,
    "seo": {
      "default_meta_description": "...",
      "default_og_image": null,
      "keywords": null,
      "google_site_verification": null,
      "canonical_domain": "https://alihsanislamicsch.co.id",
      "twitter_handle": null,
      "robots_index": true
    }
  }
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
- `blocks` — banyak baris per halaman (`type`, `order`, `is_visible`, `data` JSON). Urutan blok ditentukan kolom `order`, diubah lewat Desain Halaman atau drag & drop tabel. Setiap field teks dalam `data` punya pasangan `{field}_en` (mis. `heading` + `heading_en`) — lihat [Konten dwibahasa](#konten-dwibahasa).
- `menu_items` — satu baris per item navbar (`label` + `label_en`, `url`, `parent_id`, `order`, `is_visible`, `open_in_new_tab`, `dropdown_style`). Item dengan `parent_id` null adalah menu utama (`dropdown_style` hanya berlaku untuknya); item dengan `parent_id` terisi adalah sub-menu dropdown.
- `settings` — satu baris tunggal (dibuat otomatis saat pertama diakses) untuk Pengaturan Situs (`logo`, `site_name`, `topbar_*`, `footer_*`, `social_*`) sekaligus Pengaturan SEO (kolom berawalan `seo_*`).
- `newsletter_subscribers` — satu baris per pendaftar buletin (`email`, `locale`, `created_at`), diisi lewat `POST /api/newsletter`.

Tipe blok yang tersedia didefinisikan di `app/Support/BlockDefinitions.php` — tambahkan entri baru di sana untuk menambah tipe blok baru (form admin, termasuk kartu pratinjau varian di [Desain Halaman](#desain-halaman-page-builder), akan otomatis mengikuti). Untuk field teks yang perlu versi Inggris, gunakan `App\Filament\Support\TranslatableField::text()` / `::textarea()` / `::richEditor()` alih-alih komponen Filament biasa — ini otomatis membuatkan pasangan field `{nama}` + `{nama}_en` lengkap dengan auto-translate. Sebuah tipe blok baru juga butuh renderer React-nya sendiri di sisi frontend — lihat [Menghubungkan ke website React](#menghubungkan-ke-website-react).

## Menghubungkan ke website React

Integrasi ke website `alihsanislamicsch` **sudah selesai dikerjakan** — bukan lagi rencana. Ringkasannya, di repo frontend:

- `app/lib/cms.ts` — satu fungsi `fetch` per endpoint (`fetchCmsPage`, `fetchCmsMenu`, `fetchCmsSettings`, `subscribeToNewsletter`) plus tipe TypeScript yang cocok dengan bentuk respons di atas. `VITE_CMS_API_URL` di `.env` menentukan base URL API (`http://127.0.0.1:8001/api` untuk dev lokal, domain CMS produksi untuk build produksi).
- `app/hooks/useCmsPage.ts`, `useCmsMenu.ts`, `useCmsSettings.ts` — membungkus fungsi-fungsi itu jadi React hook yang refetch otomatis saat bahasa situs berganti (lewat `i18next`, jadi `?lang=en` terkirim otomatis, tanpa logika terjemahan sendiri di React).
- `app/components/blocks/BlockRenderer.tsx` — satu komponen React per tipe blok, dipetakan lewat `type` di objek `RENDERERS`; menerima array `blocks` dari `/api/pages/{slug}` dan merender tiap blok sesuai tipenya, dalam urutan `order`.
- `app/components/CmsPageView.tsx` (dipakai hampir semua route), plus `UnitPage.tsx` dan `VisiMisiSummary.tsx` untuk beberapa halaman Sekolah & Unit — ketiganya memanggil `useCmsPage()` lalu `BlockRenderer`, dan `useSeo()` (lihat `app/hooks/useSeo.ts`) untuk menulis `<title>`, meta description/keywords/robots, Open Graph, Twitter Card, dan link canonical ke `<head>` berdasarkan judul/deskripsi halaman itu sendiri (fallback ke Pengaturan SEO kalau kosong).
- `app/components/navbar.tsx` dan `footer.tsx` — mengambil menu & pengaturan situs lewat `useCmsMenu()`/`useCmsSettings()`; navbar merender gaya dropdown (`dropdown_style`) dan `open_in_new_tab` per menu, footer mengirim form buletin lewat `subscribeToNewsletter()`.

Halaman builder CMS (Desain Halaman & Editor Design Menu Navbar) juga memanggil frontend secara langsung: keduanya membuka route `/preview` di `alihsanislamicsch` dalam sebuah iframe, lalu mengirim draft data (blok atau menu yang belum disimpan) lewat `postMessage` — jadi pratinjau di admin **adalah** situs React sungguhan, bukan tiruan.

## Keamanan

⚠️ Akun admin awal dibuat dengan password contoh saat setup. **Segera ganti password** melalui panel admin atau `php artisan make:filament-user` sebelum digunakan di lingkungan produksi. Jangan commit file `.env` atau `database/database.sqlite`.
