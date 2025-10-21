<?php

namespace App\Trait;

trait EnumHelpers
{
    public static function tryFromName(string $name): ?self
    {
        foreach (self::cases() as $case) {
            if ($case->name === $name) {
                return $case;
            }
        }

        return null;
    }

    public static function randomName()
    {
        $key = array_rand(self::cases());

        return self::cases()[$key]->name;
    }

    public static function randomValue()
    {
        $key = array_rand(self::cases());

        return self::cases()[$key]->value;
    }
}
