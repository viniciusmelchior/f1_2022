<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Administrator emails
    |--------------------------------------------------------------------------
    |
    | Comma-separated list in F1_ADMIN_EMAILS. Entries are trimmed and
    | lower-cased (ASCII only: a lookalike such as the Kelvin sign U+212A must
    | not fold into a plain letter); an empty list means nobody is an
    | administrator. Read through config() only: env() returns null once the
    | configuration is cached.
    |
    | List only emails of accounts that already exist. Registration is open and
    | emails are not verified, so a listed email without an account would make
    | whoever registers it first an administrator.
    |
    */

    'emails' => array_values(array_unique(array_filter(array_map(
        static fn (string $email): string => strtolower(trim($email)),
        explode(',', (string) env('F1_ADMIN_EMAILS', ''))
    ), static fn (string $email): bool => $email !== ''))),

];
