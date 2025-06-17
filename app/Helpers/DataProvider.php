<?php

namespace App\Helpers;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use ReflectionClass;

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
            $columns = request()->get(key: 'columns') ? explode(',', request()->get('columns')) : $this->builder->getModel()->getFillable();
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
            $this->builder = $this->buildSearch(
                $this->builder,
                $filter['column'],
                $queryParts['operator'],
                $queryParts['value'],
                $this->getFunction($filter)
            );
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

        $relations = explode('.', $orderBy);
        if (count($relations) > 1 && ! $this->checkModelRelationships($relations[0])) {
            return;
        }

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
        $relation = Str::camel($relations[0]);
        if (! $this->checkModelRelationships($relation)) {
            return $this->basicQuery($builder, $column, $operator, $value, $func);
        }

        $relations = explode('.', string: $column);
        $column = array_pop($relations);

        return $this->relatedQuery(
            $builder,
            Str::camel(implode('.', $relations)),
            $column,
            $operator,
            $value,
            $func,
        );
    }

    protected function relatedQuery($query, $relation, $column, $operator, $value, $func)
    {

        if ($operator === 'is_null') {
            return $query->whereDoesntHave($relation);
        }

        $temp = $func == 'orWhere' ? 'orWhereHas' : 'whereHas';

        return $query->{$temp}($relation, function ($query) use ($column, $operator, $value) {
            $this->basicQuery($query, $column, $operator, $value, 'where');
        });
    }

    protected function basicQuery($query, $column, $operator, $value, $func)
    {
        if ($query->getModel()->getConnection()->getDriverName() == 'mysql') {
            $column = $query->getModel()->getTable().'.'.$column;
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

    protected function searchModel($query, $columns, $searchTerm)
    {
        foreach ($columns as $key => $column) {
            $queryParts = $this->resolveQueryParts('contains', $searchTerm);
            $query = $this->buildSearch(
                $query,
                $column,
                $queryParts['operator'],
                $queryParts['value'],
                'orWhere'
            );
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
        switch (Str::snake($operator)) {
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
                    'value' => is_array($value) ? $value : explode(',', $value),
                ];
            case 'not_in':
                return [
                    'operator' => 'not_in',
                    'value' => is_array($value) ? $value : explode(',', $value),
                ];
            case 'search':
                return [
                    'operator' => 'search',
                    'value' => $value,
                ];
            default:
                return null; // or throw an exception, or handle as needed
        }

    }

    protected function checkModelRelationships($relation)
    {
        $class = new ReflectionClass($this->model);
        $methods = $class->getMethods(\ReflectionMethod::IS_PUBLIC);
        $relationships = [];

        foreach ($methods as $method) {
            // Skip inherited methods and methods with required parameters
            if (
                $method->class != get_class($this->model) ||
                $method->getNumberOfRequiredParameters() > 0
            ) {
                continue;
            }

            try {
                $result = $method->invoke($this->model);
                if ($result instanceof Relation) {
                    $relationships[] = $method->getName();
                }
            } catch (\Throwable $e) {
                // Ignore methods that throw exceptions
            }
        }

        return in_array($relation, $relationships);
    }
}
