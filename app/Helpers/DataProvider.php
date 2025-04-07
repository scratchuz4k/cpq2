<?php

namespace App\Helpers;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class DataProvider
{
    public $model;

    public $builder;

    public string $orderBy = 'id';

    public string $order = 'desc';

    protected array $relations = [];

    public function __construct($model)
    {
        $this->model = app($model);
        $this->builder = app($model);
    }

    public function get()
    {
        $this->processFilters();
        $this->processRequest();

        $page = request()->input('page', 1);
        $limit = request()->input('limit', 10);

        return $this->builder->paginate(perPage: $limit, page: $page);
    }

    public function getCollection()
    {
        $this->processFilters();

        return $this->builder->get();
    }

    public function group(string $resource)
    {
        $this->processFilters();
        $this->processRequest();
        $collection = [];

        $page = request()->input('page', 1);
        $limit = request()->input('limit', 10);
        $group = request()->input('group', null);

        $total = $this->builder->get()->count();
        $items = $this->builder->paginate(perPage: $limit, page: $page);

        if (! $group) {
            return $resource::collection($items);
        }

        $group = explode('.', $group);
        if (count($group) == 2) {
            foreach ($group as $key => $item) {
                $group[$key] = Str::camel($item);
            }
        }

        foreach ($items as $item) {
            $groupKey = array_reduce($group, function ($acc, $temp) use ($item) {
                if ($acc == 'default') {
                    $acc = $item;
                }

                return $acc[$temp];
            }, 'default');

            $index = array_search($groupKey, array_column($collection, 'name'));

            if ($index === false) {
                array_push($collection, ['name' => $groupKey, 'items' => [$resource::make($item)]]);
            } else {
                array_push($collection[$index]['items'], $resource::make($item));
            }
        }

        // Combine the data and metadata in your response
        return new LengthAwarePaginator($collection, $total, $limit, $page);
    }

    private function processFilters()
    {
        $search = request()->input('search', null);
        $filters = request()->input('filters', []);
        $filters = is_string($filters) ? json_decode($filters, true) : $filters;

        if ($search) {
            $columns = request()->get('columns') ? explode(',', request()->get('columns')) : $this->builder->getModel()->getFillable();
            $hidden = $this->builder->getModel()->getHidden();

            $columns = array_filter($columns, function ($column) use ($hidden) {
                $firstPart = explode('.', $column)[0];

                return ! in_array($firstPart, $hidden);
            });

            $this->builder = $this->builder->where(function ($query) use ($columns, $search) {
                return $this->searchModel($query, $columns, $search);
            });
        }

        foreach ($filters as $filter) {
            if (is_null($filter['value'])) {
                continue;
            }

            $queryParts = $this->resolveQueryParts($filter['operator'], $filter['value']);
            $func = $this->getFunction($filter);
            $this->builder = $this->buildSearch($this->builder, $filter['column'], $queryParts['operator'], $queryParts['value'], $func);
        }
    }

    private function processRequest()
    {
        $group = request()->input('group', null);
        if ($group) {
            $groupOrder = request()->input('groupOrder', 'asc');
            $groupBy = explode('.', $group);

            if (count($groupBy) == 2) {
                $table = $this->builder->getModel()->getTable();
                $this->builder = $this->builder->select($table.'.*')->joinRelation(Str::camel($groupBy[0]).' as '.$groupBy[0])
                    ->orderBy($groupBy[0].'.'.$groupBy[1], $groupOrder);
            } else {
                $this->builder = $this->builder->orderBy($groupBy[0], $groupOrder);
            }
        }

        $order = request()->input('order', 'asc');
        $orderBy = request()->input('orderBy', 'id');
        $orderByArray = explode('.', $orderBy);

        if (count($orderByArray) == 2) {
            $table = $this->builder->getModel()->getTable();
            $this->builder = $this->builder->select($table.'.*')->joinRelation(Str::camel($orderByArray[0]).' as '.$orderByArray[0])
                ->orderBy($orderByArray[0].'.'.$orderByArray[1], $order);
        } else {
            $this->builder = $this->builder->orderBy($orderBy, $order);
        }
    }

    public function buildSearch($builder, $column, $operator, $value, $func)
    {
        $relations = explode('.', $column);
        $column = array_pop($relations);

        if (count($relations) == 0) {
            return $this->basicQuery($builder, $column, $operator, $value, $func);
        }

        return $this->relatedQuery(
            $builder,
            Str::camel(implode('.', $relations)),
            $column,
            $operator,
            $value,
            $func,
        );
    }

    protected function basicQuery($query, $column, $operator, $value, $func)
    {
        $column = $query->getModel()->getTable().'.'.$column;

        if ($this->model->translatable && in_array($column, $this->model->translatable)) {
            $column = $column.'->'.app()->getLocale();
        }

        switch ($operator) {
            case 'in':
                return $query->whereIn($column, $value);
            case 'not_in':
                return $query->whereNotIn($column, $value);
            case 'is_null':
                return $query->whereNull($column);
            default:
                return $query->{$func}($column, $operator, $value);
        }
    }

    protected function relatedQuery($query, $relation, $column, $operator, $value, $func)
    {
        $temp = $func == 'orWhere' ? 'orWhereHas' : 'whereHas';

        return $query->{$temp}($relation, function ($query) use ($column, $operator, $value) {
            $relatedTableName = $query->getModel()->getTable();

            if ($query->getModel()->translatable && in_array($column, $query->getModel()->translatable)) {
                $column = $column.'->'.app()->getLocale();
            }

            switch ($operator) {
                case 'in':
                    return $query->whereIn($relatedTableName.'.'.$column, $value);
                case 'not_in':
                    return $query->whereNotIn($relatedTableName.'.'.$column, $value);
                case 'is_null':
                    return $query->whereNull($relatedTableName.'.'.$column);
                default:
                    return $query->where($relatedTableName.'.'.$column, $operator, $value);
            }
        });
    }

    protected function searchModel($query, $columns, $searchTerm)
    {
        foreach ($columns as $key => $column) {
            $queryParts = $this->resolveQueryParts('contains', $searchTerm);
            $query = $this->buildSearch($query, $column, $queryParts['operator'], $queryParts['value'], ! $key ? 'where' : 'orWhere');
        }

        return $query;
    }

    protected function getFunction($filter)
    {
        $functions = ['where', 'orWhere', 'whereNull'];
        if (! array_key_exists('function', $filter)) {
            return 'where';
        }

        if (! in_array($filter['function'], $functions)) {
            return 'where';
        }

        if ($filter['operator'] == 'is_null') {
            return 'whereNull';
        }

        return $filter['function'];
    }

    protected function resolveQueryParts($operator, $value)
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
