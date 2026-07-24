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
    // Bank / InstaPay details for admin enrollment payments
    'bank_name' => env('SSC_BANK_NAME', 'Landbank of the Philippines'),
    'bank_account_name' => env('SSC_BANK_ACCOUNT_NAME', 'MCC Supreme Student Council'),
    'bank_account_number' => env('SSC_BANK_ACCOUNT_NUMBER', '1234-5678-90'),
];
