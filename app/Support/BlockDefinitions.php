<?php

namespace App\Support;

use App\Filament\Support\TranslatableField;
use Filament\Forms;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Central definition of every block "type" a page can be built from:
 * its label + plain-language description (shown in the type picker)
 * and the form schema used to edit its content. Each field is stored
 * under the block's `data` JSON column, addressed by dot-notation
 * (e.g. "data.heading").
 *
 * Text fields are bilingual: each `data.foo` field is paired with a
 * `data.foo_en` sibling (see TranslatableField), auto-filled with a
 * machine-translated draft so the React site can serve English
 * content without every field being typed twice from scratch.
 */
class BlockDefinitions
{
    public static function options(): array
    {
        return collect(self::all())->mapWithKeys(
            fn (array $definition, string $type) => [$type => $definition['label']]
        )->all();
    }

    public static function descriptions(): array
    {
        return collect(self::all())->mapWithKeys(
            fn (array $definition, string $type) => [$type => $definition['description']]
        )->all();
    }

    public static function schemaFor(string $type): array
    {
        $schema = self::all()[$type]['schema'] ?? [];
        if ($schema !== []) {
            $schema[] = self::customDataField();
        }

        return $schema;
    }

    /** Named visual layouts a block type can be rendered as, keyed by type. Empty array = no variant choice. */
    public static function variantOptions(string $type): array
    {
        return self::all()[$type]['variants'] ?? [];
    }

    /**
     * Free-form key/value data every block type can carry, for developer-defined overrides
     * that don't have a dedicated field yet. Stored under data.custom and passed through
     * verbatim to the frontend — has no built-in meaning until a component reads specific keys.
     */
    protected static function customDataField(): Forms\Components\KeyValue
    {
        return Forms\Components\KeyValue::make('data.custom')
            ->label('Data Kustom (opsional)')
            ->helperText('Pasangan kunci-nilai bebas untuk kebutuhan khusus (mis. penyesuaian tampilan) yang belum ada field-nya — dibaca developer di frontend sebagai data.custom. Kosongkan jika tidak perlu.')
            ->keyLabel('Kunci')
            ->valueLabel('Nilai')
            ->reorderable()
            ->addActionLabel('Tambah data kustom')
            ->columnSpanFull();
    }

    public static function all(): array
    {
        return [
            'hero' => [
                'label' => 'Hero (Banner Utama)',
                'description' => 'Banner besar di paling atas halaman — judul utama, sub-judul, dan satu tombol.',
                'variants' => [
                    'center' => 'Pusat + Gelombang (Standar)',
                    'split' => 'Split — Teks Kiri, Foto Kanan',
                    'minimal' => 'Pusat Tanpa Gelombang',
                    'banner' => 'Banner Ramping (Padding Minim)',
                    'fullscreen' => 'Layar Penuh (Ukuran Besar)',
                ],
                'schema' => [
                    ...TranslatableField::text('data.heading', 'Judul', required: true, placeholder: 'Pendidikan Terbaik untuk Anak Anda'),
                    ...TranslatableField::textarea('data.subheading', 'Sub Judul', placeholder: 'Kalimat pendukung di bawah judul utama'),
                    ...TranslatableField::text('data.cta_text', 'Teks Tombol', placeholder: 'Daftar Sekarang'),
                    Forms\Components\TextInput::make('data.cta_link')
                        ->label('Link Tombol')
                        ->url()
                        ->prefixIcon('heroicon-o-link')
                        ->placeholder('/penerimaan')
                        ->columnSpanFull(),
                    Forms\Components\FileUpload::make('data.image')
                        ->label('Gambar')
                        ->image()
                        ->panelLayout('integrated')
                        ->imagePreviewHeight('250')
                        ->directory('blocks')
                        ->live()
                        ->columnSpanFull(),
                ],
            ],
            'rich_text' => [
                'label' => 'Teks / Paragraf',
                'description' => 'Teks bebas untuk paragraf atau penjelasan panjang, bisa diberi format (bold, list, dll).',
                'variants' => [
                    'standard' => 'Tengah (Standar)',
                    'left' => 'Rata Kiri',
                    'two_column' => 'Dua Kolom Teks',
                    'boxed' => 'Kotak Bertepi (Card)',
                    'highlight' => 'Kotak Warna Sorot',
                ],
                'schema' => [
                    ...TranslatableField::text('data.heading', 'Judul (opsional)', placeholder: 'Tentang Kami'),
                    ...TranslatableField::richEditor('data.body', 'Isi', required: true),
                ],
            ],
            'image_gallery' => [
                'label' => 'Galeri Gambar',
                'description' => 'Menampilkan kumpulan foto berjejer, misalnya dokumentasi kegiatan.',
                'variants' => [
                    'grid' => 'Grid (Standar)',
                    'carousel' => 'Carousel — Geser Horizontal',
                    'masonry' => 'Masonry — Tinggi Bervariasi',
                    'columns_2' => 'Grid 2 Kolom Besar',
                    'strip' => 'Strip Geser Tipis',
                ],
                'schema' => [
                    ...TranslatableField::text('data.caption', 'Keterangan (opsional)', placeholder: 'Dokumentasi kegiatan 2026'),
                    Forms\Components\FileUpload::make('data.images')
                        ->label('Gambar-gambar')
                        ->image()
                        ->multiple()
                        ->reorderable()
                        ->panelLayout('grid')
                        ->directory('blocks')
                        ->live()
                        ->columnSpanFull(),
                ],
            ],
            'video' => [
                'label' => 'Video',
                'description' => 'Menampilkan satu video — bisa link YouTube/Vimeo, atau unggah file sendiri.',
                'variants' => [
                    'standard' => 'Standar (dengan judul)',
                    'compact' => 'Ramping (tanpa judul)',
                    'side_by_side' => 'Video + Teks Berdampingan',
                    'background' => 'Latar Belakang Penuh + Overlay',
                    'framed' => 'Berbingkai Dekoratif',
                ],
                'schema' => [
                    ...TranslatableField::text('data.caption', 'Keterangan (opsional)', placeholder: 'Profil sekolah dalam 2 menit'),
                    ...TranslatableField::textarea('data.body', 'Teks Pendamping (opsional, dipakai varian Berdampingan)', placeholder: 'Penjelasan singkat tentang video ini'),
                    Forms\Components\TextInput::make('data.embed_url')
                        ->label('Link YouTube / Vimeo')
                        ->helperText('Isi ini ATAU unggah file video di bawah, tidak perlu keduanya.')
                        ->url()
                        ->prefixIcon('heroicon-o-play-circle')
                        ->placeholder('https://youtube.com/watch?v=...')
                        ->columnSpanFull(),
                    Forms\Components\FileUpload::make('data.video')
                        ->label('Unggah File Video')
                        ->acceptedFileTypes(['video/mp4', 'video/webm', 'video/ogg'])
                        ->panelLayout('integrated')
                        ->imagePreviewHeight('250')
                        ->directory('blocks')
                        ->live()
                        ->columnSpanFull(),
                ],
            ],
            'cta' => [
                'label' => 'CTA (Ajakan Bertindak)',
                'description' => 'Kotak ajakan singkat dengan satu tombol, misalnya mendorong pengunjung untuk mendaftar.',
                'variants' => [
                    'plain' => 'Polos (Standar)',
                    'banner' => 'Banner Warna',
                    'split' => 'Teks Kiri, Tombol Kanan',
                    'gradient' => 'Gradasi Warna',
                    'boxed_card' => 'Kartu Terpisah',
                ],
                'schema' => [
                    ...TranslatableField::text('data.heading', 'Judul', required: true, placeholder: 'Pendaftaran 2026 Sudah Dibuka'),
                    ...TranslatableField::textarea('data.body', 'Isi', placeholder: 'Ajakan singkat untuk mendorong pengunjung bertindak'),
                    ...TranslatableField::text('data.button_text', 'Teks Tombol', placeholder: 'Daftar Sekarang'),
                    Forms\Components\TextInput::make('data.button_link')
                        ->label('Link Tombol')
                        ->url()
                        ->prefixIcon('heroicon-o-link')
                        ->placeholder('/penerimaan')
                        ->columnSpanFull(),
                ],
            ],
            'faq' => [
                'label' => 'FAQ (Tanya Jawab)',
                'description' => 'Daftar pertanyaan yang bisa dibuka-tutup, beserta jawabannya.',
                'variants' => [
                    'accordion' => 'Akordeon (Standar)',
                    'grid' => 'Grid Dua Kolom',
                    'two_column' => 'Akordeon Dua Kolom',
                    'minimal_list' => 'List Polos Tanpa Akordeon',
                    'boxed' => 'Akordeon Dalam Kartu',
                ],
                'schema' => [
                    ...TranslatableField::text('data.heading', 'Judul (opsional)', placeholder: 'Pertanyaan yang Sering Diajukan'),
                    Forms\Components\Repeater::make('data.items')->live()
                        ->label('Daftar Pertanyaan')
                        ->schema([
                            ...TranslatableField::text('question', 'Pertanyaan', required: true, placeholder: 'Bagaimana cara mendaftar?'),
                            ...TranslatableField::textarea('answer', 'Jawaban', required: true, placeholder: 'Jelaskan jawabannya di sini'),
                        ])
                        ->columns(2)
                        ->reorderable()
                        ->collapsible()
                        ->itemLabel(fn (array $state): ?string => $state['question'] ?? null)
                        ->columnSpanFull(),
                ],
            ],
            'team' => [
                'label' => 'Tim / Guru',
                'description' => 'Daftar orang (guru, staff, pengurus) lengkap dengan foto dan jabatan.',
                'variants' => [
                    'grid' => 'Grid Bulat (Standar)',
                    'list' => 'Kartu List Horizontal',
                    'compact_grid' => 'Grid Rapat (Lebih Banyak per Baris)',
                    'carousel' => 'Carousel Geser',
                    'minimal' => 'Minimalis (Teks + Foto Kecil)',
                ],
                'schema' => [
                    ...TranslatableField::text('data.heading', 'Judul (opsional)', placeholder: 'Guru & Staff Kami'),
                    Forms\Components\Repeater::make('data.items')->live()
                        ->label('Daftar Anggota')
                        ->schema([
                            Forms\Components\FileUpload::make('photo')
                                ->label('Foto')
                                ->image()
                                ->avatar()
                                ->directory('blocks/team')
                                ->live()
                                ->columnSpanFull(),
                            Forms\Components\TextInput::make('name')
                                ->label('Nama')
                                ->required()
                                ->prefixIcon('heroicon-o-user')
                                ->placeholder('Nama lengkap')
                                ->columnSpanFull(),
                            ...TranslatableField::text('role', 'Jabatan', placeholder: 'Kepala Sekolah'),
                        ])
                        ->columns(2)
                        ->reorderable()
                        ->collapsible()
                        ->itemLabel(fn (array $state): ?string => $state['name'] ?? null)
                        ->columnSpanFull(),
                ],
            ],
            'testimonials' => [
                'label' => 'Testimoni',
                'description' => 'Kutipan/ulasan dari orang tua, alumni, atau siswa, lengkap dengan foto.',
                'variants' => [
                    'carousel' => 'Carousel Geser (Standar)',
                    'grid' => 'Grid Statis',
                    'single_featured' => 'Satu Kutipan Besar Bergantian',
                    'masonry' => 'Masonry Bertumpuk',
                    'minimal_quote' => 'Kutipan Polos Tanpa Kartu',
                ],
                'schema' => [
                    ...TranslatableField::text('data.heading', 'Judul (opsional)', placeholder: 'Apa Kata Mereka'),
                    Forms\Components\Repeater::make('data.items')->live()
                        ->label('Daftar Testimoni')
                        ->schema([
                            Forms\Components\FileUpload::make('photo')
                                ->label('Foto')
                                ->image()
                                ->avatar()
                                ->directory('blocks/testimonials')
                                ->live()
                                ->columnSpanFull(),
                            Forms\Components\TextInput::make('name')
                                ->label('Nama')
                                ->required()
                                ->prefixIcon('heroicon-o-user')
                                ->placeholder('Nama lengkap')
                                ->columnSpanFull(),
                            ...TranslatableField::text('role', 'Peran / Angkatan', placeholder: 'Orang Tua Siswa / Angkatan 2024'),
                            ...TranslatableField::textarea('quote', 'Kutipan', required: true, placeholder: 'Tulis kutipan/ulasannya di sini'),
                        ])
                        ->columns(2)
                        ->reorderable()
                        ->collapsible()
                        ->itemLabel(fn (array $state): ?string => $state['name'] ?? null)
                        ->columnSpanFull(),
                ],
            ],
            'contact_info' => [
                'label' => 'Info Kontak',
                'description' => 'Menampilkan alamat, telepon, email, dan peta lokasi sekolah.',
                'variants' => [
                    'standard' => 'Berdampingan (Standar)',
                    'stacked' => 'Bertumpuk (Peta di Bawah)',
                    'cards' => 'Setiap Info Jadi Kartu',
                    'sidebar' => 'Sidebar Warna + Peta',
                    'minimal' => 'Minimalis Tanpa Peta',
                ],
                'schema' => [
                    ...TranslatableField::textarea('data.address', 'Alamat', placeholder: 'Jl. Contoh No. 1, Kota, Provinsi'),
                    Forms\Components\TextInput::make('data.phone')
                        ->label('Telepon')
                        ->tel()
                        ->prefixIcon('heroicon-o-phone')
                        ->placeholder('021-1234567'),
                    Forms\Components\TextInput::make('data.email')
                        ->label('Email')
                        ->email()
                        ->prefixIcon('heroicon-o-envelope')
                        ->placeholder('info@alihsanislamicsch.co.id'),
                    Forms\Components\Textarea::make('data.map_embed')
                        ->label('URL Embed Google Maps')
                        ->rows(2)
                        ->placeholder('https://maps.google.com/...')
                        ->columnSpanFull(),
                ],
            ],
            'stats' => [
                'label' => 'Statistik / Angka',
                'description' => 'Angka-angka pencapaian, misalnya jumlah siswa atau tahun berdiri.',
                'variants' => [
                    'inline' => 'Sejajar (Standar)',
                    'cards' => 'Kartu Terpisah',
                    'circular' => 'Lingkaran Angka',
                    'gradient_band' => 'Pita Gradasi Selebar Halaman',
                    'bordered_grid' => 'Grid Bertepi',
                ],
                'schema' => [
                    Forms\Components\Repeater::make('data.items')->live()
                        ->label('Daftar Angka')
                        ->schema([
                            ...TranslatableField::text('label', 'Label', required: true, placeholder: 'Jumlah Siswa'),
                            Forms\Components\TextInput::make('value')
                                ->label('Nilai')
                                ->required()
                                ->placeholder('500+')
                                ->columnSpanFull(),
                        ])
                        ->columns(2)
                        ->reorderable()
                        ->columnSpanFull(),
                ],
            ],
            'feature_list' => [
                'label' => 'Daftar Fitur / Program',
                'description' => 'Daftar keunggulan atau program, masing-masing dengan judul singkat, deskripsi, dan ikon.',
                'variants' => [
                    'grid' => 'Grid Kartu (Standar)',
                    'list' => 'List Bernomor',
                    'timeline' => 'Garis Waktu Vertikal',
                    'icons_row' => 'Baris Ikon Horizontal',
                    'alternating' => 'Zig-zag Kiri-Kanan',
                ],
                'schema' => [
                    ...TranslatableField::text('data.heading', 'Judul (opsional)', placeholder: 'Program Unggulan'),
                    ...TranslatableField::text('data.subheading', 'Sub Judul (opsional)', placeholder: 'Kalimat pendukung di bawah judul'),
                    Forms\Components\Repeater::make('data.items')->live()
                        ->label('Daftar Item')
                        ->schema([
                            ...TranslatableField::text('title', 'Judul Item', required: true, placeholder: 'Tahfidz Al-Quran'),
                            ...TranslatableField::textarea('description', 'Deskripsi', placeholder: 'Penjelasan singkat tentang item ini'),
                            Forms\Components\Select::make('icon')
                                ->label('Ikon')
                                ->options(self::iconOptions())
                                ->native(false)
                                ->columnSpanFull(),
                            ...TranslatableField::text('link_text', 'Teks Tombol (opsional)', placeholder: 'Selengkapnya'),
                            Forms\Components\TextInput::make('link')
                                ->label('Link Tombol (opsional)')
                                ->url()
                                ->prefixIcon('heroicon-o-link')
                                ->placeholder('/sekolah-unit/tk'),
                        ])
                        ->columns(2)
                        ->reorderable()
                        ->collapsible()
                        ->itemLabel(fn (array $state): ?string => $state['title'] ?? null)
                        ->columnSpanFull(),
                ],
            ],
            'photo_feature' => [
                'label' => 'Foto + Teks',
                'description' => 'Foto di satu sisi, judul/teks/daftar singkat/tombol di sisi lain — cocok untuk profil singkat atau ajakan bergabung.',
                'variants' => [
                    'standard' => 'Dua Kolom (Standar)',
                    'overlay' => 'Foto Latar Belakang Penuh',
                    'stacked' => 'Foto Atas, Teks Bawah',
                    'side_card' => 'Kartu Mengambang di Tepi Foto',
                    'minimal' => 'Polos Tanpa Dekorasi',
                ],
                'schema' => [
                    ...TranslatableField::text('data.heading', 'Judul', required: true, placeholder: 'Kenali Lebih Dekat Al-Ihsan'),
                    ...TranslatableField::textarea('data.body', 'Isi', rows: 4, placeholder: 'Penjelasan singkat'),
                    Forms\Components\Select::make('data.image_position')
                        ->label('Posisi Foto')
                        ->options(['right' => 'Kanan', 'left' => 'Kiri'])
                        ->default('right')
                        ->native(false),
                    Forms\Components\FileUpload::make('data.image')
                        ->label('Foto')
                        ->image()
                        ->panelLayout('integrated')
                        ->imagePreviewHeight('200')
                        ->directory('blocks')
                        ->live()
                        ->columnSpanFull(),
                    Forms\Components\Repeater::make('data.features')->live()
                        ->label('Daftar Singkat (opsional, tampil sebagai chip kecil)')
                        ->schema([
                            ...TranslatableField::text('label', 'Teks', required: true, placeholder: 'Lingkungan belajar yang aman'),
                        ])
                        ->reorderable()
                        ->columnSpanFull(),
                    ...TranslatableField::text('data.cta_text', 'Teks Tombol (opsional)', placeholder: 'Daftar Sekarang'),
                    Forms\Components\TextInput::make('data.cta_link')
                        ->label('Link Tombol')
                        ->url()
                        ->prefixIcon('heroicon-o-link')
                        ->placeholder('/penerimaan')
                        ->columnSpanFull(),
                ],
            ],
            'video_feature' => [
                'label' => 'Video + Teks',
                'description' => 'Video di satu sisi, judul/teks/daftar singkat/tombol di sisi lain — cocok untuk profil sekolah dalam bentuk video atau ajakan bergabung.',
                'variants' => [
                    'standard' => 'Dua Kolom (Standar)',
                    'stacked' => 'Video Atas, Teks Bawah',
                    'side_card' => 'Kartu Teks Mengambang di Tepi Video',
                    'minimal' => 'Polos Tanpa Dekorasi',
                    'framed' => 'Video Berbingkai Dekoratif',
                ],
                'schema' => [
                    ...TranslatableField::text('data.heading', 'Judul', required: true, placeholder: 'Kenali Lebih Dekat Al-Ihsan Lewat Video'),
                    ...TranslatableField::textarea('data.body', 'Isi', rows: 4, placeholder: 'Penjelasan singkat'),
                    Forms\Components\Select::make('data.video_position')
                        ->label('Posisi Video')
                        ->options(['right' => 'Kanan', 'left' => 'Kiri'])
                        ->default('right')
                        ->native(false),
                    Forms\Components\TextInput::make('data.embed_url')
                        ->label('Link YouTube / Vimeo')
                        ->helperText('Isi ini ATAU unggah file video di bawah, tidak perlu keduanya.')
                        ->url()
                        ->prefixIcon('heroicon-o-play-circle')
                        ->placeholder('https://youtube.com/watch?v=...')
                        ->live(onBlur: true)
                        ->columnSpanFull(),
                    Forms\Components\FileUpload::make('data.video')
                        ->label('Unggah File Video (opsional)')
                        ->acceptedFileTypes(['video/mp4', 'video/webm', 'video/ogg'])
                        ->directory('blocks')
                        ->live()
                        ->columnSpanFull(),
                    Forms\Components\Repeater::make('data.features')->live()
                        ->label('Daftar Singkat (opsional, tampil sebagai chip kecil)')
                        ->schema([
                            ...TranslatableField::text('label', 'Teks', required: true, placeholder: 'Lingkungan belajar yang aman'),
                        ])
                        ->reorderable()
                        ->columnSpanFull(),
                    ...TranslatableField::text('data.cta_text', 'Teks Tombol (opsional)', placeholder: 'Daftar Sekarang'),
                    Forms\Components\TextInput::make('data.cta_link')
                        ->label('Link Tombol')
                        ->url()
                        ->prefixIcon('heroicon-o-link')
                        ->placeholder('/penerimaan')
                        ->columnSpanFull(),
                ],
            ],
            'about_split' => [
                'label' => 'Tentang + Visi & Misi',
                'description' => 'Dua kolom: profil singkat di kiri, Visi & Misi di kanan — gaya editorial bersih tanpa foto.',
                'variants' => [
                    'columns' => 'Dua Kolom (Standar)',
                    'stacked' => 'Bertumpuk Vertikal',
                    'tabs' => 'Visi & Misi Sebagai Tab',
                    'timeline' => 'Misi Sebagai Garis Waktu',
                    'cards' => 'Visi & Misi Masing-masing Kartu',
                ],
                'schema' => [
                    ...TranslatableField::text('data.heading', 'Judul Kiri', required: true, placeholder: 'Tentang Kami'),
                    ...TranslatableField::textarea('data.body', 'Isi Kiri', rows: 5, required: true, placeholder: 'Penjelasan singkat tentang sekolah'),
                    ...TranslatableField::text('data.vision_heading', 'Judul Visi', placeholder: 'Visi Kami'),
                    ...TranslatableField::textarea('data.vision_text', 'Isi Visi', rows: 3, placeholder: 'Pernyataan visi sekolah'),
                    ...TranslatableField::text('data.mission_heading', 'Judul Misi', placeholder: 'Misi Kami'),
                    Forms\Components\Repeater::make('data.mission_items')->live()
                        ->label('Daftar Misi')
                        ->schema([
                            ...TranslatableField::text('text', 'Poin Misi', required: true, placeholder: 'Menerapkan ajaran islam pada semua aktivitas sekolah.'),
                        ])
                        ->reorderable()
                        ->columnSpanFull(),
                ],
            ],
            'program_cards' => [
                'label' => 'Kartu Program',
                'description' => 'Grid kartu berwarna untuk menampilkan jenjang/program — foto (mengintip di atas kartu), judul, deskripsi, dan warna.',
                'variants' => [
                    'colorful' => 'Kartu Warna (Standar)',
                    'minimal' => 'Kartu Minimalis',
                    'horizontal' => 'Kartu List Horizontal',
                    'bordered' => 'Kartu Bertepi Tanpa Foto Bulat',
                    'stacked_image' => 'Foto Penuh di Atas',
                ],
                'schema' => [
                    ...TranslatableField::text('data.heading', 'Judul', required: true, placeholder: 'Program Kami'),
                    ...TranslatableField::text('data.subheading', 'Sub Judul (opsional)', placeholder: 'Kalimat pendukung'),
                    Forms\Components\Repeater::make('data.items')->live()
                        ->label('Daftar Program')
                        ->schema([
                            ...TranslatableField::text('title', 'Judul', required: true, placeholder: 'Kelompok Bermain (Kober)'),
                            ...TranslatableField::textarea('description', 'Deskripsi (opsional)', placeholder: 'Penjelasan singkat program ini'),
                            Forms\Components\FileUpload::make('photo')
                                ->label('Foto (opsional — tampil bulat di atas kartu)')
                                ->image()
                                ->avatar()
                                ->directory('blocks/program')
                                ->live()
                                ->columnSpanFull(),
                            Forms\Components\Select::make('icon')
                                ->label('Ikon (dipakai jika foto kosong)')
                                ->options(self::iconOptions())
                                ->default('graduation')
                                ->native(false)
                                ->columnSpanFull(),
                            Forms\Components\Select::make('color')
                                ->label('Warna Kartu')
                                ->options([
                                    'teal' => 'Teal',
                                    'purple' => 'Ungu',
                                    'pink' => 'Merah Muda',
                                    'amber' => 'Kuning',
                                    'blue' => 'Biru',
                                ])
                                ->default('teal')
                                ->native(false),
                            Forms\Components\TextInput::make('link')
                                ->label('Link Tombol "Info Lebih Lanjut" (opsional)')
                                ->url()
                                ->placeholder('/akademik-kober'),
                            Forms\Components\TextInput::make('whatsapp')
                                ->label('Nomor WhatsApp (opsional, tampilkan tombol WhatsApp)')
                                ->tel()
                                ->placeholder('62813xxxxxxx'),
                        ])
                        ->columns(2)
                        ->reorderable()
                        ->collapsible()
                        ->itemLabel(fn (array $state): ?string => $state['title'] ?? null)
                        ->columnSpanFull(),
                ],
            ],
            'news_list' => [
                'label' => 'Daftar Berita',
                'description' => 'Kartu berita/event ringkas — judul, tanggal, cuplikan, dan foto.',
                'variants' => [
                    'grid' => 'Grid Kartu (Standar)',
                    'list' => 'List Horizontal',
                    'featured' => 'Satu Berita Utama + Grid Kecil',
                    'magazine' => 'Gaya Majalah Asimetris',
                    'minimal_list' => 'List Teks Tanpa Foto',
                ],
                'schema' => [
                    ...TranslatableField::text('data.heading', 'Judul', required: true, placeholder: 'Berita & Event Sekolah'),
                    ...TranslatableField::text('data.subheading', 'Sub Judul (opsional)', placeholder: 'Kalimat pendukung'),
                    Forms\Components\Repeater::make('data.items')->live()
                        ->label('Daftar Berita')
                        ->schema([
                            ...TranslatableField::text('title', 'Judul', required: true, placeholder: 'Pembukaan Tahun Ajaran Baru'),
                            Forms\Components\TextInput::make('date')
                                ->label('Tanggal')
                                ->placeholder('25 September 2025'),
                            ...TranslatableField::textarea('excerpt', 'Cuplikan', placeholder: 'Ringkasan singkat berita'),
                            Forms\Components\FileUpload::make('image')
                                ->label('Foto (opsional)')
                                ->image()
                                ->panelLayout('integrated')
                                ->imagePreviewHeight('150')
                                ->directory('blocks/news')
                                ->live()
                                ->columnSpanFull(),
                            ...TranslatableField::textarea(
                                'content',
                                'Isi Lengkap (opsional, tampil di jendela pop-up saat kartu diklik)',
                                rows: 6,
                                placeholder: 'Isi lengkap berita ini...',
                            ),
                            Forms\Components\TextInput::make('link')
                                ->label('Link (opsional, dipakai hanya jika Isi Lengkap dikosongkan)')
                                ->url()
                                ->placeholder('/berita'),
                        ])
                        ->columns(2)
                        ->reorderable()
                        ->collapsible()
                        ->itemLabel(fn (array $state): ?string => $state['title'] ?? null)
                        ->columnSpanFull(),
                ],
            ],
            'accordion_tabs' => [
                'label' => 'Konten Tab / Akordeon',
                'description' => 'Beberapa bagian konten yang bisa dibuka satu per satu, atau lewat tab — cocok untuk kebijakan, prosedur, atau info berlapis.',
                'variants' => [
                    'accordion' => 'Akordeon (Standar)',
                    'tabs' => 'Tab Mendatar',
                    'boxed_accordion' => 'Akordeon Dalam Kartu',
                    'numbered_list' => 'List Bernomor Terbuka Semua',
                    'two_column' => 'Akordeon Dua Kolom',
                ],
                'schema' => [
                    ...TranslatableField::text('data.heading', 'Judul (opsional)', placeholder: 'Kebijakan & Prosedur'),
                    Forms\Components\Repeater::make('data.items')->live()
                        ->label('Daftar Bagian')
                        ->schema([
                            ...TranslatableField::text('title', 'Judul Bagian', required: true, placeholder: 'Kebijakan Seragam'),
                            ...TranslatableField::textarea('body', 'Isi', rows: 3, required: true, placeholder: 'Penjelasan bagian ini'),
                        ])
                        ->columns(2)
                        ->reorderable()
                        ->collapsible()
                        ->itemLabel(fn (array $state): ?string => $state['title'] ?? null)
                        ->columnSpanFull(),
                ],
            ],
            'pricing_table' => [
                'label' => 'Tabel Harga / Biaya',
                'description' => 'Kartu-kartu biaya atau paket, masing-masing dengan harga, daftar fitur, dan tombol.',
                'variants' => [
                    'cards' => 'Kartu Sejajar (Standar)',
                    'table' => 'Tabel Perbandingan',
                    'minimal' => 'Kartu Minimalis',
                    'horizontal' => 'Kartu List Horizontal',
                    'featured_center' => 'Kartu Tengah Lebih Besar',
                ],
                'schema' => [
                    ...TranslatableField::text('data.heading', 'Judul (opsional)', placeholder: 'Biaya Pendaftaran'),
                    ...TranslatableField::text('data.subheading', 'Sub Judul (opsional)', placeholder: 'Kalimat pendukung'),
                    Forms\Components\Repeater::make('data.items')->live()
                        ->label('Daftar Paket')
                        ->schema([
                            ...TranslatableField::text('title', 'Nama Paket', required: true, placeholder: 'Kelompok Bermain'),
                            Forms\Components\TextInput::make('price')
                                ->label('Harga')
                                ->required()
                                ->placeholder('Rp 2.500.000'),
                            Forms\Components\TextInput::make('period')
                                ->label('Keterangan Harga (opsional)')
                                ->placeholder('/ tahun ajaran'),
                            Forms\Components\Toggle::make('highlighted')
                                ->label('Tonjolkan Paket Ini')
                                ->default(false)
                                ->live(),
                            Forms\Components\Repeater::make('features')->live()
                                ->label('Daftar Fitur / Termasuk')
                                ->schema([
                                    ...TranslatableField::text('label', 'Teks', required: true, placeholder: 'Gratis seragam'),
                                ])
                                ->reorderable()
                                ->columnSpanFull(),
                            ...TranslatableField::text('cta_text', 'Teks Tombol (opsional)', placeholder: 'Daftar Sekarang'),
                            Forms\Components\TextInput::make('cta_link')
                                ->label('Link Tombol (opsional)')
                                ->url()
                                ->placeholder('/penerimaan')
                                ->columnSpanFull(),
                        ])
                        ->columns(2)
                        ->reorderable()
                        ->collapsible()
                        ->itemLabel(fn (array $state): ?string => $state['title'] ?? null)
                        ->columnSpanFull(),
                ],
            ],
            'countdown' => [
                'label' => 'Hitung Mundur',
                'description' => 'Hitung mundur menuju tanggal penting, misalnya batas akhir pendaftaran.',
                'variants' => [
                    'standard' => 'Standar',
                    'boxed' => 'Kotak Terpisah',
                    'banner' => 'Banner Warna Selebar Halaman',
                    'minimal' => 'Minimalis (Angka Saja)',
                    'dark' => 'Latar Gelap',
                ],
                'schema' => [
                    ...TranslatableField::text('data.heading', 'Judul', required: true, placeholder: 'Pendaftaran Ditutup Dalam'),
                    ...TranslatableField::textarea('data.subheading', 'Sub Judul (opsional)', placeholder: 'Segera daftarkan putra-putri Anda'),
                    Forms\Components\DateTimePicker::make('data.target_date')
                        ->label('Tanggal & Waktu Target')
                        ->required()
                        ->native(false)
                        ->live(),
                    ...TranslatableField::text('data.cta_text', 'Teks Tombol (opsional)', placeholder: 'Daftar Sekarang'),
                    Forms\Components\TextInput::make('data.cta_link')
                        ->label('Link Tombol (opsional)')
                        ->url()
                        ->placeholder('/penerimaan')
                        ->columnSpanFull(),
                ],
            ],
            'logo_cloud' => [
                'label' => 'Logo Partner / Akreditasi',
                'description' => 'Barisan logo mitra, sponsor, atau lembaga akreditasi.',
                'variants' => [
                    'grid' => 'Grid (Standar)',
                    'carousel' => 'Carousel Geser',
                    'inline_row' => 'Satu Baris Sejajar',
                    'bordered_grid' => 'Grid Bertepi',
                    'grayscale' => 'Hitam-Putih (Berwarna saat Disorot)',
                ],
                'schema' => [
                    ...TranslatableField::text('data.heading', 'Judul (opsional)', placeholder: 'Dipercaya & Diakui Oleh'),
                    Forms\Components\Repeater::make('data.logos')->live()
                        ->label('Daftar Logo')
                        ->schema([
                            Forms\Components\FileUpload::make('image')
                                ->label('Logo')
                                ->image()
                                ->panelLayout('integrated')
                                ->imagePreviewHeight('100')
                                ->directory('blocks/logos')
                                ->live()
                                ->columnSpanFull(),
                            Forms\Components\TextInput::make('label')
                                ->label('Nama (opsional)')
                                ->placeholder('Kemendikbud'),
                            Forms\Components\TextInput::make('link')
                                ->label('Link (opsional, klik logo membuka halaman ini)')
                                ->url()
                                ->prefixIcon('heroicon-o-link')
                                ->placeholder('https://mitra.co.id'),
                        ])
                        ->columns(2)
                        ->reorderable()
                        ->collapsible()
                        ->itemLabel(fn (array $state): ?string => $state['label'] ?? null)
                        ->columnSpanFull(),
                ],
            ],
            'quote' => [
                'label' => 'Kutipan Tunggal',
                'description' => 'Satu kutipan besar yang disorot, misalnya sambutan kepala sekolah.',
                'variants' => [
                    'centered' => 'Tengah (Standar)',
                    'side_photo' => 'Foto di Samping',
                    'boxed' => 'Kotak Bertepi',
                    'minimal' => 'Minimalis Tanpa Tanda Kutip',
                    'large_type' => 'Teks Sangat Besar',
                ],
                'schema' => [
                    ...TranslatableField::textarea('data.quote', 'Kutipan', rows: 4, required: true, placeholder: 'Tulis kutipannya di sini'),
                    Forms\Components\TextInput::make('data.name')
                        ->label('Nama')
                        ->placeholder('H. Abdullah, S.Pd.'),
                    ...TranslatableField::text('data.role', 'Jabatan (opsional)', placeholder: 'Kepala Sekolah'),
                    Forms\Components\FileUpload::make('data.photo')
                        ->label('Foto (opsional)')
                        ->image()
                        ->avatar()
                        ->directory('blocks/quote')
                        ->live()
                        ->columnSpanFull(),
                ],
            ],
            'counter' => [
                'label' => 'Angka Berjalan (Counter)',
                'description' => 'Angka pencapaian dengan animasi berjalan dan ikon — mirip Statistik tapi lebih visual.',
                'variants' => [
                    'inline' => 'Sejajar (Standar)',
                    'cards' => 'Kartu Terpisah',
                    'circular' => 'Lingkaran Progres',
                    'icons_top' => 'Ikon Besar di Atas',
                    'gradient_band' => 'Pita Gradasi Selebar Halaman',
                ],
                'schema' => [
                    ...TranslatableField::text('data.heading', 'Judul (opsional)', placeholder: 'Pencapaian Kami'),
                    Forms\Components\Repeater::make('data.items')->live()
                        ->label('Daftar Angka')
                        ->schema([
                            Forms\Components\Select::make('icon')
                                ->label('Ikon')
                                ->options(self::iconOptions())
                                ->native(false)
                                ->columnSpanFull(),
                            ...TranslatableField::text('label', 'Label', required: true, placeholder: 'Jumlah Siswa'),
                            Forms\Components\TextInput::make('value')
                                ->label('Nilai Angka')
                                ->numeric()
                                ->required()
                                ->placeholder('500'),
                            Forms\Components\TextInput::make('suffix')
                                ->label('Akhiran (opsional)')
                                ->placeholder('+'),
                        ])
                        ->columns(2)
                        ->reorderable()
                        ->collapsible()
                        ->itemLabel(fn (array $state): ?string => $state['label'] ?? null)
                        ->columnSpanFull(),
                ],
            ],
            'downloads' => [
                'label' => 'Unduhan Dokumen',
                'description' => 'Daftar file yang bisa diunduh pengunjung, misalnya formulir pendaftaran atau brosur.',
                'variants' => [
                    'list' => 'List (Standar)',
                    'grid' => 'Grid Kartu',
                    'cards' => 'Kartu Besar',
                    'table' => 'Tabel',
                    'minimal' => 'Minimalis (Teks Saja)',
                ],
                'schema' => [
                    ...TranslatableField::text('data.heading', 'Judul (opsional)', placeholder: 'Formulir & Dokumen'),
                    Forms\Components\Repeater::make('data.items')->live()
                        ->label('Daftar File')
                        ->schema([
                            ...TranslatableField::text('title', 'Nama File', required: true, placeholder: 'Formulir Pendaftaran PPDB'),
                            Forms\Components\Select::make('media_pick')
                                ->label('Pilih dari Pustaka Media')
                                ->helperText('File PDF/Word/Excel yang sudah pernah diunggah — pilih di sini agar tidak perlu unggah ulang. Kosongkan dan pakai "Atau Unggah File Baru" di bawah untuk file yang belum pernah ada.')
                                ->options(fn (): array => self::documentLibraryOptions())
                                ->searchable()
                                ->native(false)
                                ->live()
                                ->dehydrated(false)
                                ->afterStateUpdated(function (?string $state, callable $set): void {
                                    if ($state) {
                                        $set('file', $state);
                                        $set('size_label', self::documentSizeLabel($state));
                                    }
                                })
                                ->columnSpanFull(),
                            Forms\Components\FileUpload::make('file')
                                ->label('Atau Unggah File Baru')
                                ->helperText('Mengunggah di sini akan menggantikan pilihan dari Pustaka Media di atas.')
                                ->acceptedFileTypes([
                                    'application/pdf',
                                    'application/msword',
                                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                                    'application/vnd.ms-excel',
                                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                ])
                                ->directory('blocks/downloads')
                                ->live()
                                ->columnSpanFull(),
                            Forms\Components\TextInput::make('size_label')
                                ->label('Keterangan Ukuran (opsional)')
                                ->placeholder('PDF, 1.2 MB'),
                            Forms\Components\Select::make('icon')
                                ->label('Ikon (opsional)')
                                ->options(self::iconOptions())
                                ->native(false),
                        ])
                        ->columns(2)
                        ->reorderable()
                        ->collapsible()
                        ->itemLabel(fn (array $state): ?string => $state['title'] ?? null)
                        ->columnSpanFull(),
                ],
            ],
            'map' => [
                'label' => 'Peta Lokasi',
                'description' => 'Peta lokasi berdiri sendiri, tanpa info kontak lain di sampingnya.',
                'variants' => [
                    'standard' => 'Standar',
                    'side_info' => 'Peta + Info di Samping',
                    'fullwidth' => 'Selebar Halaman',
                    'boxed' => 'Kotak Bertepi',
                    'minimal' => 'Minimalis Tanpa Judul',
                ],
                'schema' => [
                    ...TranslatableField::text('data.heading', 'Judul (opsional)', placeholder: 'Lokasi Kami'),
                    ...TranslatableField::textarea('data.address', 'Alamat (opsional, dipakai varian Peta + Info)', placeholder: 'Jl. Contoh No. 1, Kota, Provinsi'),
                    Forms\Components\Textarea::make('data.map_embed')
                        ->label('URL Embed Google Maps')
                        ->required()
                        ->rows(2)
                        ->placeholder('https://maps.google.com/...')
                        ->columnSpanFull(),
                ],
            ],
            'scroll_to_top' => [
                'label' => 'Tombol Scroll to Top',
                'description' => 'Tombol mengambang yang muncul setelah pengunjung scroll ke bawah, untuk kembali ke atas halaman. Cukup satu blok ini per halaman.',
                'variants' => [
                    'circle' => 'Lingkaran Solid',
                    'outline' => 'Lingkaran Garis Tepi',
                    'square' => 'Kotak Membulat',
                    'pill' => 'Pil dengan Label',
                    'minimal' => 'Panah Minimalis',
                ],
                'schema' => [
                    Forms\Components\Select::make('data.color')
                        ->label('Warna')
                        ->options([
                            'indigo' => 'Indigo',
                            'teal' => 'Teal',
                            'dark' => 'Gelap',
                        ])
                        ->default('indigo')
                        ->native(false),
                    Forms\Components\Select::make('data.position')
                        ->label('Posisi')
                        ->options([
                            'right' => 'Kanan Bawah',
                            'left' => 'Kiri Bawah',
                        ])
                        ->default('right')
                        ->native(false),
                    ...TranslatableField::text(
                        'data.label',
                        'Label Tombol (dipakai varian "Pil dengan Label")',
                        placeholder: 'Ke Atas',
                    ),
                ],
            ],
            'ppdb_form' => [
                'label' => 'Formulir Pendaftaran (PPDB)',
                'description' => 'Formulir pendaftaran calon siswa baru — nama anak, orang tua, telepon, email, unit, pesan. Terkirim langsung ke menu "Pendaftaran PPDB".',
                'variants' => [
                    'standard' => 'Standar (Standar)',
                    'boxed' => 'Kotak Terpisah',
                    'split' => 'Teks Kiri, Formulir Kanan',
                ],
                'schema' => [
                    ...TranslatableField::text('data.heading', 'Judul', required: true, placeholder: 'Formulir Pendaftaran Siswa Baru'),
                    ...TranslatableField::textarea('data.subheading', 'Sub Judul (opsional)', placeholder: 'Isi data di bawah ini, tim kami akan segera menghubungi Anda'),
                ],
            ],
        ];
    }

    /**
     * PDF/Word/Excel files already sitting in the public disk (uploaded from anywhere
     * — this block, another block, or Pustaka Media itself), keyed by their storage
     * path so picking one just reuses that path instead of re-uploading the same file.
     *
     * @return array<string, string>
     */
    public static function documentLibraryOptions(): array
    {
        $extensions = ['pdf', 'doc', 'docx', 'xls', 'xlsx'];

        return collect(Storage::disk('public')->allFiles())
            ->filter(fn (string $path): bool => in_array(
                strtolower(pathinfo($path, PATHINFO_EXTENSION)),
                $extensions,
                true,
            ))
            ->sortByDesc(fn (string $path): int => Storage::disk('public')->lastModified($path))
            ->mapWithKeys(fn (string $path): array => [$path => basename($path)])
            ->all();
    }

    protected static function documentSizeLabel(string $path): string
    {
        $bytes = Storage::disk('public')->size($path);
        $extension = strtoupper(pathinfo($path, PATHINFO_EXTENSION));

        $size = $bytes < 1024 * 1024
            ? round($bytes / 1024, 1).' KB'
            : round($bytes / (1024 * 1024), 1).' MB';

        return "{$extension}, {$size}";
    }

    public static function iconOptions(): array
    {
        return [
            'book' => 'Buku',
            'music' => 'Musik',
            'palette' => 'Seni / Palet',
            'heart' => 'Hati',
            'shield' => 'Keamanan',
            'users' => 'Orang / Komunitas',
            'sparkles' => 'Kilau / Kreativitas',
            'graduation' => 'Topi Wisuda',
            'smile' => 'Senyum',
            'globe' => 'Bahasa / Dunia',
            'award' => 'Penghargaan',
            'star' => 'Bintang',
        ];
    }
}
