<?php

namespace App\Providers\Filament;

use App\Filament\Resources\PageResource;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use App\Filament\Widgets\BlockTypeChart;
use App\Filament\Widgets\CmsStatsOverview;
use App\Filament\Widgets\RecentPagesWidget;
use App\Models\Page;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->brandName('Al-Ihsan CMS')
            ->brandLogo(asset('images/logo.png'))
            ->brandLogoHeight('2.75rem')
            ->favicon(asset('images/favicon.ico'))
            ->login(\App\Filament\Pages\Auth\Login::class)
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn (): string => '<link rel="stylesheet" href="'.e(asset('css/admin-theme.css')).'">',
            )
            ->renderHook(
                PanelsRenderHook::BODY_END,
                function (): string {
                    $welcomeName = session()->pull('just_logged_in');

                    $welcomeScript = $welcomeName
                        ? '<script>(function(){var fire=function(){if(!window.Swal)return;Swal.fire({toast:true,position:"top-end",icon:"success",title:'.\Illuminate\Support\Js::from('Selamat datang, '.$welcomeName.'!').',showConfirmButton:false,timer:3500,timerProgressBar:true});};document.readyState==="loading"?document.addEventListener("DOMContentLoaded",fire):fire();})();</script>'
                        : '';

                    $bridgeUrl = asset('js/sweetalert-bridge.js').'?v='.filemtime(public_path('js/sweetalert-bridge.js'));

                    return '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>'
                        .'<script src="'.e($bridgeUrl).'" defer></script>'
                        .$welcomeScript;
                },
            )
            ->colors([
                'primary' => Color::Indigo,
            ])
            // Content stays light always; only the sidebar is dark (via
            // admin-theme.css). Without this, a device set to a dark
            // OS/browser preference flips the WHOLE panel to Filament's
            // dark palette, which breaks contrast on our custom pages
            // (e.g. white-on-white labels on the login screen).
            ->darkMode(false)
            ->databaseNotifications()
            ->databaseNotificationsPolling('30s')
            ->sidebarCollapsibleOnDesktop()
            ->navigationGroups([
                NavigationGroup::make('Halaman'),
                NavigationGroup::make('Navigasi Website'),
            ])
            ->navigationItems($this->pageNavigationItems())
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                CmsStatsOverview::class,
                BlockTypeChart::class,
                RecentPagesWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->plugins([
                FilamentShieldPlugin::make(),
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }

    /**
     * Every page gets its own item directly in the "Halaman" sidebar
     * group (instead of only a single link to a table), so an admin
     * can jump straight to editing one page's content. PageResource's
     * own navigation registration is disabled to avoid a duplicate —
     * see PageResource::$shouldRegisterNavigation.
     *
     * @return array<NavigationItem>
     */
    protected function pageNavigationItems(): array
    {
        $listItem = NavigationItem::make('Semua Halaman')
            ->icon('heroicon-o-queue-list')
            ->group('Halaman')
            ->sort(0)
            ->url(fn () => PageResource::getUrl('index'))
            ->isActiveWhen(fn () => request()->routeIs('filament.admin.resources.pages.index'));

        $pageItems = Page::query()
            ->orderBy('title')
            ->get()
            ->map(fn (Page $page, int $index) => NavigationItem::make($page->title)
                ->icon($page->icon ?? 'heroicon-o-document-text')
                ->group('Halaman')
                ->sort($index + 1)
                ->url(fn () => PageResource::getUrl('edit', ['record' => $page]))
                ->isActiveWhen(fn () => request()->route('record') == $page->getKey()
                    && request()->routeIs('filament.admin.resources.pages.edit')))
            ->all();

        return [$listItem, ...$pageItems];
    }
}
