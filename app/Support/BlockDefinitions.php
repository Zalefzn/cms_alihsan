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
                    ...TranslatableField::text('data.heading', 'Judul', required: true),
                    ...TranslatableField::textarea('data.subheading', 'Sub Judul'),
                    ...TranslatableField::text('data.cta_text', 'Teks Tombol'),
                    Forms\Components\TextInput::make('data.cta_link')
                        ->label('Link Tombol')
                        ->url()
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
                    ...TranslatableField::text('data.heading', 'Judul (opsional)'),
                    ...TranslatableField::richEditor('data.body', 'Isi', required: true),
                ],
            ],
            'image_gallery' => [
                'label' => 'Galeri Gambar',
                'description' => 'Menampilkan kumpulan foto berjejer, misalnya dokumentasi kegiatan.',
                'schema' => [
                    ...TranslatableField::text('data.caption', 'Keterangan (opsional)'),
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
                    ...TranslatableField::text('data.caption', 'Keterangan (opsional)'),
                    Forms\Components\TextInput::make('data.embed_url')
                        ->label('Link YouTube / Vimeo')
                        ->helperText('Isi ini ATAU unggah file video di bawah, tidak perlu keduanya.')
                        ->url()
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
                    ...TranslatableField::text('data.heading', 'Judul', required: true),
                    ...TranslatableField::textarea('data.body', 'Isi'),
                    ...TranslatableField::text('data.button_text', 'Teks Tombol'),
                    Forms\Components\TextInput::make('data.button_link')
                        ->label('Link Tombol')
                        ->url()
                        ->columnSpanFull(),
                ],
            ],
            'faq' => [
                'label' => 'FAQ (Tanya Jawab)',
                'description' => 'Daftar pertanyaan yang bisa dibuka-tutup, beserta jawabannya.',
                'schema' => [
                    ...TranslatableField::text('data.heading', 'Judul (opsional)'),
                    Forms\Components\Repeater::make('data.items')
                        ->label('Daftar Pertanyaan')
                        ->schema([
                            ...TranslatableField::text('question', 'Pertanyaan', required: true),
                            ...TranslatableField::textarea('answer', 'Jawaban', required: true),
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
                    ...TranslatableField::text('data.heading', 'Judul (opsional)'),
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
                                ->columnSpanFull(),
                            ...TranslatableField::text('role', 'Jabatan'),
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
                    ...TranslatableField::text('data.heading', 'Judul (opsional)'),
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
                                ->columnSpanFull(),
                            ...TranslatableField::text('role', 'Peran / Angkatan'),
                            ...TranslatableField::textarea('quote', 'Kutipan', required: true),
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
                    ...TranslatableField::textarea('data.address', 'Alamat'),
                    Forms\Components\TextInput::make('data.phone')
                        ->label('Telepon'),
                    Forms\Components\TextInput::make('data.email')
                        ->label('Email')
                        ->email(),
                    Forms\Components\Textarea::make('data.map_embed')
                        ->label('URL Embed Google Maps')
                        ->rows(2)
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
                            ...TranslatableField::text('label', 'Label', required: true),
                            Forms\Components\TextInput::make('value')
                                ->label('Nilai')
                                ->required()
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
                    ...TranslatableField::text('data.heading', 'Judul (opsional)'),
                    Forms\Components\Repeater::make('data.items')
                        ->label('Daftar Item')
                        ->schema([
                            ...TranslatableField::text('title', 'Judul Item', required: true),
                            ...TranslatableField::textarea('description', 'Deskripsi'),
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
