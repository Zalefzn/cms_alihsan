{{--
    Structural layout below uses plain classes defined in
    public/css/admin-theme.css (see .auth-* rules) instead of ad-hoc
    Tailwind utilities — Filament ships a pre-compiled, pre-purged CSS
    bundle built from its OWN views, so Tailwind classes that only
    appear in this custom page (like a raw `w-1/2` or `lg:flex`) are
    silently missing from it and have no effect. Small/common
    utilities that already exist in Filament's bundle (text sizing,
    weights, colors) are still used directly.
--}}
<div class="auth-split">
    <a
        href="{{ route('panduan') }}"
        target="_blank"
        rel="noopener"
        class="auth-guide-link"
        title="Panduan Penggunaan CMS"
    >
        <x-filament::icon icon="heroicon-o-cog-6-tooth" class="auth-guide-icon" />
    </a>

    {{-- Decorative left panel — hidden on small screens --}}
    <div class="auth-split-left">
        <div class="auth-blob auth-blob-1"></div>
        <div class="auth-blob auth-blob-2"></div>

        <div class="auth-brand">
            <img
                src="{{ asset('images/logo.png') }}"
                alt="Al-Ihsan Islamic School"
                class="auth-brand-logo"
            />
            <div>
                <p class="auth-brand-name">Al-Ihsan Islamic School</p>
                <p class="auth-brand-sub">Panel Admin CMS</p>
            </div>
        </div>

        <div
            class="auth-quote-ctn"
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
                    class="auth-quote"
                >
                    <p class="auth-quote-text" x-text="'“' + quote.text + '”'"></p>
                    <footer class="auth-quote-by" x-text="'— ' + quote.by"></footer>
                </blockquote>
            </template>
        </div>

        <p class="auth-copyright">&copy; {{ date('Y') }} Al-Ihsan Islamic School</p>
    </div>

    {{-- Login form --}}
    <div class="auth-split-right">
        <div class="auth-form-ctn">
            <div class="auth-form-header">
                <img
                    src="{{ asset('images/logo.png') }}"
                    alt="Al-Ihsan Islamic School"
                    class="auth-form-logo"
                />
                <h1 class="auth-form-heading">{{ $this->getHeading() }}</h1>
                <p class="auth-form-subheading">Masuk untuk mengelola konten website sekolah.</p>
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
