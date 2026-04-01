<?php

namespace App\Helpers;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class FilterProvider
{
    public static function get(array $data, array $filters)
    {
        $data = collect($data);
        foreach ($filters as $filter) {
            if (! isset($filter['value'])) {
                continue;
            }

            if (is_array($filter['value']) && ! count($filter['value'])) {
                continue;
            }
            $data = static::resolveQuery($filter['operator'], $filter['column'], $filter['value'], $data);
        }
        if (request()->has('search') && ! is_null(request()->search)) {
            $data = $data->filter(fn ($item) => collect($item)->contains(key: fn ($value) => ! is_array($value) ? str_contains(strtolower((string) $value), strtolower(request()->search)) : false));
        }

        return $data->values();
    }

    public static function slice(Collection $items): array
    {
        $order = request()->input('order', 'asc');
        $orderBy = 'id';
        if (count($items) && is_array($items[0]) && count(array_keys($items[0]))) {
            $orderBy = array_keys($items[0])[0];
        }
        $orderBy = request()->input('orderBy', $orderBy);

        $items = $order == 'asc' ? $items->sortBy($orderBy) : $items->sortByDesc($orderBy);
        $limit = request()->input('perPage', 10);
        $currentPage = request()->input('page', 1);
        $temp = $items->slice(($currentPage - 1) * $limit, $limit)->values();

        return [
            'items' => $temp,
            'total' => $items->count(),
            'perPage' => $limit,
            'currentPage' => $currentPage,
        ];
    }

    public static function paginate(Collection $items): LengthAwarePaginator
    {
        $res = static::slice($items);

        return new LengthAwarePaginator(...$res);
    }

    public static function process($filters, $value, $addCount = false)
    {
        foreach ($filters as $filterKey => $items) {
            if (! array_key_exists($filterKey, $value)) {
                continue;
            }

            $oldValue = $value[$filterKey];
            $newValue = is_null($oldValue) ? 'null' : (is_bool($oldValue) ? ($oldValue ? 'true' : 'false') : (string) $oldValue);

            if (array_key_exists($newValue, $items) && $addCount) {
                $filters[$filterKey][$newValue]++;

                continue;
            } else {
                $filters[$filterKey][$newValue] = 0;
            }
        }

        return $filters;
    }

    private static function resolveQuery($operator, $column, $value, $data)
    {
        switch ($operator) {
            case 'equals':
                return $data->where($column, $value);
            case 'contains':
                return $data->filter(fn ($item) => Str::contains($item[$column], $value));
            case 'in':
                $value = is_array($value) ? $value : [$value];
                $normalizedSearch = array_map(function ($v) {
                    return static::normalizeValue($v);
                }, $value);

                return $data->filter(function ($item) use ($column, $normalizedSearch) {
                    $val = is_array($item) ? $item[$column] : $item->{$column};
                    $normalizedColumn = static::normalizeValue($val);

                    return in_array($normalizedColumn, $normalizedSearch);
                });
            case 'not_in':
                return $data->whereNotIn($column, $value);
            case 'between':
                sort($value);

                return $data->whereBetween($column, $value);
            case 'greater_than':
                return $data->filter(fn ($item) => $item[$column] > $value);
            case 'greater_than_or_equal_to':
                return $data->filter(fn ($item) => $item[$column] >= $value);
            case 'less_than':
                return $data->filter(fn ($item) => $item[$column] < $value);
            case 'less_than_or_equal_to':
                return $data->filter(fn ($item) => $item[$column] <= $value);
            case 'lowest-cost':
                // CONNECTIVITY SPECIFIC FILTER
                $keys = [];
                $temp = collect($data->reduce(function ($carry, array $item) use (&$keys) {
                    $key = Str::slug(
                        $item['productName'].
                            $item['providerName'].
                            $item['connectivitySupplierGroup'].
                            $item['bearer'].
                            $item['speed'].
                            $item['priceInstall'].
                            $item['priceECC'].
                            $item['contractTermMonths'],
                        $item['uploadSpeed'] ?? ''
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

    private static function normalizeValue($val)
    {
        if ($val === 'null') {
            $val = null;
        }
        if (is_bool($val)) {
            $val = $val ? 1 : 0;
        }
        if (is_null($val)) {
            return null;
        }

        return strtolower((string) $val);
    }

    public function getCollection()
    {
        static::process();

        return $this->builder->get();
    }
}
