<?php

namespace App\Filament\Support;

use App\Services\TranslationService;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Illuminate\Support\Str;

/**
 * Builds a Bahasa Indonesia field paired with its English sibling
 * (`{name}_en`). The Indonesian field auto-fills the English one via
 * MyMemory translation when the editor leaves the field — but only if
 * the English field is still empty, so a manual edit is never silently
 * overwritten. Every English field also gets a "translate now" button
 * to (re)generate it on demand.
 */
class TranslatableField
{
    /**
     * @return array{0: TextInput, 1: TextInput}
     */
    public static function text(string $name, string $label, bool $required = false): array
    {
        $enName = "{$name}_en";

        $id = TextInput::make($name)
            ->label($label)
            ->required($required)
            ->maxLength(255)
            ->live(onBlur: true)
            ->afterStateUpdated(fn (?string $state, Set $set, Get $get) => self::autoFill($state, $enName, $set, $get));

        $en = TextInput::make($enName)
            ->label("{$label} (Inggris)")
            ->maxLength(255)
            ->hintAction(self::translateAction($name, $enName));

        return [$id, $en];
    }

    /**
     * @return array{0: Textarea, 1: Textarea}
     */
    public static function textarea(string $name, string $label, int $rows = 2, bool $required = false): array
    {
        $enName = "{$name}_en";

        $id = Textarea::make($name)
            ->label($label)
            ->required($required)
            ->rows($rows)
            ->live(onBlur: true)
            ->afterStateUpdated(fn (?string $state, Set $set, Get $get) => self::autoFill($state, $enName, $set, $get));

        $en = Textarea::make($enName)
            ->label("{$label} (Inggris)")
            ->rows($rows)
            ->hintAction(self::translateAction($name, $enName));

        return [$id, $en];
    }

    /**
     * Rich text isn't auto-translated (machine translation would mangle
     * the HTML formatting) — editors write the English version by hand.
     *
     * @return array{0: RichEditor, 1: RichEditor}
     */
    public static function richEditor(string $name, string $label, bool $required = false): array
    {
        $id = RichEditor::make($name)
            ->label($label)
            ->required($required)
            ->columnSpanFull();

        $en = RichEditor::make("{$name}_en")
            ->label("{$label} (Inggris)")
            ->helperText('Isi manual — teks berformat tidak diterjemahkan otomatis.')
            ->columnSpanFull();

        return [$id, $en];
    }

    public static function autoFill(?string $state, string $enName, Set $set, Get $get): void
    {
        if (blank($state) || filled($get($enName))) {
            return;
        }

        $translated = TranslationService::idToEn($state);

        if ($translated !== null) {
            $set($enName, $translated);
        }
    }

    public static function translateAction(string $idName, string $enName): Action
    {
        return Action::make('translate_' . Str::afterLast($enName, '.'))
            ->label('Terjemahkan')
            ->icon('heroicon-o-language')
            ->tooltip('Terjemahkan otomatis dari Bahasa Indonesia')
            ->action(function (Set $set, Get $get) use ($idName, $enName) {
                $source = $get($idName);

                if (blank($source)) {
                    return;
                }

                $translated = TranslationService::idToEn($source);

                if ($translated !== null) {
                    $set($enName, $translated);
                }
            });
    }
}
