<?php

namespace App\Helpers;

use App\Models\ShopBlocks\Dimension;
use App\Models\ShopBlocks\DimensionType;
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
                $dimsQuery = Dimension::where('dimension_type_id', $dimType->id);

                if ($queryParts['operator'] === 'in') {
                    $dims = $dimsQuery
                        ->whereIn('value', $queryParts['value'])
                        ->get()
                        ->pluck('id');
                } elseif ($queryParts['operator'] === 'not_in') {
                    $dims = $dimsQuery
                        ->whereNotIn('value', $queryParts['value'])
                        ->get()
                        ->pluck('id');
                } else {
                    $dims = $dimsQuery
                        ->where('value', $queryParts['operator'], $queryParts['value'])
                        ->get()
                        ->pluck('id');
                }
            }

            $query = $query->whereHas('dimensions', function ($query) use ($dims) {
                return $query->whereIn('dimension_id', $dims);
            });
        }

        return $query;
    }

    protected static function resolveQueryParts($operator, $value)
    {
        $op = Str::snake($operator);

        switch ($op) {
            case 'not_equals':
                return [
                    'operator' => '!=',
                    'value' => $value,
                ];
            case 'equals':
                return [
                    'operator' => '=',
                    'value' => $value,
                ];
            case 'not_contains':
                return [
                    'operator' => 'NOT LIKE',
                    'value' => "%{$value}%",
                ];
            case 'contains':
                return [
                    'operator' => 'LIKE',
                    'value' => "%{$value}%",
                ];
            case 'starts_with':
                return [
                    'operator' => 'LIKE',
                    'value' => "{$value}%",
                ];
            case 'ends_with':
                return [
                    'operator' => 'LIKE',
                    'value' => "%{$value}",
                ];
            case 'greater_than':
                return [
                    'operator' => '>',
                    'value' => $value,
                ];
            case 'less_than':
                return [
                    'operator' => '<',
                    'value' => $value,
                ];
            case 'greater_than_or_equal_to':
                return [
                    'operator' => '>=',
                    'value' => $value,
                ];
            case 'less_than_or_equal_to':
                return [
                    'operator' => '<=',
                    'value' => $value,
                ];
            case 'is_null':
                return [
                    'operator' => 'is_null',
                    'value' => '',
                ];
            case 'in':
                return [
                    'operator' => 'in',
                    'value' => $value,
                ];
            case 'not_in':
                return [
                    'operator' => 'not_in',
                    'value' => $value,
                ];
            case 'search':
                return [
                    'operator' => 'search',
                    'value' => $value,
                ];
        }
    }
}
