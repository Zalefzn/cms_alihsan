<div class="flex min-h-screen w-full">
    {{-- Decorative left panel — hidden on small screens --}}
        <div
            class="relative hidden w-1/2 flex-col justify-between overflow-hidden p-12 lg:flex"
            style="background: linear-gradient(155deg, #ffffff 0%, #f3e8ff 35%, #ddd6fe 60%, #f9a8d4 100%);"
        >
            <div class="pointer-events-none absolute -right-24 -top-24 h-96 w-96 rounded-full bg-fuchsia-300/30 blur-3xl"></div>
            <div class="pointer-events-none absolute -bottom-32 -left-16 h-80 w-80 rounded-full bg-violet-300/40 blur-3xl"></div>

            <div class="relative flex items-center gap-x-4">
                <img
                    src="{{ asset('images/logo.png') }}"
                    alt="Al-Ihsan Islamic School"
                    class="h-16 w-16 rounded-xl bg-white/70 p-1.5 shadow-sm"
                />
                <div>
                    <p class="text-xl font-bold leading-tight text-gray-800">Al-Ihsan Islamic School</p>
                    <p class="text-sm text-gray-600">Panel Admin CMS</p>
                </div>
            </div>

            <div
                class="relative"
                x-data="{
                    quotes: [
                        { text: 'Pendidikan adalah senjata paling ampuh untuk mengubah dunia.', by: 'Nelson Mandela' },
                        { text: 'Ing ngarso sung tuladha, ing madya mangun karsa, tut wuri handayani.', by: 'Ki Hajar Dewantara' },
                        { text: 'Pendidikan bukan sekadar persiapan untuk hidup — pendidikan adalah hidup itu sendiri.', by: 'John Dewey' },
                        { text: 'Barang siapa menempuh jalan untuk menuntut ilmu, Allah akan mudahkan baginya jalan menuju surga.', by: 'Hadits Riwayat Muslim' },
                        { text: 'Belajar di waktu kecil bagai mengukir di atas batu.', by: 'Peribahasa' },
                    ],
                    i: 0,
                    init() {
                        setInterval(() => { this.i = (this.i + 1) % this.quotes.length }, 6000)
                    },
                }"
            >
                <template x-for="(quote, index) in quotes" :key="index">
                    <blockquote
                        x-show="i === index"
                        x-transition:enter="transition ease-out duration-700"
                        x-transition:enter-start="opacity-0 translate-y-2"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-cloak
                        class="max-w-md"
                    >
                        <p class="text-2xl font-semibold leading-snug text-gray-800" x-text="'“' + quote.text + '”'"></p>
                        <footer class="mt-4 text-sm font-medium text-gray-600" x-text="'— ' + quote.by"></footer>
                    </blockquote>
                </template>
            </div>

            <p class="relative text-xs text-gray-500">&copy; {{ date('Y') }} Al-Ihsan Islamic School</p>
        </div>

        {{-- Login form --}}
        <div class="flex w-full flex-col items-center justify-center bg-white px-6 py-12 lg:w-1/2">
            <div class="w-full max-w-sm">
                <div class="mb-8 flex flex-col items-center text-center lg:items-start lg:text-left">
                    <img
                        src="{{ asset('images/logo.png') }}"
                        alt="Al-Ihsan Islamic School"
                        class="mb-4 h-14 w-14 lg:hidden"
                    />
                    <h1 class="text-2xl font-bold text-gray-950">{{ $this->getHeading() }}</h1>
                    <p class="mt-1 text-sm text-gray-500">Masuk untuk mengelola konten website sekolah.</p>
                </div>

                {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::AUTH_LOGIN_FORM_BEFORE, scopes: $this->getRenderHookScopes()) }}

                <x-filament-panels::form id="form" wire:submit="authenticate">
                    {{ $this->form }}

                    <x-filament-panels::form.actions
                        :actions="$this->getCachedFormActions()"
                        :full-width="$this->hasFullWidthFormActions()"
                    />
                </x-filament-panels::form>

                {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::AUTH_LOGIN_FORM_AFTER, scopes: $this->getRenderHookScopes()) }}
            </div>
        </div>

        <x-filament-actions::modals />
    </div>

