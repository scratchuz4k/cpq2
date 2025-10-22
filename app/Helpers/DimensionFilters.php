<?php

namespace App\Helpers;

use App\Models\ShopBlocks\Dimension;
use App\Models\ShopBlocks\DimensionType;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class DimensionFilters
{
    public static function build($query)
    {
        $dimensions = json_decode(request()->input('dimensions', '[]'), true);

        foreach ($dimensions as $dim) {
            $dimType = DimensionType::where('key', $dim['column'])->first();
            if (! $dimType) {
                continue;
            }
            $dims = [];
            if ($dim['operator'] == 'between') {
                $array = is_string($dim['value']) ? json_decode($dim['value'], true) : $dim['value'];
                if (count($array) != 2) {
                    continue;
                }
                $queryParts1 = self::resolveQueryParts('greater_than_or_equal_to', $array[0]);
                $queryParts2 = self::resolveQueryParts('less_than_or_equal_to', $array[1]);
                $dims = Dimension::where('dimension_type_id', $dimType->id)
                    ->where('value', $queryParts1['operator'], $queryParts1['value'])
                    ->where('value', $queryParts2['operator'], $queryParts2['value'])
                    ->get()->pluck('id');
            } else {
                $queryParts = self::resolveQueryParts($dim['operator'], $dim['value']);
                $dims = Dimension::where('dimension_type_id', $dimType->id)
                    ->where('value', $queryParts['operator'], $queryParts['value'])->get()->pluck('id');
            }

            $query = $query->whereHas('dimensions', function ($query) use ($dims) {
                return $query->whereIn('dimension_id', $dims);
            });
        }

        return $query;
    }

    protected static function resolveQueryParts($operator, $value)
    {
        return Arr::get([
            'not_equals' => [
                'operator' => '!=',
                'value' => $value,
            ],
            'equals' => [
                'operator' => '=',
                'value' => $value,
            ],
            'not_contains' => [
                'operator' => 'NOT LIKE',
                'value' => "%{$value}%",
            ],
            'contains' => [
                'operator' => 'LIKE',
                'value' => "%{$value}%",
            ],
            'starts_with' => [
                'operator' => 'LIKE',
                'value' => "{$value}%",
            ],
            'ends_with' => [
                'operator' => 'LIKE',
                'value' => "%{$value}",
            ],
            'greater_than' => [
                'operator' => '>',
                'value' => $value,
            ],
            'less_than' => [
                'operator' => '<',
                'value' => $value,
            ],
            'greater_than_or_equal_to' => [
                'operator' => '>=',
                'value' => $value,
            ],
            'less_than_or_equal_to' => [
                'operator' => '<=',
                'value' => $value,
            ],
            'is_null' => [
                'operator' => 'is_null',
                'value' => '',
            ],
            'in' => [
                'operator' => 'in',
                'value' => explode(',', $value),
            ],
            'not_in' => [
                'operator' => 'not_in',
                'value' => explode(',', $value),
            ],
            'search' => [
                'operator' => 'search',
                'value' => $value,
            ],
        ], Str::snake($operator));
    }
}
