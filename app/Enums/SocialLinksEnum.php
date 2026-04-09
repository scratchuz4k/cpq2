<?php

namespace App\Enums;

enum SocialLinksEnum: string
{
    case FB = 'Facebook';
    case IN = 'LinkedIn';
    case GO = 'Google';
    case YT = 'Youtube';
    case X = 'X';
    case VI = 'Vimeo';

    public static function asArray(): array
    {
        return array_map(fn ($case) => [
            'name' => $case->name,
            'value' => $case->value,
            'icon' => $case->icons(),
        ], self::cases());
    }

    public function icons(): string
    {
        return match ($this) {
            self::FB => 'fa-facebook',
            self::IN => 'fa-linkedin',
            self::GO => 'fa-google',
            self::YT => 'fa-youtube',
            self::X => 'fa-square-x-twitter',
            self::VI => 'fa-vimeo',
        };
    }
}
