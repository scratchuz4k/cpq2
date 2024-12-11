<?php

namespace App\Helpers;

use App\Models\Order;
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

    public function with(array $items)
    {
        array_push($this->relations, ...$items);

        return $this;
    }

    public function get()
    {
        $this->builder = $this->builder->with($this->relations);

        $search = request()->input('search', null);
        $filters = request()->input('filters', []);
        $filters = is_string($filters) ? json_decode($filters, true) : $filters;

        if ($search) {
            $columns = request()->get('columns') ? explode(",", request()->get('columns')) : $this->builder->getModel()->getFillable();
            $hidden = $this->builder->getModel()->getHidden();

            $columns = array_filter($columns, function ($column) use ($hidden) {
                $firstPart = explode('.', $column)[0];
                return !in_array($firstPart, $hidden);
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

        $page = request()->input("page", 1);
        $limit = request()->input("limit", 10);
        $order = request()->input("order", "asc");
        $orderBy = request()->input("orderBy", "id");

        $orderByArray = explode('.', $orderBy);
        if (count($orderByArray) == 1) {
            return $this->builder->orderBy($orderBy, $order)->paginate(perPage: $limit, page: $page);
        }
        if (count($orderByArray) == 2) {
            $table = $this->builder->getModel()->getTable();
                $this->builder = $this->builder->select($table . '.*')->leftJoinRelation(Str::camel($orderByArray[0]) . ' as ' . $orderByArray[0])
                    ->orderBy($orderByArray[0] . '.' . $orderByArray[1], $order);
            return  $this->builder->paginate(perPage: $limit, page: $page);
        }
        return $this->builder->paginate(perPage: $limit, page: $page);
    }

    public function buildSearch($builder, $column, $operator, $value, $func)
    {
        $relations = explode('.', $column);
        $column = array_pop($relations);

        if (count($relations) == 0) {
            return $this->query($builder, $column, $operator, $value, $func);
        } else {
            return $this->queryRelated(
                $builder,
                Str::camel(implode('.', $relations)),
                $column,
                $operator,
                $value,
                $func,
            );
        }
    }

    protected function query($query, $column, $operator, $value, $func)
    {
        $column = $query->getModel()->getTable() . '.' . $column;

        if ($this->model->translatable && in_array($column, $this->model->translatable)) {
            $column = $column . '->' . app()->getLocale();
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

    protected function queryRelated($query, $relation, $column, $operator, $value, $func)
    {
        $temp = $func == 'orWhere' ? 'orWhereHas' : 'whereHas';
        return $query->{$temp}($relation, function ($query) use ($column, $operator, $value) {
            $relatedTableName = $query->getModel()->getTable();

            if ($query->getModel()->translatable && in_array($column, $query->getModel()->translatable)) {
                $column = $column . '->' . app()->getLocale();
            }

            switch ($operator) {
                case 'in':
                    return $query->whereIn($relatedTableName . '.' . $column, $value);
                case 'not_in':
                    return $query->whereNotIn($relatedTableName . '.' . $column, $value);
                case 'is_null':
                    return $query->whereNull($relatedTableName . '.' . $column);
                default:
                    return $query->where($relatedTableName . '.' . $column, $operator, $value);
            }
        });
    }

    protected function searchModel($query, $columns = [], $searchTerm)
    {
        foreach ($columns as $key => $column) {
            $queryParts = $this->resolveQueryParts('contains', $searchTerm);
            $query = $this->buildSearch($query, $column, $queryParts['operator'], $queryParts['value'], !$key ? 'where' :  'orWhere');
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
