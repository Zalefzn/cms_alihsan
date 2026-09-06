<?php

namespace App\Providers;

use App\Models\NewsletterSubscriber;
use App\Models\Page;
use App\Models\Registration;
use App\Observers\NewsletterSubscriberObserver;
use App\Observers\PageObserver;
use App\Observers\RegistrationObserver;
use Filament\Actions\Action;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Page::observe(PageObserver::class);
        Registration::observe(RegistrationObserver::class);
        NewsletterSubscriber::observe(NewsletterSubscriberObserver::class);

        $this->registerDefaultActionIcons();
    }

    /**
     * Filament's own Save/Cancel/Create form actions ship with no
     * icon at all (unlike EditAction/DeleteAction, which already have
     * a pencil/trash icon out of the box) — set sensible defaults for
     * them here, once, instead of repeating ->icon() on every
     * resource's every action. Anything that already has an icon
     * (either from Filament itself, like Delete, or set explicitly in
     * a resource) is left alone.
     */
    private function registerDefaultActionIcons(): void
    {
        Action::configureUsing(function (Action $action): void {
            if ($action->getIcon()) {
                return;
            }

            $icon = match ($action->getName()) {
                'save', 'create', 'attach', 'associate' => 'heroicon-o-check',
                'createAnother' => 'heroicon-o-plus',
                'cancel' => 'heroicon-o-x-mark',
                default => null,
            };

            if ($icon) {
                $action->icon($icon);
            }
        });

        \Filament\Tables\Actions\CreateAction::configureUsing(
            fn (\Filament\Tables\Actions\CreateAction $action) => $action->icon($action->getIcon() ?? 'heroicon-o-plus'),
        );

        \Filament\Actions\CreateAction::configureUsing(
            fn (\Filament\Actions\CreateAction $action) => $action->icon($action->getIcon() ?? 'heroicon-o-plus'),
        );
    }
}
