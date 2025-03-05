<?php

namespace App\Enums;

enum ComparisonOperatorsEnum: string
{
    case EQUAL = '=';
    case GREATER_THAN = '>';
    case LESS_THAN = '<';
    case GREATER_THAN_OR_EQUAL_TO = '>=';
    case LESS_THAN_OR_EQUAL_TO = '<=';

    public function description(): string {
        return match($this) {
            self::EQUAL => 'Equals',
            self::GREATER_THAN => 'Greater than',
            self::LESS_THAN => 'Lesser than',
            self::GREATER_THAN_OR_EQUAL_TO => 'Greater than or equals to',
            self::LESS_THAN_OR_EQUAL_TO => 'Lesser than or equals to',
        };
    }
    public static function asArray(): array
    {
        return array_map(fn($case) => [
            'name' => $case->name,
            'value' => $case->value,
            'description' => $case->description()
        ], self::cases());
    }
}
