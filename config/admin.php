<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Administrator emails
    |--------------------------------------------------------------------------
    |
    | Comma-separated list in F1_ADMIN_EMAILS. Entries are trimmed and
    | lower-cased; an empty list means nobody is an administrator. Read through
    | config() only: env() returns null once the configuration is cached.
    |
    */

    'emails' => array_values(array_unique(array_filter(array_map(
        static fn (string $email): string => mb_strtolower(trim($email)),
        explode(',', (string) env('F1_ADMIN_EMAILS', ''))
    ), static fn (string $email): bool => $email !== ''))),

];
