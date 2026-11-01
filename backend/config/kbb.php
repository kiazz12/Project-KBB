<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Host tujuan redirect formulir
    |--------------------------------------------------------------------------
    |
    | Daftar host yang diizinkan sebagai tujuan `forms.redirect_url`,
    | dipisahkan koma melalui env KBB_ALLOWED_REDIRECT_HOSTS. Host aplikasi
    | (APP_URL) selalu diizinkan. URL relatif (diawali "/") juga diizinkan.
    |
    */
    'allowed_redirect_hosts' => array_values(array_filter(array_map(
        static fn (string $host): string => strtolower(trim($host)),
        explode(',', (string) env('KBB_ALLOWED_REDIRECT_HOSTS', ''))
    ))),
];
