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
        return self::all()[$type]['schema'] ?? [];
    }

    public static function all(): array
    {
        return [
            'hero' => [
                'label' => 'Hero (Banner Utama)',
                'description' => 'Banner besar di paling atas halaman — judul utama, sub-judul, dan satu tombol.',
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
                'schema' => [
                    ...TranslatableField::text('data.heading', 'Judul (opsional)', placeholder: 'Tentang Kami'),
                    ...TranslatableField::richEditor('data.body', 'Isi', required: true),
                ],
            ],
            'image_gallery' => [
                'label' => 'Galeri Gambar',
                'description' => 'Menampilkan kumpulan foto berjejer, misalnya dokumentasi kegiatan.',
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
                'description' => 'Daftar keunggulan atau program, masing-masing dengan judul singkat dan deskripsi.',
                'schema' => [
                    ...TranslatableField::text('data.heading', 'Judul (opsional)', placeholder: 'Program Unggulan'),
                    Forms\Components\Repeater::make('data.items')
                        ->label('Daftar Item')
                        ->schema([
                            ...TranslatableField::text('title', 'Judul Item', required: true, placeholder: 'Tahfidz Al-Quran'),
                            ...TranslatableField::textarea('description', 'Deskripsi', placeholder: 'Penjelasan singkat tentang item ini'),
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
}
