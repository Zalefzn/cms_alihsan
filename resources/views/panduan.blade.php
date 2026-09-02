<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Panduan Penggunaan CMS — Al-Ihsan Islamic School</title>
    <link rel="icon" href="{{ asset('images/favicon.ico') }}">
    <style>
        :root {
            --indigo: #4f46e5;
            --indigo-dark: #1e1b4b;
            --ink: #111827;
            --muted: #6b7280;
            --border: #e5e7eb;
            --bg: #f9fafb;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            color: var(--ink);
            background: var(--bg);
        }

        .pg-topbar {
            position: sticky;
            top: 0;
            z-index: 20;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            padding: 0.85rem 1.5rem;
            background: var(--indigo-dark);
            color: #fff;
        }

        .pg-brand {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            text-decoration: none;
            color: #fff;
        }

        .pg-brand img {
            height: 2.25rem;
            width: 2.25rem;
            border-radius: 0.5rem;
            background: #fff;
            padding: 0.2rem;
        }

        .pg-brand-text {
            line-height: 1.25;
        }

        .pg-brand-text strong {
            display: block;
            font-size: 0.95rem;
        }

        .pg-brand-text span {
            display: block;
            font-size: 0.75rem;
            color: #c7d2fe;
        }

        .pg-back {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.5rem 0.9rem;
            border-radius: 0.5rem;
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 600;
            transition: background-color 150ms ease;
        }

        .pg-back:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        .pg-hero {
            background: linear-gradient(155deg, #ffffff 0%, #f3e8ff 35%, #ddd6fe 60%, #f9a8d4 100%);
            padding: 3rem 1.5rem 2.5rem;
            text-align: center;
        }

        .pg-hero h1 {
            margin: 0 0 0.5rem;
            font-size: 1.85rem;
            font-weight: 800;
        }

        .pg-hero p {
            margin: 0;
            color: #374151;
            font-size: 1rem;
            max-width: 40rem;
            margin-inline: auto;
        }

        .pg-layout {
            max-width: 68rem;
            margin: 0 auto;
            padding: 2.5rem 1.5rem 5rem;
            display: grid;
            grid-template-columns: 1fr;
            gap: 2rem;
        }

        .pg-toc {
            border: 1px solid var(--border);
            background: #fff;
            border-radius: 0.85rem;
            padding: 1.25rem 1.5rem;
            align-self: start;
        }

        .pg-toc h2 {
            margin: 0 0 0.75rem;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--muted);
        }

        .pg-toc ol {
            margin: 0;
            padding-left: 1.1rem;
            display: grid;
            gap: 0.4rem;
        }

        .pg-toc a {
            color: var(--indigo);
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .pg-toc a:hover {
            text-decoration: underline;
        }

        .pg-content section {
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 0.85rem;
            padding: 1.75rem 2rem;
            margin-bottom: 1.5rem;
            scroll-margin-top: 5rem;
        }

        .pg-content h2 {
            margin: 0 0 0.9rem;
            font-size: 1.25rem;
            display: flex;
            align-items: center;
            gap: 0.6rem;
        }

        .pg-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            height: 1.75rem;
            width: 1.75rem;
            border-radius: 9999px;
            background: var(--indigo);
            color: #fff;
            font-size: 0.85rem;
            font-weight: 700;
            flex-shrink: 0;
        }

        .pg-content h3 {
            font-size: 1rem;
            margin: 1.25rem 0 0.5rem;
            color: var(--ink);
        }

        .pg-content p {
            line-height: 1.7;
            color: #374151;
            margin: 0 0 0.75rem;
        }

        .pg-content ul, .pg-content ol.pg-steps {
            line-height: 1.75;
            color: #374151;
            padding-left: 1.25rem;
            margin: 0 0 0.75rem;
        }

        .pg-content li {
            margin-bottom: 0.3rem;
        }

        .pg-note {
            background: #eef2ff;
            border: 1px solid #c7d2fe;
            border-radius: 0.6rem;
            padding: 0.85rem 1rem;
            font-size: 0.9rem;
            color: #3730a3;
            margin: 0.75rem 0;
        }

        .pg-table {
            width: 100%;
            border-collapse: collapse;
            margin: 0.75rem 0;
            font-size: 0.88rem;
        }

        .pg-table th, .pg-table td {
            text-align: left;
            padding: 0.55rem 0.75rem;
            border-bottom: 1px solid var(--border);
            vertical-align: top;
        }

        .pg-table th {
            color: var(--muted);
            font-weight: 600;
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }

        .pg-role {
            display: inline-block;
            padding: 0.15rem 0.55rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 700;
        }

        .pg-role-admin {
            background: #ede9fe;
            color: #5b21b6;
        }

        .pg-role-editor {
            background: #dcfce7;
            color: #166534;
        }

        .pg-footer {
            text-align: center;
            padding: 2rem 1.5rem 3rem;
            color: var(--muted);
            font-size: 0.85rem;
        }

        @media (min-width: 900px) {
            .pg-layout {
                grid-template-columns: 15rem 1fr;
                align-items: start;
            }

            .pg-toc {
                position: sticky;
                top: 5.5rem;
            }
        }
    </style>
</head>
<body>
    <header class="pg-topbar">
        <a href="{{ route('panduan') }}" class="pg-brand">
            <img src="{{ asset('images/logo.png') }}" alt="Al-Ihsan Islamic School">
            <span class="pg-brand-text">
                <strong>Al-Ihsan Islamic School</strong>
                <span>Panduan Penggunaan CMS</span>
            </span>
        </a>
        <a href="{{ url('/admin/login') }}" class="pg-back">&larr; Kembali ke Halaman Masuk</a>
    </header>

    <section class="pg-hero">
        <h1>Panduan Penggunaan CMS</h1>
        <p>Panduan singkat untuk admin dan editor dalam mengelola konten website Al-Ihsan Islamic School melalui panel admin ini.</p>
    </section>

    <div class="pg-layout">
        <nav class="pg-toc">
            <h2>Daftar Isi</h2>
            <ol>
                <li><a href="#masuk">1. Masuk ke Panel Admin</a></li>
                <li><a href="#dasbor">2. Mengenal Dasbor</a></li>
                <li><a href="#halaman">3. Mengelola Halaman</a></li>
                <li><a href="#blok">4. Blok Konten</a></li>
                <li><a href="#menu">5. Menu Navigasi (Navbar)</a></li>
                <li><a href="#pengguna">6. Pengguna &amp; Peran</a></li>
                <li><a href="#notifikasi">7. Notifikasi &amp; Konfirmasi</a></li>
                <li><a href="#tips">8. Tips &amp; Trik</a></li>
                <li><a href="#bantuan">9. Butuh Bantuan?</a></li>
            </ol>
        </nav>

        <main class="pg-content">
            <section id="masuk">
                <h2><span class="pg-badge">1</span> Masuk ke Panel Admin</h2>
                <p>Buka halaman <code>/admin/login</code>, lalu masukkan alamat email dan kata sandi akun Anda. Centang "Ingat saya" jika ingin tetap masuk di perangkat ini.</p>
                <p>Jika lupa kata sandi, hubungi Super Admin untuk mengatur ulang melalui menu <strong>Pengguna</strong>.</p>
            </section>

            <section id="dasbor">
                <h2><span class="pg-badge">2</span> Mengenal Dasbor</h2>
                <p>Setelah masuk, Anda akan melihat Dasbor yang menampilkan ringkasan:</p>
                <ul>
                    <li><strong>Total Halaman</strong> — jumlah halaman website beserta status terbit.</li>
                    <li><strong>Total Blok Konten</strong> — jumlah blok konten di seluruh halaman.</li>
                    <li><strong>Menu Navbar</strong> — jumlah menu utama dan sub menu yang aktif.</li>
                    <li><strong>Sebaran Tipe Blok</strong> — grafik jenis blok konten yang paling sering dipakai.</li>
                    <li><strong>Halaman Terbaru Diperbarui</strong> — daftar halaman yang baru saja diubah.</li>
                </ul>
                <p>Ikon lonceng di pojok kanan atas menampilkan notifikasi, misalnya saat ada halaman baru dibuat atau diterbitkan.</p>
            </section>

            <section id="halaman">
                <h2><span class="pg-badge">3</span> Mengelola Halaman</h2>
                <p>Menu <strong>Halaman</strong> di sidebar berisi setiap halaman website (Beranda, Tentang Kami, Kontak, dst). Klik langsung nama halaman di sidebar, atau buka <strong>Semua Halaman</strong> untuk melihat daftar lengkap dengan kolom pencarian.</p>

                <h3>Membuat halaman baru</h3>
                <ol class="pg-steps">
                    <li>Klik <strong>Buat Halaman</strong> di kanan atas daftar halaman.</li>
                    <li>Isi <strong>Judul Halaman</strong> — slug (identitas URL) akan terisi otomatis, bisa diubah manual bila perlu.</li>
                    <li>Isi <strong>Deskripsi Meta (SEO)</strong> untuk membantu mesin pencari memahami isi halaman.</li>
                    <li>Pilih <strong>Ikon di Sidebar</strong> agar halaman mudah dikenali di menu.</li>
                    <li>Aktifkan <strong>Publikasikan</strong> jika halaman siap tampil di website publik.</li>
                </ol>

                <div class="pg-note">
                    Setiap kolom teks (judul, deskripsi, dsb.) punya pasangan versi Bahasa Inggris. Isi versi Indonesia lalu klik di luar kolom (blur) — versi Inggris akan terisi otomatis lewat terjemahan mesin. Anda tetap bisa menyunting hasilnya secara manual, atau klik <strong>Terjemahkan</strong> untuk menerjemahkan ulang.
                </div>

                <h3>Mengelola isi halaman</h3>
                <p>Klik <strong>Kelola Konten</strong> pada baris halaman untuk membuka susunan bloknya. Di sana Anda bisa menambah, mengurutkan (drag & drop), menyunting, atau menghapus blok konten.</p>
            </section>

            <section id="blok">
                <h2><span class="pg-badge">4</span> Blok Konten</h2>
                <p>Setiap halaman disusun dari satu atau lebih blok konten. Tersedia 11 jenis blok:</p>
                <table class="pg-table">
                    <thead>
                        <tr><th>Blok</th><th>Kegunaan</th></tr>
                    </thead>
                    <tbody>
                        <tr><td>Hero (Banner Utama)</td><td>Banner besar di atas halaman: judul, sub-judul, gambar, dan satu tombol.</td></tr>
                        <tr><td>Teks / Paragraf</td><td>Paragraf bebas dengan format (bold, list, dll) untuk penjelasan panjang.</td></tr>
                        <tr><td>Galeri Gambar</td><td>Kumpulan foto berjejer, misalnya dokumentasi kegiatan.</td></tr>
                        <tr><td>Video</td><td>Satu video — tempel link YouTube/Vimeo atau unggah file sendiri.</td></tr>
                        <tr><td>CTA (Ajakan Bertindak)</td><td>Kotak ajakan singkat dengan satu tombol, misalnya ajakan mendaftar.</td></tr>
                        <tr><td>FAQ (Tanya Jawab)</td><td>Daftar pertanyaan yang bisa dibuka-tutup beserta jawabannya.</td></tr>
                        <tr><td>Tim / Guru</td><td>Daftar orang (guru, staf, pengurus) lengkap dengan foto dan jabatan.</td></tr>
                        <tr><td>Testimoni</td><td>Kutipan/ulasan dari orang tua, alumni, atau siswa.</td></tr>
                        <tr><td>Info Kontak</td><td>Alamat, telepon, email, dan peta lokasi sekolah.</td></tr>
                        <tr><td>Statistik / Angka</td><td>Angka pencapaian, misalnya jumlah siswa atau tahun berdiri.</td></tr>
                        <tr><td>Daftar Fitur / Program</td><td>Daftar keunggulan atau program dengan judul dan deskripsi singkat.</td></tr>
                    </tbody>
                </table>
                <p>Untuk menambah blok: buka halaman terkait &rarr; <strong>Kelola Konten</strong> &rarr; <strong>Tambah Blok</strong> &rarr; pilih jenis blok &rarr; isi isinya &rarr; simpan.</p>
            </section>

            <section id="menu">
                <h2><span class="pg-badge">5</span> Menu Navigasi (Navbar)</h2>
                <p>Menu <strong>Menu Navbar</strong> mengatur tautan yang tampil di navbar website publik.</p>
                <ul>
                    <li>Setiap menu utama bisa memiliki beberapa <strong>Sub Menu</strong> (tampil sebagai dropdown).</li>
                    <li>Urutan menu bisa diubah dengan menyeret (drag & drop) baris pada daftar.</li>
                    <li>Aktifkan <strong>Tampilkan di navbar</strong> untuk memunculkan/menyembunyikan menu tanpa perlu menghapusnya.</li>
                    <li>Aktifkan <strong>Buka di tab baru</strong> untuk tautan yang menuju situs/halaman lain.</li>
                    <li>Kosongkan kolom <strong>Link</strong> jika menu tersebut hanya berfungsi sebagai induk dropdown.</li>
                </ul>
            </section>

            <section id="pengguna">
                <h2><span class="pg-badge">6</span> Pengguna &amp; Peran</h2>
                <p>Menu <strong>Pengguna</strong> (grup "Pengguna & Peran") mengatur siapa saja yang bisa masuk ke panel admin, dan menu <strong>Peran & Izin</strong> mengatur hak akses tiap peran. Tersedia dua peran bawaan:</p>
                <table class="pg-table">
                    <thead>
                        <tr><th>Peran</th><th>Akses</th></tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><span class="pg-role pg-role-admin">Super Admin</span></td>
                            <td>Akses penuh ke semua fitur: halaman, blok, menu navbar, pengguna, dan peran.</td>
                        </tr>
                        <tr>
                            <td><span class="pg-role pg-role-editor">Editor</span></td>
                            <td>Hanya bisa mengelola isi halaman (blok konten), tidak bisa mengubah menu navbar, pengguna, atau peran.</td>
                        </tr>
                    </tbody>
                </table>
                <p>Untuk menambah pengguna baru: buka <strong>Pengguna</strong> &rarr; <strong>Buat Pengguna</strong> &rarr; isi nama, email, kata sandi, lalu pilih peran yang sesuai.</p>
            </section>

            <section id="notifikasi">
                <h2><span class="pg-badge">7</span> Notifikasi &amp; Konfirmasi</h2>
                <p>Setiap kali Anda menyimpan, menghapus, atau mengubah data, sistem akan menampilkan pesan konfirmasi (toast) di pojok layar sebagai tanda aksi berhasil atau gagal.</p>
                <p>Aksi hapus data akan selalu meminta konfirmasi terlebih dahulu, karena data yang sudah dihapus tidak bisa dikembalikan.</p>
                <p>Jika Anda mencoba membuka halaman yang tidak berhak diakses (Editor mencoba membuka menu Pengguna, misalnya), sistem akan menampilkan halaman <strong>403 (Tidak Diizinkan)</strong>. Halaman yang sudah dihapus/tidak ditemukan akan menampilkan halaman <strong>404</strong>.</p>
            </section>

            <section id="tips">
                <h2><span class="pg-badge">8</span> Tips &amp; Trik</h2>
                <ul>
                    <li>Gunakan kolom <strong>Cari</strong> di atas setiap daftar tabel untuk menemukan data dengan cepat, dan ikon corong (filter) untuk menyaring berdasarkan status.</li>
                    <li>Klik judul kolom (mis. "Judul", "Diperbarui") untuk mengurutkan tabel.</li>
                    <li>Ikon di sidebar sengaja dibuat berbeda-beda per halaman/menu agar mudah dikenali sekilas.</li>
                    <li>Gunakan tombol panah di kiri atas sidebar untuk mempersempit/melebarkan sidebar di layar besar.</li>
                    <li>Di perangkat mobile, sidebar otomatis berubah menjadi menu geser (drawer) yang bisa dibuka lewat ikon garis tiga di pojok kiri atas.</li>
                </ul>
            </section>

            <section id="bantuan">
                <h2><span class="pg-badge">9</span> Butuh Bantuan?</h2>
                <p>Jika mengalami kendala teknis yang tidak tercakup dalam panduan ini, hubungi Super Admin atau tim pengembang website sekolah.</p>
            </section>
        </main>
    </div>

    <footer class="pg-footer">
        &copy; {{ date('Y') }} Al-Ihsan Islamic School — Panduan Penggunaan CMS
    </footer>
</body>
</html>
