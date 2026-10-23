<?php

declare(strict_types=1);

namespace App\Coordinator\Admin\Forms;

use Closure;

/** Validator factory: V::required(), V::length(1, 50), V::email() ... */
final class V
{
    public static function required(string $message = 'This field is required.'): Required
    {
        return new Required($message);
    }

    public static function length(?int $min = null, ?int $max = null, ?string $message = null): Closure
    {
        return static function (mixed $value) use ($min, $max, $message): ?string {
            $len = mb_strlen((string) $value);
            if ($min !== null && $len < $min) {
                return $message ?? "Must be at least $min characters.";
            }
            if ($max !== null && $len > $max) {
                return $message ?? "Must be at most $max characters.";
            }
            return null;
        };
    }

    public static function email(string $message = 'Invalid email format.'): Closure
    {
        return static fn(mixed $value): ?string =>
            filter_var((string) $value, FILTER_VALIDATE_EMAIL) === false ? $message : null;
    }

    public static function regex(string $pattern, string $message = 'Invalid format.'): Closure
    {
        return static fn(mixed $value): ?string =>
            preg_match($pattern, (string) $value) === 1 ? null : $message;
    }

    public static function range(?int $min = null, ?int $max = null, ?string $message = null): Closure
    {
        return static function (mixed $value) use ($min, $max, $message): ?string {
            if (($min !== null && $value < $min) || ($max !== null && $value > $max)) {
                return $message ?? 'Number out of range.';
            }
            return null;
        };
    }

    /** @param array<int|string,mixed> $allowed */
    public static function oneOf(array $allowed, string $message = 'Not a valid choice.'): Closure
    {
        return static fn(mixed $value): ?string =>
            in_array((string) $value, array_map('strval', $allowed), true) ? null : $message;
    }
}
