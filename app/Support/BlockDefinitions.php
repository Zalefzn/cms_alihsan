<?php

namespace App\Support;

use App\Filament\Support\TranslatableField;
use Filament\Forms;

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
                        ->columnSpanFull(),
                ],
            ],
            'rich_text' => [
                'label' => 'Teks / Paragraf',
                'description' => 'Teks bebas untuk paragraf atau penjelasan panjang, bisa diberi format (bold, list, dll).',
                'variants' => [
                    'standard' => 'Tengah (Standar)',
                    'left' => 'Rata Kiri',
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
                        ->columnSpanFull(),
                ],
            ],
            'video' => [
                'label' => 'Video',
                'description' => 'Menampilkan satu video — bisa link YouTube/Vimeo, atau unggah file sendiri.',
                'variants' => [
                    'standard' => 'Standar (dengan judul)',
                    'compact' => 'Ramping (tanpa judul)',
                ],
                'schema' => [
                    ...TranslatableField::text('data.caption', 'Keterangan (opsional)', placeholder: 'Profil sekolah dalam 2 menit'),
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
                        ->columnSpanFull(),
                ],
            ],
            'cta' => [
                'label' => 'CTA (Ajakan Bertindak)',
                'description' => 'Kotak ajakan singkat dengan satu tombol, misalnya mendorong pengunjung untuk mendaftar.',
                'variants' => [
                    'plain' => 'Polos (Standar)',
                    'banner' => 'Banner Warna',
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
                ],
                'schema' => [
                    ...TranslatableField::text('data.heading', 'Judul (opsional)', placeholder: 'Pertanyaan yang Sering Diajukan'),
                    Forms\Components\Repeater::make('data.items')
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
                ],
                'schema' => [
                    ...TranslatableField::text('data.heading', 'Judul (opsional)', placeholder: 'Guru & Staff Kami'),
                    Forms\Components\Repeater::make('data.items')
                        ->label('Daftar Anggota')
                        ->schema([
                            Forms\Components\FileUpload::make('photo')
                                ->label('Foto')
                                ->image()
                                ->avatar()
                                ->directory('blocks/team')
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
                ],
                'schema' => [
                    ...TranslatableField::text('data.heading', 'Judul (opsional)', placeholder: 'Apa Kata Mereka'),
                    Forms\Components\Repeater::make('data.items')
                        ->label('Daftar Testimoni')
                        ->schema([
                            Forms\Components\FileUpload::make('photo')
                                ->label('Foto')
                                ->image()
                                ->avatar()
                                ->directory('blocks/testimonials')
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
                ],
                'schema' => [
                    Forms\Components\Repeater::make('data.items')
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
                ],
                'schema' => [
                    ...TranslatableField::text('data.heading', 'Judul (opsional)', placeholder: 'Program Unggulan'),
                    ...TranslatableField::text('data.subheading', 'Sub Judul (opsional)', placeholder: 'Kalimat pendukung di bawah judul'),
                    Forms\Components\Repeater::make('data.items')
                        ->label('Daftar Item')
                        ->schema([
                            ...TranslatableField::text('title', 'Judul Item', required: true, placeholder: 'Tahfidz Al-Quran'),
                            ...TranslatableField::textarea('description', 'Deskripsi', placeholder: 'Penjelasan singkat tentang item ini'),
                            Forms\Components\Select::make('icon')
                                ->label('Ikon')
                                ->options(self::iconOptions())
                                ->native(false)
                                ->columnSpanFull(),
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
                        ->columnSpanFull(),
                    Forms\Components\Repeater::make('data.features')
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
                ],
                'schema' => [
                    ...TranslatableField::text('data.heading', 'Judul Kiri', required: true, placeholder: 'Tentang Kami'),
                    ...TranslatableField::textarea('data.body', 'Isi Kiri', rows: 5, required: true, placeholder: 'Penjelasan singkat tentang sekolah'),
                    ...TranslatableField::text('data.vision_heading', 'Judul Visi', placeholder: 'Visi Kami'),
                    ...TranslatableField::textarea('data.vision_text', 'Isi Visi', rows: 3, placeholder: 'Pernyataan visi sekolah'),
                    ...TranslatableField::text('data.mission_heading', 'Judul Misi', placeholder: 'Misi Kami'),
                    Forms\Components\Repeater::make('data.mission_items')
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
                ],
                'schema' => [
                    ...TranslatableField::text('data.heading', 'Judul', required: true, placeholder: 'Program Kami'),
                    ...TranslatableField::text('data.subheading', 'Sub Judul (opsional)', placeholder: 'Kalimat pendukung'),
                    Forms\Components\Repeater::make('data.items')
                        ->label('Daftar Program')
                        ->schema([
                            ...TranslatableField::text('title', 'Judul', required: true, placeholder: 'Kelompok Bermain (Kober)'),
                            ...TranslatableField::textarea('description', 'Deskripsi (opsional)', placeholder: 'Penjelasan singkat program ini'),
                            Forms\Components\FileUpload::make('photo')
                                ->label('Foto (opsional — tampil bulat di atas kartu)')
                                ->image()
                                ->avatar()
                                ->directory('blocks/program')
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
                ],
                'schema' => [
                    ...TranslatableField::text('data.heading', 'Judul', required: true, placeholder: 'Berita & Event Sekolah'),
                    ...TranslatableField::text('data.subheading', 'Sub Judul (opsional)', placeholder: 'Kalimat pendukung'),
                    Forms\Components\Repeater::make('data.items')
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
                                ->columnSpanFull(),
                            Forms\Components\TextInput::make('link')
                                ->label('Link (opsional)')
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
        ];
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
