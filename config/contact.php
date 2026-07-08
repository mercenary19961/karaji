<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Operator contact (shop onboarding)
    |--------------------------------------------------------------------------
    |
    | Self-signup is disabled — shops are created by an admin. The login page
    | shows a "contact us" WhatsApp link so a shop owner can request an
    | account. Set the real number in CONTACT_WHATSAPP (wa.me format, digits
    | only, e.g. 9627XXXXXXXX). Placeholder until the operator number is set.
    |
    */

    'whatsapp' => env('CONTACT_WHATSAPP', '962790000000'),
];
