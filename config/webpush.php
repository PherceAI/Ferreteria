<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Web Push Subscriptions Table
    |--------------------------------------------------------------------------
    | La tabla de suscripciones vive en el schema public (sistema).
    */
    'db_table' => 'push_subscriptions',

    'auth_guard' => null,

    /*
    |--------------------------------------------------------------------------
    | Filter Reachable Subscriptions
    |--------------------------------------------------------------------------
    | Si true, el canal filtra las suscripciones cuyo endpoint ya no responde.
    | Útil en producción para limpiar suscripciones expiradas automáticamente.
    */
    'filter_reachable' => true,

    /*
    |--------------------------------------------------------------------------
    | VAPID Keys
    |--------------------------------------------------------------------------
    | Generar con: php artisan webpush:vapid
    | Las claves se guardan en .env como VAPID_PUBLIC_KEY y VAPID_PRIVATE_KEY.
    |
    | VAPID_SUBJECT: identifica al remitente (URL o mailto).
    */
    'vapid' => [
        'subject' => env('VAPID_SUBJECT', 'mailto:'.env('MAIL_FROM_ADDRESS', 'admin@example.com')),
        'public_key' => env('VAPID_PUBLIC_KEY', ''),
        'private_key' => env('VAPID_PRIVATE_KEY', ''),
        'pem_file' => '',
    ],

];
