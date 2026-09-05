<?php

namespace App\Filament\Resources\PageResource\Pages;

use App\Filament\Resources\PageResource;
use App\Models\Block;
use App\Support\BlockDefinitions;
use App\Support\MediaResolver;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * WordPress-style page builder: a sidebar lists every block on the page in its real
 * order (add / reorder / show-hide / delete + an inline edit form for whichever block
 * is selected), while the canvas is an iframe embedding the actual React frontend's
 * `/preview` route, kept in sync live via postMessage — so the canvas is pixel-identical
 * to the real site instead of a re-implemented mockup. The existing table+drawer UI
 * (BlocksRelationManager) is left untouched as an alternative way to edit blocks.
 */
class BuildPage extends Page implements HasForms
{
    use InteractsWithForms;
    use InteractsWithRecord;

    protected static string $resource = PageResource::class;

    protected static string $view = 'filament.resources.page-resource.pages.build-page';

    protected ?string $maxContentWidth = 'full';

    /**
     * Draft, in-memory list of every block on the page (including hidden ones), in
     * display order. Each entry: {key, id, order, type, is_visible, data}. `key` is a
     * stable string identity for Livewire/Alpine tracking — the DB id once saved, a
     * uuid while still unsaved. Not persisted until saveAll() runs.
     *
     * @var array<int, array{key: string, id: int|null, order: int, type: string, is_visible: bool, data: array}>
     */
    public array $blocks = [];

    public ?string $selectedKey = null;

    public bool $isPickingType = false;

    /**
     * Mirrors the currently-selected block's editable fields, bound to editorForm().
     * Synced back into $blocks whenever the selection changes or the page is saved.
     *
     * @var array{type?: string, is_visible?: bool, data?: array}
     */
    public array $editingBlock = [];

    /**
     * Undo/redo history: snapshots of $blocks taken before each structural change
     * (add/remove/duplicate/reorder/toggle) or committed content edit. In-memory only —
     * cleared on save(), since a save reassigns ids/keys for new blocks that old
     * snapshots wouldn't match.
     *
     * @var array<int, array>
     */
    public array $undoStack = [];

    public array $redoStack = [];

    protected int $maxHistorySize = 25;

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);

        $this->authorizeAccess();

        $this->loadBlocksFromRecord();
    }

    protected function authorizeAccess(): void
    {
        abort_unless(static::getResource()::canEdit($this->getRecord()), 403);
    }

    /**
     * A real YouTube thumbnail for the video/video_feature block's row in the sidebar
     * list (matching the "gallery" look the admin asked for) — see MediaResolver for
     * the actual extraction logic, shared with the block-type preview's real-content
     * card so a YouTube-backed video block shows a real frame there too.
     */
    public function videoThumbnailUrl(?string $url): ?string
    {
        return MediaResolver::youtubeThumbnail($url);
    }

    public function getTitle(): string
    {
        return 'Desain Halaman: '.$this->getRecord()->title;
    }

    protected function loadBlocksFromRecord(): void
    {
        $this->blocks = $this->record->blocks()
            ->orderBy('order')
            ->get()
            ->map(fn (Block $block): array => [
                'key' => (string) $block->id,
                'id' => $block->id,
                'order' => $block->order,
                'type' => $block->type,
                'is_visible' => $block->is_visible,
                'data' => $block->data ?? [],
            ])
            ->values()
            ->all();
    }

    protected function getForms(): array
    {
        return ['editorForm'];
    }

    public function editorForm(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Hidden::make('type'),
                Forms\Components\Placeholder::make('type_label')
                    ->label('Tipe Blok')
                    ->content(fn (Get $get): string => BlockDefinitions::options()[$get('type')] ?? '—'),
                Forms\Components\Radio::make('data.variant')
                    ->label('Varian Tampilan')
                    ->helperText('Gaya layout section ini di halaman web — isi kontennya tetap sama. Bandingkan pratinjaunya di bawah.')
                    ->options(fn (Get $get): array => BlockDefinitions::variantOptions($get('type') ?? ''))
                    ->live()
                    ->visible(fn (Get $get): bool => BlockDefinitions::variantOptions($get('type') ?? '') !== [])
                    ->columnSpanFull(),
                Forms\Components\ViewField::make('block_preview')
                    ->label('Pratinjau Tampilan')
                    ->view('filament.forms.components.block-type-preview')
                    ->viewData(fn (Get $get): array => [
                        'type' => $get('type'),
                        'variant' => $get('data.variant'),
                        'variants' => BlockDefinitions::variantOptions($get('type') ?? ''),
                        'liveData' => $get('data') ?? [],
                    ])
                    ->dehydrated(false)
                    ->columnSpanFull(),
                Forms\Components\Toggle::make('is_visible')
                    ->label('Tampilkan blok ini')
                    ->live()
                    ->columnSpanFull(),
                // Single column throughout — BlockDefinitions' shared schemas (also used by
                // the wider table+drawer modal) default several groups to 2 columns, which
                // is cramped in this sidebar's fixed ~420px width. Force every level down to
                // 1 column here specifically, without touching the shared definitions.
                Forms\Components\Group::make(
                    fn (Get $get): array => $this->forceSingleColumnSchema(BlockDefinitions::schemaFor($get('type') ?? ''))
                )
                    ->columns(1)
                    ->columnSpanFull(),
            ])
            ->columns(1)
            ->statePath('editingBlock');
    }

    /**
     * Recursively overrides every component's column count to 1 so a block's edit form
     * always stacks vertically in the builder's narrow sidebar, regardless of how many
     * columns the shared BlockDefinitions schema declares (those declarations are tuned
     * for the wider table+drawer modal instead).
     *
     * @param  array<int, \Filament\Forms\Components\Component>  $schema
     * @return array<int, \Filament\Forms\Components\Component>
     */
    protected function forceSingleColumnSchema(array $schema): array
    {
        foreach ($schema as $component) {
            $component->columns(1);

            if ($component instanceof Forms\Components\Repeater) {
                $this->forceSingleColumnSchema($component->getChildComponents());
            }
        }

        return $schema;
    }

    /**
     * Livewire's generic property-update hook — fires for every nested field of
     * `editingBlock` (headings, images, repeater items, the variant radio, the
     * visibility toggle, …). Used to keep the draft $blocks array and the live
     * iframe preview in sync as the admin edits, without wiring a listener per field.
     */
    public function updated(string $name): void
    {
        if ($this->selectedKey === null) {
            return;
        }

        if ($name !== 'editingBlock' && ! str_starts_with($name, 'editingBlock.')) {
            return;
        }

        $this->applyRawEditingBlockToDraft();
        $this->pushPreview();
    }

    protected function applyRawEditingBlockToDraft(): void
    {
        foreach ($this->blocks as $i => $block) {
            if ($block['key'] !== $this->selectedKey) {
                continue;
            }

            $type = $this->editingBlock['type'] ?? $block['type'];

            $this->blocks[$i]['type'] = $type;
            $this->blocks[$i]['is_visible'] = (bool) ($this->editingBlock['is_visible'] ?? true);
            $this->blocks[$i]['data'] = $this->normalizeRepeaterShapes(
                $this->editingBlock['data'] ?? [],
                BlockDefinitions::schemaFor($type),
            );

            break;
        }
    }

    /**
     * Filament's Repeater keeps its LIVE (not-yet-dehydrated) Livewire state as an
     * associative array keyed by item UUID rather than a plain list — reading it
     * directly (as the live-preview sync above does, and as the validation-fallback
     * commit below does, both to stay reactive without a full form dehydration) can
     * leave e.g. `data.items` shaped as a PHP associative array. json_encode() then
     * serializes that as a JS *object* instead of an array, crashing any frontend code
     * that iterates/destructures it (e.g. `const [main, ...rest] = items`) — and if it
     * ever reaches save(), it corrupts the persisted block permanently. Reindex every
     * Repeater-shaped field (recursing one level into repeater-within-repeater, e.g.
     * pricing_table's per-plan "features" list) back to a plain list before it's used
     * anywhere else.
     */
    protected function normalizeRepeaterShapes(array $data, array $schema): array
    {
        foreach ($schema as $component) {
            if (! $component instanceof Forms\Components\Repeater) {
                continue;
            }

            $name = method_exists($component, 'getName') ? $component->getName() : null;

            if ($name === null) {
                continue;
            }

            $path = str_starts_with($name, 'data.') ? substr($name, strlen('data.')) : $name;
            $value = Arr::get($data, $path);

            if (! is_array($value)) {
                continue;
            }

            $value = array_values($value);
            $childSchema = $component->getChildComponents();

            foreach ($value as $i => $item) {
                if (is_array($item)) {
                    $value[$i] = $this->normalizeRepeaterShapes($item, $childSchema);
                }
            }

            Arr::set($data, $path, $value);
        }

        return $data;
    }

    /**
     * Best-effort "commit" of the currently-open block: tries a full validated
     * dehydration (which is also what actually moves a freshly-picked file upload
     * from Livewire's temp disk to permanent storage) and falls back to the raw,
     * already-synced draft data if required fields aren't filled in yet — a hard
     * validation block would make it impossible to hop between blocks mid-edit.
     */
    protected function commitSelectedBlock(): void
    {
        if ($this->selectedKey === null) {
            return;
        }

        try {
            $state = $this->editorForm->getState();

            $this->applyToSelectedBlock(
                $state['type'] ?? null,
                (bool) ($state['is_visible'] ?? true),
                $state['data'] ?? [],
            );
        } catch (ValidationException) {
            $this->applyToSelectedBlock(
                $this->editingBlock['type'] ?? null,
                (bool) ($this->editingBlock['is_visible'] ?? true),
                $this->editingBlock['data'] ?? [],
            );
        }
    }

    /**
     * Applies the given final state to the selected block and — only when it actually
     * differs from what's already stored — records one undo step for it. This groups
     * an entire visit to a block's edit form into a single undo step (matching most
     * editors' behavior) rather than one per keystroke, which `applyRawEditingBlockToDraft()`
     * (used for the live preview on every keystroke) intentionally does not do.
     */
    protected function applyToSelectedBlock(?string $type, bool $isVisible, array $data): void
    {
        foreach ($this->blocks as $i => $block) {
            if ($block['key'] !== $this->selectedKey) {
                continue;
            }

            $resolvedType = $type ?? $block['type'];

            $updated = $block;
            $updated['type'] = $resolvedType;
            $updated['is_visible'] = $isVisible;
            $updated['data'] = $this->normalizeRepeaterShapes($data, BlockDefinitions::schemaFor($resolvedType));

            if ($updated !== $block) {
                $this->snapshotForUndo();
                $this->blocks[$i] = $updated;
            }

            break;
        }
    }

    protected function snapshotForUndo(): void
    {
        $this->undoStack[] = $this->blocks;

        if (count($this->undoStack) > $this->maxHistorySize) {
            array_shift($this->undoStack);
        }

        $this->redoStack = [];
    }

    public function undo(): void
    {
        if ($this->undoStack === []) {
            return;
        }

        $this->redoStack[] = $this->blocks;
        $this->blocks = array_pop($this->undoStack);

        $this->selectedKey = null;
        $this->editingBlock = [];
        $this->isPickingType = false;

        $this->pushPreview();
    }

    public function redo(): void
    {
        if ($this->redoStack === []) {
            return;
        }

        $this->undoStack[] = $this->blocks;
        $this->blocks = array_pop($this->redoStack);

        $this->selectedKey = null;
        $this->editingBlock = [];
        $this->isPickingType = false;

        $this->pushPreview();
    }

    public function selectBlock(string $key): void
    {
        $this->commitSelectedBlock();

        $this->isPickingType = false;
        $this->selectedKey = $key;

        $block = collect($this->blocks)->firstWhere('key', $key);
        $type = $block['type'] ?? '';

        // Fill (not a raw property assignment) so dynamically-resolved fields — the
        // Group built from BlockDefinitions::schemaFor($type), which only exists once
        // "type" is known — go through Filament's normal hydration pass. Skipping that
        // leaves components like FileUpload holding a raw un-normalized value (a plain
        // stored path instead of the internal file-key => path array they expect).
        $this->editorForm->fill([
            'type' => $type,
            'is_visible' => $block['is_visible'] ?? true,
            // Merge over a full skeleton of every field the current schema declares —
            // older/seeded blocks can be missing a key the schema later grew (e.g. an
            // "_en" translation sibling, or the free-form data.custom KeyValue), and an
            // absent key breaks Livewire's JS-side entangle binding for that field.
            'data' => array_replace_recursive($this->buildDataSkeleton($type), $block['data'] ?? []),
        ]);
    }

    /**
     * Every `data.*` leaf field the current block type's schema declares, defaulted to
     * null (or an empty array for list-shaped fields) — see selectBlock() for why.
     */
    protected function buildDataSkeleton(string $type): array
    {
        $skeleton = [];

        foreach (BlockDefinitions::schemaFor($type) as $component) {
            if (! method_exists($component, 'getName')) {
                continue;
            }

            $name = $component->getName();

            if (! str_starts_with($name, 'data.')) {
                continue;
            }

            $isListShaped = $component instanceof Forms\Components\Repeater
                || $component instanceof Forms\Components\KeyValue
                || ($component instanceof Forms\Components\BaseFileUpload && $component->isMultiple());

            Arr::set($skeleton, substr($name, strlen('data.')), $isListShaped ? [] : null);
        }

        return $skeleton;
    }

    public function deselectBlock(): void
    {
        $this->commitSelectedBlock();

        $this->selectedKey = null;
        $this->editingBlock = [];
    }

    public function startPickingType(): void
    {
        $this->commitSelectedBlock();

        $this->selectedKey = null;
        $this->editingBlock = [];
        $this->isPickingType = true;
    }

    public function cancelPickingType(): void
    {
        $this->isPickingType = false;
    }

    public function addBlock(string $type): void
    {
        if (! array_key_exists($type, BlockDefinitions::options())) {
            return;
        }

        $variants = BlockDefinitions::variantOptions($type);

        $key = (string) Str::uuid();

        $this->snapshotForUndo();

        $this->blocks[] = [
            'key' => $key,
            'id' => null,
            'order' => count($this->blocks),
            'type' => $type,
            'is_visible' => true,
            'data' => $variants !== [] ? ['variant' => array_key_first($variants)] : [],
        ];

        $this->isPickingType = false;
        $this->selectBlock($key);
        $this->pushPreview();
    }

    public function toggleVisible(string $key): void
    {
        foreach ($this->blocks as $i => $block) {
            if ($block['key'] !== $key) {
                continue;
            }

            $this->snapshotForUndo();

            $this->blocks[$i]['is_visible'] = ! $block['is_visible'];

            if ($key === $this->selectedKey) {
                $this->editingBlock['is_visible'] = $this->blocks[$i]['is_visible'];
            }

            break;
        }

        $this->pushPreview();
    }

    public function moveUp(string $key): void
    {
        $this->moveBlock($key, -1);
    }

    public function moveDown(string $key): void
    {
        $this->moveBlock($key, 1);
    }

    protected function moveBlock(string $key, int $direction): void
    {
        $index = collect($this->blocks)->search(fn (array $b): bool => $b['key'] === $key);

        if ($index === false) {
            return;
        }

        $target = $index + $direction;

        if ($target < 0 || $target >= count($this->blocks)) {
            return;
        }

        $this->snapshotForUndo();

        $blocks = $this->blocks;
        [$blocks[$index], $blocks[$target]] = [$blocks[$target], $blocks[$index]];
        $this->blocks = array_values($blocks);

        $this->reindexOrder();
        $this->pushPreview();
    }

    /**
     * Drag-and-drop reorder from the sidebar (SortableJS) — receives the block keys in
     * their new visual order and rebuilds $blocks to match.
     *
     * @param  array<int, string>  $keys
     */
    public function reorderBlocks(array $keys): void
    {
        $this->commitSelectedBlock();
        $this->snapshotForUndo();

        $lookup = collect($this->blocks)->keyBy('key');

        $this->blocks = collect($keys)
            ->map(fn (string $key) => $lookup->get($key))
            ->filter()
            ->values()
            ->all();

        $this->reindexOrder();
        $this->pushPreview();
    }

    public function removeBlock(string $key): void
    {
        $this->snapshotForUndo();

        $this->blocks = collect($this->blocks)
            ->reject(fn (array $b): bool => $b['key'] === $key)
            ->values()
            ->all();

        $this->reindexOrder();

        if ($this->selectedKey === $key) {
            $this->selectedKey = null;
            $this->editingBlock = [];
        }

        $this->pushPreview();
    }

    /**
     * Inserts a copy of the given block right after itself — a new draft (unsaved)
     * block with the same content, auto-selected so the admin can tweak it immediately.
     */
    public function duplicateBlock(string $key): void
    {
        $index = collect($this->blocks)->search(fn (array $b): bool => $b['key'] === $key);

        if ($index === false) {
            return;
        }

        $this->snapshotForUndo();

        $copy = $this->blocks[$index];
        $copy['key'] = (string) Str::uuid();
        $copy['id'] = null;

        array_splice($this->blocks, $index + 1, 0, [$copy]);

        $this->reindexOrder();
        $this->selectBlock($copy['key']);
        $this->pushPreview();
    }

    protected function reindexOrder(): void
    {
        foreach ($this->blocks as $i => $block) {
            $this->blocks[$i]['order'] = $i;
        }
    }

    /**
     * Mirrors the public API's block shape (id/type/order/data with media resolved to
     * real URLs) so the embedded `/preview` route renders exactly like the live site —
     * only visible blocks are included, matching what a visitor would actually see.
     *
     * @return array<int, array{id: int|string, type: string, order: int, data: array}>
     */
    protected function buildPreviewPayload(): array
    {
        return collect($this->blocks)
            ->filter(fn (array $b): bool => $b['is_visible'] ?? true)
            ->values()
            ->map(fn (array $b, int $i): array => [
                'id' => $b['id'] ?? $b['key'],
                'type' => $b['type'],
                'order' => $i,
                'data' => MediaResolver::resolveDeep(Block::localize($b['data'] ?? [], 'id')),
            ])
            ->all();
    }

    public function pushPreview(): void
    {
        $this->dispatch('preview-updated', blocks: $this->buildPreviewPayload());
    }

    public function getPreviewPayload(): array
    {
        return $this->buildPreviewPayload();
    }

    public function getPreviewUrl(): string
    {
        return rtrim(config('app.frontend_url'), '/').'/preview';
    }

    public function getPreviewOrigin(): string
    {
        $parts = parse_url($this->getPreviewUrl());

        $origin = ($parts['scheme'] ?? 'https').'://'.($parts['host'] ?? '');

        if (isset($parts['port'])) {
            $origin .= ':'.$parts['port'];
        }

        return $origin;
    }

    public function save(): void
    {
        $this->commitSelectedBlock();

        DB::transaction(function (): void {
            $existingIds = collect($this->blocks)->pluck('id')->filter()->all();

            Block::where('page_id', $this->record->id)
                ->whereNotIn('id', $existingIds === [] ? [0] : $existingIds)
                ->delete();

            foreach ($this->blocks as $i => $block) {
                $attributes = [
                    'page_id' => $this->record->id,
                    'type' => $block['type'],
                    'order' => $i,
                    'is_visible' => $block['is_visible'],
                    'data' => $block['data'],
                ];

                if ($block['id']) {
                    Block::whereKey($block['id'])->update($attributes);
                } else {
                    $this->blocks[$i]['id'] = Block::create($attributes)->id;
                    $this->blocks[$i]['key'] = (string) $this->blocks[$i]['id'];
                }
            }
        });

        Notification::make()
            ->title('Semua perubahan tersimpan')
            ->success()
            ->send();

        $this->loadBlocksFromRecord();
        $this->selectedKey = null;
        $this->editingBlock = [];
        // Old snapshots reference pre-save keys/ids for newly-created blocks, which
        // save() just rewrote — undoing past a save would restore a mismatched state.
        $this->undoStack = [];
        $this->redoStack = [];
        $this->pushPreview();
    }
}
