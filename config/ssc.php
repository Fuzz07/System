<?php

return [
    /*
    |--------------------------------------------------------------------------
    | SSC Configuration
    |--------------------------------------------------------------------------
    |
    | Place settings related to SSC business rules here.
    |
    */

    // Year level values that should be considered "graduated" and therefore
    // automatically set to inactive when detected.
    'graduated_levels' => [
        'Graduated',
        'Alumni',
    ],

    // Enable automatic deactivation of graduated students during login.
    'auto_deactivate_graduates' => true,
];
