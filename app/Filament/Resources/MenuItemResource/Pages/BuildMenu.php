<?php

namespace App\Filament\Resources\MenuItemResource\Pages;

use App\Filament\Resources\MenuItemResource;
use App\Filament\Support\TranslatableField;
use App\Models\MenuItem;
use App\Support\MenuDropdownStyles;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * WordPress-style visual builder for the navbar menu tree — same concept as
 * PageResource\Pages\BuildPage (sidebar list + a live iframe of the real frontend kept
 * in sync via postMessage), but scoped to just the menu instead of a page's content
 * blocks. The existing table+relation-manager UI (ListMenuItems/EditMenuItem) is left
 * untouched as an alternative way to manage the menu.
 */
class BuildMenu extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $resource = MenuItemResource::class;

    protected static string $view = 'filament.resources.menu-item-resource.pages.build-menu';

    protected ?string $maxContentWidth = 'full';

    /**
     * Draft, in-memory menu tree (including hidden items), in display order. Each
     * top-level entry additionally carries a `children` list of the same shape (minus
     * `dropdown_style`, which only applies to a top-level item's own dropdown). `key`
     * is a stable string identity for Livewire/Alpine tracking — the DB id once saved,
     * a uuid while still unsaved. Not persisted until save() runs.
     *
     * @var array<int, array{key: string, id: int|null, label: string, label_en: ?string, url: ?string, is_visible: bool, open_in_new_tab: bool, dropdown_style: string, children: array}>
     */
    public array $items = [];

    public ?string $selectedKey = null;

    /** Whether $selectedKey currently points at a child (sub menu) row rather than a top-level one. */
    public bool $editingIsChild = false;

    /**
     * Mirrors the currently-selected item's editable fields, bound to editorForm().
     * Synced back into $items whenever the selection changes or the page is saved.
     *
     * @var array{label?: string, label_en?: ?string, url?: ?string, open_in_new_tab?: bool, is_visible?: bool, dropdown_style?: string}
     */
    public array $editingItem = [];

    public function mount(): void
    {
        $this->authorizeAccess();

        $this->loadItemsFromRecords();
    }

    /**
     * No per-record `{record}` route param here (unlike BuildPage), so check the
     * general "can edit menu items" ability instead of a specific model instance.
     */
    protected function authorizeAccess(): void
    {
        abort_unless(static::getResource()::canEdit(new MenuItem()), 403);
    }

    public function getTitle(): string
    {
        return 'Desain Menu Navbar';
    }

    protected function loadItemsFromRecords(): void
    {
        $this->items = MenuItem::query()
            ->whereNull('parent_id')
            ->with(['children' => fn ($query) => $query->orderBy('order')])
            ->orderBy('order')
            ->get()
            ->map(fn (MenuItem $item): array => [
                'key' => (string) $item->id,
                'id' => $item->id,
                'label' => $item->label,
                'label_en' => $item->label_en,
                'url' => $item->url,
                'is_visible' => $item->is_visible,
                'open_in_new_tab' => $item->open_in_new_tab,
                'dropdown_style' => $item->dropdown_style ?? 'simple',
                'children' => $item->children->map(fn (MenuItem $child): array => [
                    'key' => (string) $child->id,
                    'id' => $child->id,
                    'label' => $child->label,
                    'label_en' => $child->label_en,
                    'url' => $child->url,
                    'is_visible' => $child->is_visible,
                    'open_in_new_tab' => $child->open_in_new_tab,
                ])->values()->all(),
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
                ...TranslatableField::text('label', 'Teks Menu', required: true, placeholder: 'Contoh: Beranda, Tentang, Kontak'),
                Forms\Components\TextInput::make('url')
                    ->label('Link')
                    ->helperText('Contoh: "/", "/about", "/kontak". Kosongkan jika menu ini hanya jadi induk dropdown.')
                    ->maxLength(255)
                    ->prefixIcon('heroicon-o-link')
                    ->placeholder('/about')
                    ->live(onBlur: true),
                Forms\Components\Toggle::make('open_in_new_tab')
                    ->label('Buka di tab baru')
                    ->live(),
                Forms\Components\Toggle::make('is_visible')
                    ->label('Tampilkan di navbar')
                    ->live(),
                Forms\Components\Select::make('dropdown_style')
                    ->label('Gaya Dropdown')
                    ->helperText('Cara tampil sub menu di navbar — hanya berlaku jika menu ini punya sub menu.')
                    ->options(MenuDropdownStyles::options())
                    ->default('simple')
                    ->native(false)
                    ->live()
                    ->visible(fn (): bool => ! $this->editingIsChild),
            ])
            ->columns(1)
            ->statePath('editingItem');
    }

    /**
     * Livewire's generic property-update hook — fires for every field of `editingItem`
     * (label, url, toggles, dropdown style). Used to keep the draft $items array and
     * the live iframe preview in sync as the admin edits, without wiring a per-field
     * listener — mirrors BuildPage's own `updated()`.
     */
    public function updated(string $name): void
    {
        if ($this->selectedKey === null) {
            return;
        }

        if ($name !== 'editingItem' && ! str_starts_with($name, 'editingItem.')) {
            return;
        }

        $this->applyRawEditingToDraft();
        $this->pushPreview();
    }

    protected function applyRawEditingToDraft(): void
    {
        $this->mutateSelected(fn (array $item): array => array_merge($item, $this->editingItemAsData()));
    }

    /**
     * @return array{label: string, label_en: ?string, url: ?string, open_in_new_tab: bool, is_visible: bool, dropdown_style?: string}
     */
    protected function editingItemAsData(?array $state = null): array
    {
        $state ??= $this->editingItem;

        $data = [
            'label' => $state['label'] ?? '',
            'label_en' => $state['label_en'] ?? null,
            'url' => $state['url'] ?? null,
            'open_in_new_tab' => (bool) ($state['open_in_new_tab'] ?? false),
            'is_visible' => (bool) ($state['is_visible'] ?? true),
        ];

        if (! $this->editingIsChild) {
            $data['dropdown_style'] = $state['dropdown_style'] ?? 'simple';
        }

        return $data;
    }

    /**
     * Best-effort "commit" of the currently-open item: tries a full validated
     * dehydration and falls back to the raw, already-synced draft data if required
     * fields aren't filled in yet — a hard validation block would make it impossible
     * to hop between items mid-edit. Mirrors BuildPage's commitSelectedBlock().
     */
    protected function commitSelected(): void
    {
        if ($this->selectedKey === null) {
            return;
        }

        try {
            $state = $this->editorForm->getState();
        } catch (ValidationException) {
            $state = $this->editingItem;
        }

        $this->mutateSelected(fn (array $item): array => array_merge($item, $this->editingItemAsData($state)));
    }

    /**
     * Applies a mutator to whichever item $selectedKey currently points at (a
     * top-level item or one of its children), in place within $this->items.
     */
    protected function mutateSelected(\Closure $mutate): void
    {
        foreach ($this->items as $i => $item) {
            if ($item['key'] === $this->selectedKey) {
                $this->items[$i] = $mutate($item);

                return;
            }

            foreach ($item['children'] as $j => $child) {
                if ($child['key'] === $this->selectedKey) {
                    $this->items[$i]['children'][$j] = $mutate($child);

                    return;
                }
            }
        }
    }

    public function selectItem(string $key): void
    {
        $this->commitSelected();

        $found = $this->findItem($key);

        if ($found === null) {
            return;
        }

        [$item, $isChild] = $found;

        $this->selectedKey = $key;
        $this->editingIsChild = $isChild;

        $this->editorForm->fill([
            'label' => $item['label'],
            'label_en' => $item['label_en'],
            'url' => $item['url'],
            'open_in_new_tab' => $item['open_in_new_tab'],
            'is_visible' => $item['is_visible'],
            'dropdown_style' => $item['dropdown_style'] ?? 'simple',
        ]);
    }

    /**
     * @return array{0: array, 1: bool}|null [item, isChild]
     */
    protected function findItem(string $key): ?array
    {
        foreach ($this->items as $item) {
            if ($item['key'] === $key) {
                return [$item, false];
            }

            foreach ($item['children'] as $child) {
                if ($child['key'] === $key) {
                    return [$child, true];
                }
            }
        }

        return null;
    }

    public function deselectItem(): void
    {
        $this->commitSelected();

        $this->selectedKey = null;
        $this->editingItem = [];
    }

    public function addTopLevel(): void
    {
        $this->commitSelected();

        $key = (string) Str::uuid();

        $this->items[] = [
            'key' => $key,
            'id' => null,
            'label' => 'Menu Baru',
            'label_en' => null,
            'url' => null,
            'is_visible' => true,
            'open_in_new_tab' => false,
            'dropdown_style' => 'simple',
            'children' => [],
        ];

        $this->selectItem($key);
        $this->pushPreview();
    }

    public function addChild(string $parentKey): void
    {
        $this->commitSelected();

        foreach ($this->items as $i => $item) {
            if ($item['key'] !== $parentKey) {
                continue;
            }

            $key = (string) Str::uuid();

            $this->items[$i]['children'][] = [
                'key' => $key,
                'id' => null,
                'label' => 'Sub Menu Baru',
                'label_en' => null,
                'url' => null,
                'is_visible' => true,
                'open_in_new_tab' => false,
            ];

            $this->selectItem($key);
            break;
        }

        $this->pushPreview();
    }

    public function removeItem(string $key): void
    {
        foreach ($this->items as $i => $item) {
            if ($item['key'] === $key) {
                unset($this->items[$i]);
                $this->items = array_values($this->items);
                $this->afterRemove($key);

                return;
            }

            foreach ($item['children'] as $j => $child) {
                if ($child['key'] === $key) {
                    unset($this->items[$i]['children'][$j]);
                    $this->items[$i]['children'] = array_values($this->items[$i]['children']);
                    $this->afterRemove($key);

                    return;
                }
            }
        }
    }

    protected function afterRemove(string $key): void
    {
        if ($this->selectedKey === $key) {
            $this->selectedKey = null;
            $this->editingItem = [];
        }

        $this->pushPreview();
    }

    public function toggleVisible(string $key): void
    {
        $this->mutateByKey($key, function (array $item) use ($key): array {
            $item['is_visible'] = ! $item['is_visible'];

            if ($key === $this->selectedKey) {
                $this->editingItem['is_visible'] = $item['is_visible'];
            }

            return $item;
        });

        $this->pushPreview();
    }

    protected function mutateByKey(string $key, \Closure $mutate): void
    {
        foreach ($this->items as $i => $item) {
            if ($item['key'] === $key) {
                $this->items[$i] = $mutate($item);

                return;
            }

            foreach ($item['children'] as $j => $child) {
                if ($child['key'] === $key) {
                    $this->items[$i]['children'][$j] = $mutate($child);

                    return;
                }
            }
        }
    }

    /**
     * Drag-and-drop reorder of the top-level items (SortableJS) — receives the keys in
     * their new visual order and rebuilds $items to match.
     *
     * @param  array<int, string>  $keys
     */
    public function reorderTopLevel(array $keys): void
    {
        $this->commitSelected();

        $lookup = collect($this->items)->keyBy('key');

        $this->items = collect($keys)
            ->map(fn (string $key) => $lookup->get($key))
            ->filter()
            ->values()
            ->all();

        $this->pushPreview();
    }

    /**
     * Drag-and-drop reorder of one parent's children.
     *
     * @param  array<int, string>  $keys
     */
    public function reorderChildren(string $parentKey, array $keys): void
    {
        $this->commitSelected();

        foreach ($this->items as $i => $item) {
            if ($item['key'] !== $parentKey) {
                continue;
            }

            $lookup = collect($item['children'])->keyBy('key');

            $this->items[$i]['children'] = collect($keys)
                ->map(fn (string $key) => $lookup->get($key))
                ->filter()
                ->values()
                ->all();

            break;
        }

        $this->pushPreview();
    }

    /**
     * Mirrors the public API's menu shape (MenuController::transform) so the embedded
     * `/preview` route's navbar renders exactly like the live site — only visible
     * items are included, matching what a visitor would actually see.
     */
    protected function buildPreviewMenu(): array
    {
        return collect($this->items)
            ->filter(fn (array $item): bool => $item['is_visible'])
            ->values()
            ->map(fn (array $item): array => [
                'label' => $item['label'],
                'url' => $item['url'],
                'open_in_new_tab' => $item['open_in_new_tab'],
                'dropdown_style' => $item['dropdown_style'] ?? 'simple',
                'children' => collect($item['children'])
                    ->filter(fn (array $child): bool => $child['is_visible'])
                    ->values()
                    ->map(fn (array $child): array => [
                        'label' => $child['label'],
                        'url' => $child['url'],
                        'open_in_new_tab' => $child['open_in_new_tab'],
                        'dropdown_style' => null,
                        'children' => [],
                    ])
                    ->all(),
            ])
            ->all();
    }

    public function pushPreview(): void
    {
        $this->dispatch('menu-preview-updated', menu: $this->buildPreviewMenu());
    }

    public function getPreviewPayload(): array
    {
        return $this->buildPreviewMenu();
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
        $this->commitSelected();

        DB::transaction(function (): void {
            $existingTopIds = collect($this->items)->pluck('id')->filter()->all();
            $existingChildIds = collect($this->items)
                ->flatMap(fn (array $item) => collect($item['children'])->pluck('id'))
                ->filter()
                ->all();
            $allExistingIds = array_merge($existingTopIds, $existingChildIds);

            MenuItem::whereNotIn('id', $allExistingIds === [] ? [0] : $allExistingIds)->delete();

            foreach ($this->items as $i => $item) {
                $attributes = [
                    'parent_id' => null,
                    'label' => $item['label'],
                    'label_en' => $item['label_en'],
                    'url' => $item['url'],
                    'order' => $i,
                    'is_visible' => $item['is_visible'],
                    'open_in_new_tab' => $item['open_in_new_tab'],
                    'dropdown_style' => $item['dropdown_style'] ?? 'simple',
                ];

                if ($item['id']) {
                    MenuItem::whereKey($item['id'])->update($attributes);
                    $topId = $item['id'];
                } else {
                    $topId = MenuItem::create($attributes)->id;
                    $this->items[$i]['id'] = $topId;
                    $this->items[$i]['key'] = (string) $topId;
                }

                foreach ($item['children'] as $j => $child) {
                    $childAttributes = [
                        'parent_id' => $topId,
                        'label' => $child['label'],
                        'label_en' => $child['label_en'],
                        'url' => $child['url'],
                        'order' => $j,
                        'is_visible' => $child['is_visible'],
                        'open_in_new_tab' => $child['open_in_new_tab'],
                        'dropdown_style' => null,
                    ];

                    if ($child['id']) {
                        MenuItem::whereKey($child['id'])->update($childAttributes);
                    } else {
                        $childId = MenuItem::create($childAttributes)->id;
                        $this->items[$i]['children'][$j]['id'] = $childId;
                        $this->items[$i]['children'][$j]['key'] = (string) $childId;
                    }
                }
            }
        });

        Notification::make()
            ->title('Menu navbar tersimpan')
            ->success()
            ->send();

        $this->loadItemsFromRecords();
        $this->selectedKey = null;
        $this->editingItem = [];
        $this->pushPreview();
    }
}
