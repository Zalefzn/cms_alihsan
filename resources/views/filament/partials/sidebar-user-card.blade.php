{{--
    Fixed card at the bottom of the sidebar (see PanelsRenderHook::SIDEBAR_FOOTER in
    AdminPanelProvider): avatar + name + role, a gear icon to the profile/password page,
    and a logout button — replaces the default topbar avatar dropdown (hidden via CSS in
    admin-theme.css, `.fi-topbar .fi-user-menu`), per the admin's request to keep the
    topbar limited to notifications, global search, and the sidebar-collapse toggle.
--}}
@php
    $user = filament()->auth()->user();
@endphp

@if ($user)
    <div class="fi-sidebar-user-card">
        <div class="fi-sidebar-user-card-info">
            <x-filament-panels::avatar.user :user="$user" />

            <div class="fi-sidebar-user-card-text" x-cloak x-show="$store.sidebar.isOpen">
                <p class="fi-sidebar-user-card-name">{{ filament()->getUserName($user) }}</p>
                <p class="fi-sidebar-user-card-role">
                    {{ \Illuminate\Support\Str::headline($user->getRoleNames()->first() ?? '—') }}
                </p>
            </div>

            <a
                href="{{ filament()->getProfileUrl() }}"
                class="fi-sidebar-user-card-settings"
                title="Pengaturan Akun"
                x-cloak
                x-show="$store.sidebar.isOpen"
            >
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
            </a>
        </div>

        <form
            action="{{ filament()->getLogoutUrl() }}"
            method="post"
            x-cloak
            x-show="$store.sidebar.isOpen"
        >
            @csrf
            <button type="submit" class="fi-sidebar-user-card-logout-btn">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l3 3m0 0l-3 3m3-3H2.25" />
                </svg>
                Keluar
            </button>
        </form>
    </div>
@endif
