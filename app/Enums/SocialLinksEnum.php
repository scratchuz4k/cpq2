<?php

namespace App\Enums;

enum SocialLinksEnum: string
{
    case FACEBOOK = 'Facebook';
    case LINKEDIN = 'LinkedIn';
    case GOOGLE = 'Google';
    case YOUTUBE = 'Youtube';
    case X = 'X';
    case VIMEO = 'Vimeo';

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
            self::FACEBOOK => 'fa-facebook',
            self::LINKEDIN => 'fa-linkedin',
            self::GOOGLE => 'fa-google',
            self::YOUTUBE => 'fa-youtube',
            self::X => 'fa-square-x-twitter',
            self::VIMEO => 'fa-vimeo',
        };
    }
}
