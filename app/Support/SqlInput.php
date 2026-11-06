<?php

namespace App\Support;

/**
 * Strict readers for request values that reach a query. Anything that is not exactly what the
 * query expects is refused with 422, so a payload never gets as far as the database.
 */
final class SqlInput
{
    /** A non-negative integer, as an int or a string of digits only. */
    public static function id(mixed $value): int
    {
        if (is_int($value) && $value >= 0) {
            return $value;
        }

        if (is_string($value) && strlen($value) <= 18 && ctype_digit($value)) {
            return (int) $value;
        }

        abort(422, 'Parâmetro inválido.');
    }

    /** Same as id(), but null and the empty string mean "not given". */
    public static function optionalId(mixed $value): ?int
    {
        return $value === null || $value === '' ? null : self::id($value);
    }

    /** One of a fixed list of values (column names, order keywords, flags). */
    public static function oneOf(mixed $value, array $allowed): string
    {
        if (is_string($value) && in_array($value, $allowed, true)) {
            return $value;
        }

        abort(422, 'Parâmetro inválido.');
    }
}
