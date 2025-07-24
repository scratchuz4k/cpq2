<?php

namespace App\Enums;

use App\Trait\EnumHelpers;

enum ComparisonOperatorsEnum: string
{
    use EnumHelpers;

    case equals = '=';
    case greater_than = '>';
    case less_than = '<';
    case greater_than_or_equal_to = '>=';
    case less_than_or_equal_to = '<=';

    case between = '>=|<=';

    public function description(): string
    {
        return match ($this) {
            self::equals => 'Equals',
            self::greater_than => 'Greater than',
            self::less_than => 'Lesser than',
            self::greater_than_or_equal_to => 'Greater than or equals to',
            self::less_than_or_equal_to => 'Lesser than or equals to',
            self::between => 'Between',
        };
    }

    public static function asArray(): array
    {
        return array_map(fn ($case) => [
            'name' => $case->name,
            'value' => $case->value,
            'description' => $case->description(),
        ], self::cases());
    }
}
