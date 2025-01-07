<?php

namespace App\Helpers;

use Illuminate\Support\Str;
use Illuminate\Support\Collection;

class FilterProvider
{

    public static function get(array $data, array $filters)
    {
        $data = collect($data);
        foreach ($filters as $filter) {
            if (is_null($filter['value']) || (is_array($filter['value']) && !count($filter['value']))) {
                continue;
            }
            $data = static::resolveQuery($filter['operator'], $filter['column'], $filter['value'], $data);
        }
        if (request()->has('search') && !is_null(request()->search)) {
            $data = $data->filter(function ($item) {
                return collect($item)->contains(key: function ($value) {
                    return !is_array($value) ? strpos(strtolower($value), strtolower(request()->search)) !== false : false;
                });
            });
        }
        return $data->values();
    }

    public static function paginate(Collection $items)
    {
        $order = request()->input('order', 'asc');
        $orderBy = 'id';
        if (count($items) && is_array($items[0]) && count(array_keys($items[0]))) {
            $orderBy = array_keys($items[0])[0];
        }
        $orderBy = request()->input('orderBy', $orderBy);

        $items = $order == 'asc' ? $items->sortBy($orderBy) : $items->sortByDesc($orderBy);
        $perPage =  request()->input('perPage', 10);
        $currentPage = request()->input('page', 1);
        $temp = $items->slice(($currentPage - 1) * $perPage, $perPage)->values();

        return [
            "data" => $temp,
            'current_page' => $currentPage,
            'per_page' => $perPage,
            'total' => $items->count(),
        ];
    }

    public static function process($filters, $value, $addCount = false)
    {
        foreach ($filters as $filterKey => $items) {
            if (!array_key_exists($filterKey, $value) || !$value[$filterKey]) {
                continue;
            }

            if (array_key_exists($value[$filterKey], $items) && $addCount) {
                $filters[$filterKey][$value[$filterKey]]++;
                continue;
            } else {
                $filters[$filterKey][$value[$filterKey]] = 0;
            }
        }

        return $filters;
    }

    private static function resolveQuery($operator,  $column, $value, $data)
    {
        switch ($operator) {
            case "contains":
                return $data->filter(function ($item) use ($column, $value) {
                    return Str::contains($item[$column], $value);
                });
            case "in":
                return $data->whereIn($column, $value);
            case "not_in":
                return $data->whereNotIn($column, $value);
            case "between":
                return $data->whereBetween($column, $value);
            case "greater_than_or_equal_to":
                return $data->filter(function ($item) use ($column, $value) {
                    return $item[$column] >= $value;
                });
            case "lowest-cost":
                // CONNECTIVITY SPECIFIC FILTER
                $keys = [];
                $temp = collect($data->reduce(function ($carry, array $item) use (&$keys) {
                    $key = Str::slug(
                        $item['productName'] .
                            $item['providerName'] .
                            $item['connectivitySupplierGroup'] .
                            $item['bearer'] .
                            $item['speed'] .
                            $item['priceInstall'] .
                            $item['priceECC'] .
                            $item['contractTermMonths']
                    );
                    if (in_array($key, $keys)) {
                        if ($item['priceMonthly'] < $carry[$key]['priceMonthly']) {
                            $carry[$key] = $item;
                        }
                        return $carry;
                    }
                    array_push($keys, $key);
                    $carry[$key] = $item;
                    return $carry;
                }, []));
                return $temp->values();
            default:
                return $data;
        }
    }
}
