/**
 * Bridges Filament's own notification/logout UI to SweetAlert2:
 *
 * 1. Toasts — Filament renders each flashed notification with stable
 *    public classes (`.fi-no-notification`, `.fi-no-notification-title`,
 *    `.fi-status-{success,danger,warning,info}`); we read those
 *    straight from the rendered DOM and re-show them as a SweetAlert2
 *    toast, then hide the native one so it isn't shown twice. (Its
 *    Alpine component keeps `notification` as a closure variable, not
 *    an exposed reactive property, so `Alpine.$data()` can't see it —
 *    this is why we read the rendered markup instead.)
 * 2. Logout — any `Keluar` submit button inside a `form[action*=logout]`
 *    asks for confirmation first.
 *
 * (Delete-action confirmations were deliberately left as Filament's
 * own native modal: swapping them for SweetAlert requires pausing the
 * click, confirming, then re-firing Filament's `mountTableAction` call
 * — but that re-fire unreliably re-triggered Filament's own
 * confirmation modal on top, giving a broken double-confirmation UX.
 * Not worth the risk on destructive actions, so those keep the
 * already-correct native dialog.)
 */
(function () {
    const STATUS_ICON = { success: 'success', danger: 'error', warning: 'warning', info: 'info' };

    function swalTheme() {
        return {
            confirmButtonColor: '#4f46e5',
            cancelButtonColor: '#9ca3af',
        };
    }

    function bridgeNotifications() {
        const seen = new WeakSet();

        const handle = (el) => {
            if (seen.has(el)) return;
            seen.add(el);

            const title = el.querySelector('.fi-no-notification-title')?.textContent?.trim() || '';
            const body = el.querySelector('.fi-no-notification-body')?.textContent?.trim() || '';
            if (!title && !body) return;

            const status = ['success', 'danger', 'warning', 'info'].find((s) => el.classList.contains(`fi-status-${s}`));

            // Give Alpine a tick to finish mounting/showing the native
            // notification before we hide it, so its own open/close
            // lifecycle (which other code may rely on) still runs.
            requestAnimationFrame(() => { el.style.display = 'none'; });

            window.Swal.fire({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 4500,
                timerProgressBar: true,
                icon: STATUS_ICON[status] || 'info',
                title,
                text: body || undefined,
            });
        };

        document.querySelectorAll('.fi-no-notification').forEach(handle);

        new MutationObserver((mutations) => {
            for (const mutation of mutations) {
                mutation.addedNodes.forEach((node) => {
                    if (node.nodeType !== 1) return;
                    if (node.matches?.('.fi-no-notification')) handle(node);
                    node.querySelectorAll?.('.fi-no-notification').forEach(handle);
                });
            }
        }).observe(document.body, { childList: true, subtree: true });
    }

    function bridgeLogout() {
        document.addEventListener('click', function (event) {
            const button = event.target.closest('form[action*="logout"] button[type="submit"]');
            if (!button) return;
            if (button.dataset.swalConfirmed) {
                delete button.dataset.swalConfirmed;
                return;
            }

            event.preventDefault();
            event.stopImmediatePropagation();

            window.Swal.fire({
                icon: 'question',
                title: 'Keluar dari akun?',
                text: 'Anda perlu masuk lagi untuk mengelola konten.',
                showCancelButton: true,
                confirmButtonText: 'Ya, keluar',
                cancelButtonText: 'Batal',
                ...swalTheme(),
            }).then((result) => {
                if (!result.isConfirmed) return;
                button.dataset.swalConfirmed = '1';
                button.click();
            });
        }, true);
    }

    function init() {
        if (!window.Swal) return;
        bridgeNotifications();
        bridgeLogout();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    // Filament pages are navigated between via Livewire's SPA-style
    // navigation, which doesn't reload this script — but the observer
    // and click listener above are set up once and keep working
    // across navigations since they're bound to `document`.
})();
