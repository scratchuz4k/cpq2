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
        $this->applyOrder();

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
        $this->processGroup();
        $this->applyOrder();

        $collection = [];

        $page = request()->input('page', 1);
        $limit = request()->input('limit', 10);
        $group = request()->input('group', null);

        $total = $this->builder->get()->count();
        $items = $this->builder->paginate(perPage: $limit, page: $page);

        if (! $group) {
            return $resource::collection($items);
        }

        $group = explode('.', (string) $group);
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

    private function processGroup()
    {
        $group = request()->input('group', null);
        if ($group) {
            $groupOrder = request()->input('groupOrder', 'asc');
            $groupBy = explode('.', (string) $group);

            if (count($groupBy) == 2) {
                $table = $this->builder->getModel()->getTable();
                $this->builder = $this->builder->select($table.'.*')->leftJoinRelation(Str::camel($groupBy[0]).' as '.$groupBy[0])
                    ->orderBy($groupBy[0].'.'.$groupBy[1], $groupOrder);
            } else {
                $this->builder = $this->builder->orderBy($groupBy[0], $groupOrder);
            }
        }
    }

    private function processFilters()
    {
        $search = request()->input('search', null);
        $filters = request()->input('filters', []);
        $filters = is_string($filters) ? json_decode($filters, true) : $filters;

        if ($search) {
            $columns = request()->get(key: 'columns') ? explode(',', (string) request()->get('columns')) : $this->builder->getModel()->getFillable();
            $hidden = $this->builder->getModel()->getHidden();

            $columns = array_filter($columns, function ($column) use ($hidden) {
                $firstPart = explode('.', $column)[0];

                return ! in_array($firstPart, $hidden);
            });

            $this->builder = $this->builder->where(fn ($query) => $this->searchModel($query, $columns, $search));
        }

        foreach ($filters as $filter) {
            if (isset($filter['group']) && is_array($filter['group'])) {

                $this->builder = $this->builder->where(function ($query) use ($filter) {
                    foreach ($filter['group'] as $i => $groupFilter) {
                        if (is_null($groupFilter['value'])) {
                            continue;
                        }
                        $queryParts = $this->resolveQueryParts($groupFilter['operator'], $groupFilter['value']);
                        $func = array_key_exists('function', $groupFilter) ? $groupFilter['function'] : ($i === 0 ? 'where' : 'orWhere');
                        $this->buildSearch(
                            $query,
                            $groupFilter['column'],
                            $queryParts['operator'],
                            $queryParts['value'],
                            $func
                        );
                    }
                });
            } else {
                if (is_null($filter['value'])) {
                    continue;
                }

                $queryParts = $this->resolveQueryParts($filter['operator'], $filter['value']);
                $this->builder = $this->buildSearch(
                    $this->builder,
                    $filter['column'],
                    $queryParts['operator'],
                    $queryParts['value'],
                    array_key_exists('function', $filter) ? $filter['function'] : 'where'
                );
            }
        }
    }

    private function applyOrder()
    {
        $order = request()->input('order', 'asc');
        $orderBy = request()->input('orderBy', 'id');
        $relations = explode('.', (string) $orderBy);

        if (count($relations) > 1 && ! $this->checkModelRelationships($relations[0])) {
            return;
        }

        $table = $this->model->getTable();

        if ($this->model->getConnection()->getDriverName() !== 'mongodb') {
            $this->builder = $this->builder->select($table.'.*');
        }

        // Extract the column name (last element)
        $column = array_pop($relations);

        // Build nested relation path
        $relationPath = [];
        foreach ($relations as $part) {
            $relationPath[] = Str::camel($part);
        }

        // Join the relation path with dots for nested relations
        $fullRelationPath = implode('.', $relationPath);

        // Create alias for the final table (use the last relation name)
        $alias = end($relations);

        if (CustomQuery::exists($this->builder->getModel()->getTable(), $fullRelationPath.'.'.$column)) {
            $this->builder = $this->builder->addSelect([
                $alias => CustomQuery::execute($this->builder->getModel()->getTable(), $fullRelationPath.'.'.$column),
            ]);
            $this->builder = $this->builder->orderBy($alias, $order);

            return;
        }

        if (count($relations) == 0) {
            // No relations, order directly on the model's table
            $this->builder = $this->builder->orderBy($column, $order);
        } else {
            $this->builder = $this->builder->leftJoinRelation($fullRelationPath.' as '.$alias)
                ->orderBy($alias.'.'.$column, $order);
        }

    }

    public function buildSearch($builder, $column, $operator, $value, $func)
    {
        $relations = explode('.', (string) $column);
        $relation = Str::camel($relations[0]);

        if (! $this->checkModelRelationships($relation)) {
            return $this->basicQuery($builder, $column, $operator, $value, $func);
        }

        $relations = explode('.', string: (string) $column);
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

    protected function basicQuery($query, $column, $operator, $value, $function)
    {
        if ($query->getModel()->getConnection()->getDriverName() == 'mysql') {
            $column = $query->getModel()->getTable().'.'.$column;
        }

        $func = $this->getFunction($function, $operator);

        return match ($operator) {
            'in' => $query->whereIn($column, $value),
            'not_in' => $query->whereNotIn($column, $value),
            'is_null' => $query->{$func}($column),
            default => $query->{$func}($column, $operator, $value),
        };
    }

    protected function relatedQuery($query, $relation, $column, $operator, $value, $function)
    {
        $temp = $function == 'orWhere' ? 'orWhereHas' : 'whereHas';
        $tempDoesntHave = $function == 'orWhere' ? 'orWhereDoesntHave' : 'whereDoesntHave';

        return match ($operator) {
            'is_null' => $query->{$tempDoesntHave}($relation),
            default => $query->{$temp}($relation, function ($query) use ($column, $operator, $value) {
                $this->basicQuery($query, $column, $operator, $value, 'where');
            }),
        };
    }

    protected function searchModel($query, $columns, $searchTerm)
    {
        foreach ($columns as $column) {
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

    protected function getFunction($function, $operator)
    {
        $functions = ['where', 'orWhere', 'whereNull', 'orWhereNull'];

        if (! in_array($function, $functions)) {
            return 'where';
        }

        if ($operator == 'is_null') {
            return $function === 'orWhere' ? 'orWhereNull' : 'whereNull';
        }

        return $function;
    }

    protected function resolveQueryParts($operator, $value)
    {
        return match (Str::snake($operator)) {
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
                'value' => is_array($value) ? $value : explode(',', (string) $value),
            ],
            'not_in' => [
                'operator' => 'not_in',
                'value' => is_array($value) ? $value : explode(',', (string) $value),
            ],
            'search' => [
                'operator' => 'search',
                'value' => $value,
            ],
            default => null,
        };

    }

    protected function checkModelRelationships($relation)
    {
        $class = new ReflectionClass($this->model);
        $methods = $class->getMethods(\ReflectionMethod::IS_PUBLIC);
        $relationships = [];

        foreach ($methods as $method) {
            // Skip inherited methods and methods with required parameters
            if (
                $method->class != $this->model::class ||
                $method->getNumberOfRequiredParameters() > 0
            ) {
                continue;
            }

            try {
                $result = $method->invoke($this->model);
                if ($result instanceof Relation) {
                    $relationships[] = $method->getName();
                }
            } catch (\Throwable) {
                // Ignore methods that throw exceptions
            }
        }

        return in_array($relation, $relationships) || in_array(Str::camel($relation), $relationships);
    }
}
