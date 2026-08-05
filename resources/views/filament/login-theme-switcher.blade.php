{{--
    Filament's light / dark / system switcher, which normally only
    exists inside the signed-in user menu, surfaced under the login
    card. Its buttons call close() to dismiss that menu, so an empty
    close() is supplied through the Alpine scope they inherit.
--}}
<div x-data="{ close: () => {} }" class="fi-login-theme-switcher">
    <x-filament-panels::theme-switcher />
</div>
