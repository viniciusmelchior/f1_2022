<?php

namespace App\Support;

final class ImageUrl
{
    public const PLACEHOLDER = "data:image/svg+xml,%3Csvg xmlns=%27http://www.w3.org/2000/svg%27 viewBox=%270 0 24 24%27%3E%3Crect width=%2724%27 height=%2724%27 rx=%274%27 fill=%27%23d1d5db%27/%3E%3C/svg%3E";

    public static function for(?string $imagem): string
    {
        return filled($imagem) ? asset('images/'.$imagem) : self::PLACEHOLDER;
    }
}
