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
    // current school year label used by various features (set manually)
    'current_school_year' => env('SSC_CURRENT_SCHOOL_YEAR', null),
    // default enrollment fee amount
    'enrollment_fee_amount' => env('SSC_ENROLLMENT_FEE', 50),
    // GCash number to display in student payment instructions
    'gcash_number' => env('SSC_GCASH_NUMBER', ''),
];
