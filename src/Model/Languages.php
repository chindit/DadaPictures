<?php

namespace App\Model;

class Languages
{
    public const FR = 'fr';
    public const EN = 'en';

    /**
     * @return string[]
     */
    public static function all(): array
    {
        return [self::EN, self::FR];
    }
}
