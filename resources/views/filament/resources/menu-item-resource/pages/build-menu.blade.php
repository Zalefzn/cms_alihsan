{{--
    Plain inline styles throughout (like build-page.blade.php / block-type-preview.blade.php)
    — this panel's CSS is a small hand-written override file (public/css/admin-theme.css),
    not a Tailwind build that scans the app's own Blade views, so arbitrary Tailwind
    utility classes used here would silently have no effect.
--}}
<x-filament-panels::page>
    <div
        x-data="alihsanMenuBuilder(@js($this->getPreviewOrigin()))"
        x-on:menu-preview-updated.window="setMenu($event.detail.menu)"
        style="display:flex;flex-direction:column;gap:12px;"
    >
        <div style="display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:12px;border:1px solid #e5e7eb;border-radius:12px;background:#fff;padding:12px;">
            <a
                href="{{ \App\Filament\Resources\MenuItemResource::getUrl('index') }}"
                style="font-size:13px;font-weight:600;color:#6b7280;text-decoration:none;"
            >
                &larr; Kembali ke Daftar Menu (alternatif lama)
            </a>

            <button
                type="button"
                wire:click="save"
                wire:loading.attr="disabled"
                style="display:inline-flex;align-items:center;justify-content:center;gap:6px;border-radius:8px;background:#4f46e5;padding:10px 16px;font-size:13px;font-weight:600;color:#fff;border:0;cursor:pointer;"
            >
                <span wire:loading.remove wire:target="save">Simpan Semua Perubahan</span>
                <span wire:loading wire:target="save">Menyimpan…</span>
            </button>
        </div>

        <div style="display:flex;gap:12px;min-height:75vh;">
            {{-- Sidebar: menu tree navigator + inline editor, same WordPress-layout-builder style as BuildPage --}}
            <div style="display:flex;flex-direction:column;width:420px;flex-shrink:0;overflow-y:auto;max-height:82vh;border:1px solid #e5e7eb;border-radius:12px;background:#fff;">
                <div style="display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid #e5e7eb;padding:12px;">
                    <h2 style="font-size:13px;font-weight:700;color:#111827;margin:0;">Susunan Menu Navbar</h2>
                    <button
                        type="button"
                        wire:click="addTopLevel"
                        style="display:inline-flex;align-items:center;gap:4px;border-radius:8px;background:#eef2ff;border:0;padding:6px 10px;font-size:12px;font-weight:600;color:#4f46e5;cursor:pointer;"
                    >
                        + Menu Utama
                    </button>
                </div>

                <div x-ref="topList" x-init="initTopSortable()" style="display:flex;flex-direction:column;">
                    @forelse ($items as $item)
                        <div wire:key="menu-item-{{ $item['key'] }}" data-item-key="{{ $item['key'] }}" style="border-bottom:1px solid #f3f4f6;">
                            <div style="display:flex;align-items:center;gap:6px;padding:8px 12px;{{ $selectedKey === $item['key'] ? 'background:#eef2ff;' : '' }}{{ $item['is_visible'] ? '' : 'opacity:.5;' }}">
                                <div class="menu-drag-handle" title="Geser untuk mengurutkan" style="cursor:grab;color:#cbd5e1;flex-shrink:0;">
                                    <svg style="width:14px;height:14px;" fill="currentColor" viewBox="0 0 20 20"><path d="M7 4a1 1 0 110-2 1 1 0 010 2zm0 6a1 1 0 110-2 1 1 0 010 2zm0 6a1 1 0 110-2 1 1 0 010 2zm6-12a1 1 0 110-2 1 1 0 010 2zm0 6a1 1 0 110-2 1 1 0 010 2zm0 6a1 1 0 110-2 1 1 0 010 2z" /></svg>
                                </div>

                                <button
                                    type="button"
                                    wire:click="selectItem('{{ $item['key'] }}')"
                                    style="min-width:0;flex:1;text-align:left;background:none;border:0;padding:0;cursor:pointer;"
                                >
                                    <div style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;font-size:13px;font-weight:600;color:#111827;">
                                        {{ $item['label'] !== '' ? $item['label'] : '(Tanpa label)' }}
                                    </div>
                                    @if ($item['url'])
                                        <div style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;font-size:11px;color:#6b7280;">{{ $item['url'] }}</div>
                                    @endif
                                </button>

                                <button type="button" wire:click="toggleVisible('{{ $item['key'] }}')" title="{{ $item['is_visible'] ? 'Sembunyikan' : 'Tampilkan' }}" style="background:none;border:0;padding:2px;cursor:pointer;color:#9ca3af;flex-shrink:0;">
                                    @if ($item['is_visible'])
                                        <svg style="width:14px;height:14px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                    @else
                                        <svg style="width:14px;height:14px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0112 4.5c4.756 0 8.774 3.162 10.065 7.498a10.522 10.522 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" /></svg>
                                    @endif
                                </button>
                                <button type="button" wire:click="addChild('{{ $item['key'] }}')" title="Tambah Sub Menu" style="background:none;border:0;padding:2px;cursor:pointer;color:#9ca3af;flex-shrink:0;">
                                    <svg style="width:14px;height:14px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                                </button>
                                <button type="button" wire:click="removeItem('{{ $item['key'] }}')" title="Hapus" style="background:none;border:0;padding:2px;cursor:pointer;color:#f87171;flex-shrink:0;">
                                    <svg style="width:14px;height:14px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" /></svg>
                                </button>
                            </div>

                            @if (count($item['children']) > 0)
                                <div
                                    x-init="initChildSortable($el, @js($item['key']))"
                                    style="margin-left:34px;border-left:2px solid #e5e7eb;padding-left:8px;"
                                >
                                    @foreach ($item['children'] as $child)
                                        <div wire:key="menu-child-{{ $child['key'] }}" data-child-key="{{ $child['key'] }}" style="display:flex;align-items:center;gap:6px;padding:6px 8px;{{ $selectedKey === $child['key'] ? 'background:#eef2ff;' : '' }}{{ $child['is_visible'] ? '' : 'opacity:.5;' }}">
                                            <div class="submenu-drag-handle" title="Geser untuk mengurutkan" style="cursor:grab;color:#cbd5e1;flex-shrink:0;">
                                                <svg style="width:12px;height:12px;" fill="currentColor" viewBox="0 0 20 20"><path d="M7 4a1 1 0 110-2 1 1 0 010 2zm0 6a1 1 0 110-2 1 1 0 010 2zm0 6a1 1 0 110-2 1 1 0 010 2zm6-12a1 1 0 110-2 1 1 0 010 2zm0 6a1 1 0 110-2 1 1 0 010 2zm0 6a1 1 0 110-2 1 1 0 010 2z" /></svg>
                                            </div>

                                            <button
                                                type="button"
                                                wire:click="selectItem('{{ $child['key'] }}')"
                                                style="min-width:0;flex:1;text-align:left;background:none;border:0;padding:0;cursor:pointer;"
                                            >
                                                <div style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;font-size:12px;font-weight:500;color:#374151;">
                                                    {{ $child['label'] !== '' ? $child['label'] : '(Tanpa label)' }}
                                                </div>
                                            </button>

                                            <button type="button" wire:click="toggleVisible('{{ $child['key'] }}')" title="{{ $child['is_visible'] ? 'Sembunyikan' : 'Tampilkan' }}" style="background:none;border:0;padding:2px;cursor:pointer;color:#9ca3af;flex-shrink:0;">
                                                <svg style="width:12px;height:12px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                            </button>
                                            <button type="button" wire:click="removeItem('{{ $child['key'] }}')" title="Hapus" style="background:none;border:0;padding:2px;cursor:pointer;color:#f87171;flex-shrink:0;">
                                                <svg style="width:12px;height:12px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                                            </button>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @empty
                        <p style="padding:16px;font-size:12px;color:#9ca3af;margin:0;">Belum ada menu. Klik "+ Menu Utama" untuk mulai.</p>
                    @endforelse
                </div>

                @if ($selectedKey !== null)
                    <div style="border-top:1px solid #e5e7eb;padding:12px;">
                        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;">
                            <strong style="font-size:12px;color:#111827;">{{ $editingIsChild ? 'Edit Sub Menu' : 'Edit Menu' }}</strong>
                            <button type="button" wire:click="deselectItem" style="font-size:11px;color:#6b7280;background:none;border:0;cursor:pointer;">Tutup</button>
                        </div>
                        {{ $this->editorForm }}
                    </div>
                @endif
            </div>

            {{-- Canvas: the real React frontend rendered live, kept in sync via postMessage —
                 wrapped in a device-width switcher so the admin can preview mobile/tablet/desktop
                 without leaving the builder. Only the navbar (part of the shared Layout) reflects
                 the draft menu; the page body below it just shows the "waiting for editor" state
                 since no page-block data is sent here. --}}
            <div style="display:flex;flex-direction:column;min-width:0;flex:1;overflow:hidden;border:1px solid #e5e7eb;border-radius:12px;background:#fff;">
                <div style="display:flex;align-items:center;justify-content:center;gap:6px;border-bottom:1px solid #e5e7eb;background:#f9fafb;padding:8px;">
                    <template x-for="option in deviceOptions" :key="option.key">
                        <button
                            type="button"
                            x-on:click="device = option.key"
                            :style="device === option.key
                                ? 'display:inline-flex;align-items:center;gap:5px;border-radius:8px;border:0;background:#4f46e5;color:#fff;padding:6px 12px;font-size:12px;font-weight:600;cursor:pointer;'
                                : 'display:inline-flex;align-items:center;gap:5px;border-radius:8px;border:0;background:transparent;color:#6b7280;padding:6px 12px;font-size:12px;font-weight:600;cursor:pointer;'"
                        >
                            <span x-html="option.icon"></span>
                            <span x-text="option.label"></span>
                        </button>
                    </template>
                    <span style="margin-left:6px;font-size:11px;color:#9ca3af;" x-text="deviceOptions.find(o => o.key === device).width ? deviceOptions.find(o => o.key === device).width + 'px' : 'Mengikuti lebar kanvas'"></span>
                </div>

                <div style="flex:1;overflow:auto;background:#e5e7eb;padding:20px;display:flex;justify-content:center;">
                    <div :style="`width:${deviceOptions.find(o => o.key === device).width ? deviceOptions.find(o => o.key === device).width + 'px' : '100%'};height:82vh;flex-shrink:0;background:#fff;${deviceOptions.find(o => o.key === device).width ? 'box-shadow:0 1px 4px rgba(0,0,0,.15);' : ''}`">
                        <iframe
                            x-ref="canvas"
                            x-on:load="onIframeLoad()"
                            src="{{ $this->getPreviewUrl() }}"
                            style="width:100%;height:100%;border:0;display:block;"
                        ></iframe>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.2/Sortable.min.js"></script>
    <script>
        function alihsanMenuBuilder(targetOrigin) {
            return {
                targetOrigin,
                menu: @js($this->getPreviewPayload()),
                ready: false,
                device: 'full',
                deviceOptions: [
                    { key: 'full', label: 'Lebar Penuh', width: null, icon: '<svg style="width:14px;height:14px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3.75v4.5m0-4.5h4.5m-4.5 0L9 9M3.75 20.25v-4.5m0 4.5h4.5m-4.5 0L9 15M20.25 3.75h-4.5m4.5 0v4.5m0-4.5L15 9m5.25 11.25h-4.5m4.5 0v-4.5m0 4.5L15 15" /></svg>' },
                    { key: 'tablet', label: 'Tablet', width: 768, icon: '<svg style="width:14px;height:14px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5h3m-6.75 1.5h10.5a1.5 1.5 0 001.5-1.5V4.5a1.5 1.5 0 00-1.5-1.5H6.75a1.5 1.5 0 00-1.5 1.5v15a1.5 1.5 0 001.5 1.5z" /></svg>' },
                    { key: 'mobile', label: 'Mobile', width: 375, icon: '<svg style="width:14px;height:14px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 006 3.75v16.5a2.25 2.25 0 002.25 2.25h7.5A2.25 2.25 0 0018 20.25V3.75a2.25 2.25 0 00-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3" /></svg>' },
                ],
                init() {
                    window.addEventListener('message', (event) => {
                        if (event.origin !== this.targetOrigin) {
                            return;
                        }

                        if (event.data?.type === 'alihsan-preview-ready') {
                            this.ready = true;
                            this.send();
                        }
                    });
                },
                onIframeLoad() {
                    this.ready = false;
                },
                // Livewire's morphdom keeps each row's DOM node stable across re-renders
                // (they carry a stable wire:key), so this only needs to run once — SortableJS
                // just needs the container to exist, and it drags whatever children are
                // currently inside it.
                initTopSortable() {
                    new Sortable(this.$refs.topList, {
                        handle: '.menu-drag-handle',
                        animation: 150,
                        forceFallback: true,
                        fallbackTolerance: 3,
                        onEnd: () => {
                            const keys = Array.from(this.$refs.topList.children)
                                .map((el) => el.dataset.itemKey)
                                .filter(Boolean);

                            this.$wire.reorderTopLevel(keys);
                        },
                    });
                },
                // Called via x-init on each parent's children container — there can be several
                // of these at once (one per top-level item that has sub menus).
                initChildSortable(el, parentKey) {
                    new Sortable(el, {
                        handle: '.submenu-drag-handle',
                        animation: 150,
                        forceFallback: true,
                        fallbackTolerance: 3,
                        onEnd: () => {
                            const keys = Array.from(el.children)
                                .map((child) => child.dataset.childKey)
                                .filter(Boolean);

                            this.$wire.reorderChildren(parentKey, keys);
                        },
                    });
                },
                setMenu(menu) {
                    this.menu = menu;
                    this.send();
                },
                send() {
                    if (!this.ready || !this.$refs.canvas?.contentWindow) {
                        return;
                    }

                    this.$refs.canvas.contentWindow.postMessage(
                        // Alpine's reactive proxy around `menu` can't survive postMessage's
                        // structured-clone algorithm — send a plain deep copy instead.
                        JSON.parse(JSON.stringify({ type: 'alihsan-menu-preview-update', menu: this.menu })),
                        this.targetOrigin,
                    );
                },
            };
        }
    </script>
</x-filament-panels::page>
