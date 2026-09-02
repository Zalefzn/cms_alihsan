<?php

namespace App\Support;

use Filament\Forms;

/**
 * Central definition of every block "type" a page can be built from:
 * its label (shown in the type picker) and the form schema used to
 * edit its content. Each field is stored under the block's `data`
 * JSON column, addressed by dot-notation (e.g. "data.heading").
 */
class BlockDefinitions
{
    public static function options(): array
    {
        return collect(self::all())->mapWithKeys(
            fn (array $definition, string $type) => [$type => $definition['label']]
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
                'schema' => [
                    Forms\Components\TextInput::make('data.heading')
                        ->label('Judul')
                        ->required(),
                    Forms\Components\Textarea::make('data.subheading')
                        ->label('Sub Judul')
                        ->rows(2),
                    Forms\Components\TextInput::make('data.cta_text')
                        ->label('Teks Tombol'),
                    Forms\Components\TextInput::make('data.cta_link')
                        ->label('Link Tombol')
                        ->url(),
                    Forms\Components\FileUpload::make('data.image')
                        ->label('Gambar')
                        ->image()
                        ->directory('blocks')
                        ->columnSpanFull(),
                ],
            ],
            'rich_text' => [
                'label' => 'Teks / Paragraf',
                'schema' => [
                    Forms\Components\TextInput::make('data.heading')
                        ->label('Judul (opsional)'),
                    Forms\Components\RichEditor::make('data.body')
                        ->label('Isi')
                        ->required()
                        ->columnSpanFull(),
                ],
            ],
            'image_gallery' => [
                'label' => 'Galeri Gambar',
                'schema' => [
                    Forms\Components\TextInput::make('data.caption')
                        ->label('Keterangan (opsional)'),
                    Forms\Components\FileUpload::make('data.images')
                        ->label('Gambar-gambar')
                        ->image()
                        ->multiple()
                        ->reorderable()
                        ->directory('blocks')
                        ->columnSpanFull(),
                ],
            ],
            'video' => [
                'label' => 'Video',
                'schema' => [
                    Forms\Components\TextInput::make('data.caption')
                        ->label('Keterangan (opsional)'),
                    Forms\Components\TextInput::make('data.embed_url')
                        ->label('Link YouTube / Vimeo')
                        ->helperText('Isi ini ATAU unggah file video di bawah, tidak perlu keduanya.')
                        ->url(),
                    Forms\Components\FileUpload::make('data.video')
                        ->label('Unggah File Video')
                        ->acceptedFileTypes(['video/mp4', 'video/webm', 'video/ogg'])
                        ->directory('blocks')
                        ->columnSpanFull(),
                ],
            ],
            'cta' => [
                'label' => 'CTA (Ajakan Bertindak)',
                'schema' => [
                    Forms\Components\TextInput::make('data.heading')
                        ->label('Judul')
                        ->required(),
                    Forms\Components\Textarea::make('data.body')
                        ->label('Isi')
                        ->rows(2),
                    Forms\Components\TextInput::make('data.button_text')
                        ->label('Teks Tombol'),
                    Forms\Components\TextInput::make('data.button_link')
                        ->label('Link Tombol')
                        ->url(),
                ],
            ],
            'faq' => [
                'label' => 'FAQ (Tanya Jawab)',
                'schema' => [
                    Forms\Components\TextInput::make('data.heading')
                        ->label('Judul (opsional)'),
                    Forms\Components\Repeater::make('data.items')
                        ->label('Daftar Pertanyaan')
                        ->schema([
                            Forms\Components\TextInput::make('question')
                                ->label('Pertanyaan')
                                ->required(),
                            Forms\Components\Textarea::make('answer')
                                ->label('Jawaban')
                                ->rows(2)
                                ->required(),
                        ])
                        ->reorderable()
                        ->collapsible()
                        ->itemLabel(fn (array $state): ?string => $state['question'] ?? null)
                        ->columnSpanFull(),
                ],
            ],
            'team' => [
                'label' => 'Tim / Guru',
                'schema' => [
                    Forms\Components\TextInput::make('data.heading')
                        ->label('Judul (opsional)'),
                    Forms\Components\Repeater::make('data.items')
                        ->label('Daftar Anggota')
                        ->schema([
                            Forms\Components\FileUpload::make('photo')
                                ->label('Foto')
                                ->image()
                                ->directory('blocks/team'),
                            Forms\Components\TextInput::make('name')
                                ->label('Nama')
                                ->required(),
                            Forms\Components\TextInput::make('role')
                                ->label('Jabatan'),
                        ])
                        ->reorderable()
                        ->collapsible()
                        ->itemLabel(fn (array $state): ?string => $state['name'] ?? null)
                        ->columnSpanFull(),
                ],
            ],
            'testimonials' => [
                'label' => 'Testimoni',
                'schema' => [
                    Forms\Components\TextInput::make('data.heading')
                        ->label('Judul (opsional)'),
                    Forms\Components\Repeater::make('data.items')
                        ->label('Daftar Testimoni')
                        ->schema([
                            Forms\Components\FileUpload::make('photo')
                                ->label('Foto')
                                ->image()
                                ->directory('blocks/testimonials'),
                            Forms\Components\TextInput::make('name')
                                ->label('Nama')
                                ->required(),
                            Forms\Components\TextInput::make('role')
                                ->label('Peran / Angkatan'),
                            Forms\Components\Textarea::make('quote')
                                ->label('Kutipan')
                                ->rows(2)
                                ->required(),
                        ])
                        ->reorderable()
                        ->collapsible()
                        ->itemLabel(fn (array $state): ?string => $state['name'] ?? null)
                        ->columnSpanFull(),
                ],
            ],
            'contact_info' => [
                'label' => 'Info Kontak',
                'schema' => [
                    Forms\Components\Textarea::make('data.address')
                        ->label('Alamat')
                        ->rows(2)
                        ->columnSpanFull(),
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
                'schema' => [
                    Forms\Components\Repeater::make('data.items')
                        ->label('Daftar Angka')
                        ->schema([
                            Forms\Components\TextInput::make('label')
                                ->label('Label')
                                ->required(),
                            Forms\Components\TextInput::make('value')
                                ->label('Nilai')
                                ->required(),
                        ])
                        ->reorderable()
                        ->columns(2)
                        ->columnSpanFull(),
                ],
            ],
            'feature_list' => [
                'label' => 'Daftar Fitur / Program',
                'schema' => [
                    Forms\Components\TextInput::make('data.heading')
                        ->label('Judul (opsional)'),
                    Forms\Components\Repeater::make('data.items')
                        ->label('Daftar Item')
                        ->schema([
                            Forms\Components\TextInput::make('title')
                                ->label('Judul Item')
                                ->required(),
                            Forms\Components\Textarea::make('description')
                                ->label('Deskripsi')
                                ->rows(2),
                        ])
                        ->reorderable()
                        ->collapsible()
                        ->itemLabel(fn (array $state): ?string => $state['title'] ?? null)
                        ->columnSpanFull(),
                ],
            ],
        ];
    }
}
