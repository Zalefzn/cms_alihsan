<?php

namespace App\Providers\Filament;

use App\Filament\Resources\PageResource;
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
            ->login()
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn (): string => '<link rel="stylesheet" href="'.e(asset('css/admin-theme.css')).'">',
            )
            ->colors([
                'primary' => Color::Indigo,
            ])
            ->sidebarCollapsibleOnDesktop()
            ->navigationGroups([
                NavigationGroup::make('Halaman')
                    ->icon('heroicon-o-document-text'),
                NavigationGroup::make('Navigasi Website')
                    ->icon('heroicon-o-bars-3'),
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
                ->icon('heroicon-o-document-text')
                ->group('Halaman')
                ->sort($index + 1)
                ->url(fn () => PageResource::getUrl('edit', ['record' => $page]))
                ->isActiveWhen(fn () => request()->route('record') == $page->getKey()
                    && request()->routeIs('filament.admin.resources.pages.edit')))
            ->all();

        return [$listItem, ...$pageItems];
    }
}
