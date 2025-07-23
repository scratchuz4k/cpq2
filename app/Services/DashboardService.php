<?php

namespace App\Services;

use App\Enums\CloudMarketSuppliersEnum;
use App\Enums\SolutionTypesEnum;
use App\Models\QuoteItem;
use App\Models\SolutionType;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;

class DashboardService
{
    public $model;
    public $relation;
    protected $user_instance = 'App\\Models\\User';
    protected $activity_instance = 'App\\Models\\Activity';
    protected $supplier_instance = 'App\\Models\\Supplier';
    protected $quote_instance = 'App\\Models\\Quote';

    public function __construct($model, $relation = null)
    {
        $this->model = app($model);

        if ($relation) {
            $this->relation = app($relation);
        }
    }

    private function excludeSuperAdmins($query)
    {
        return $query->whereDoesntHave('roles', function ($q) {
            $q->where('name', 'Super Admin');
        });
    }

    private function excludeSuperAdminActivities($query)
    {
        return $query->whereHas('user', function ($userQuery) {
            $userQuery->whereDoesntHave('roles', function ($roleQuery) {
                $roleQuery->where('name', 'Super Admin');
            });
        });
    }

    public function getData(array $array)
    {
        if ($this->relation) {
            return $this->getRelationFilteredData($array);
        }

        return $this->getFilteredData($array);
    }

    public function getFilteredData(array $array): array
    {
        $cumulative = true;
        $date_interval = formated_date_range($array, get_class($this->model));
        $query = $this->model->whereBetween('created_at', $date_interval);
        $total_query = $this->model->query();

        if ($this->model instanceof $this->user_instance) {
            $query = $this->excludeSuperAdmins($query)->where('tenant_id', request()->user()->tenant_id);

            $total_query = $this->excludeSuperAdmins($total_query)->where('tenant_id', request()->user()->tenant_id);
        }

        if ($this->model instanceof $this->quote_instance) {
            $cumulative = false;
        }

        if (! isset($this->relation) && $this->model instanceof $this->quote_instance) {
            $results = [];
            $grouped = collect();

            $solution_types = $this->model->distinct()->pluck('solution_type_id');

            foreach ($solution_types as $key => $solution_type_id) {
                $solution_type = SolutionType::find($solution_type_id);
                $quotes_collection = $solution_type->quotes;

                [$data, $labels] = $this->getParsedData(
                    $date_interval[0],
                    $date_interval[1],
                    $quotes_collection,
                    $cumulative
                );

                $results[$solution_type->name] = [
                    'data' => $data,
                    'total' => $quotes_collection->count(),
                    'label' => $solution_type->name ?? 'Unknown',
                ];
            }

            uasort($results, function ($a, $b) {
                return $b['total'] <=> $a['total'];
            });

            return [
                'data' => $results ?? collect(),
                'labels' => $labels ?? [],
            ];
        }

        [$data, $labels] = $this->getParsedData(
            $date_interval[0],
            $date_interval[1],
            null,
            $cumulative
        );

        return [
            'data' => $data,
            'labels' => $labels,
            'total' => $total_query->count(),
            'partial' => $query->count(),
        ];
    }

    public function getParsedData(Carbon $start_date, Carbon $end_date, ?Collection $collection = null, bool $cumulative = true): array
    {
        $diffInDays = $start_date->diffInDays($end_date);
        $diffInMonths = $start_date->diffInMonths($end_date);

        switch ($diffInMonths) {
            case $diffInDays < 1:
                $adjustedStartDate = (clone $start_date)->subDay()->startOfDay();
                $unit = 'day';
                $period = CarbonPeriod::create($adjustedStartDate, '1 day', $end_date);
                break;
            case $diffInMonths < 1:
                $unit = 'day';
                $period = CarbonPeriod::create($start_date, '1 day', $end_date);
                break;
            case $diffInMonths < 6:
                $unit = 'week';
                $period = CarbonPeriod::create($start_date, '1 week', $end_date);
                break;
            case $diffInMonths < 12:
                $unit = 'month';
                $period = CarbonPeriod::create($start_date, '1 month', $end_date);
                break;
            default:
                $unit = 'year';
                $period = CarbonPeriod::create($start_date, '1 year', $end_date);
                break;
        }

        $model = $this->model;
        $items = collect($period)->map(function ($date) use ($unit, $model, $collection, $cumulative) {
            if ($unit === 'day') {
                $startDate = $date->copy()->startOfDay();
                $endDate = $date->copy()->endOfDay();
                $label = $date->format('Y-m-d');
            } elseif ($unit === 'week') {
                $startDate = $date->copy()->startOfWeek();
                $endDate = $date->copy()->endOfWeek();
                $label = 'Week of '.$date->format('Y-m-d');
            } elseif ($unit === 'month') {
                $startDate = $date->copy()->startOfMonth();
                $endDate = $date->copy()->endOfMonth();
                $label = $date->format('M, Y');
            } else {
                $startDate = $date->copy()->startOfYear();
                $endDate = $date->copy()->endOfYear();
                $label = $date->format('Y');
            }

            if ($collection) {
                if ($cumulative) {
                    $count = $collection->where('created_at', '<=', $endDate)->count();
                } else {
                    $count = $collection->whereBetween('created_at', [$startDate, $endDate])->count();
                }

                return [
                    'count' => $count,
                    $unit => $label,
                ];
            }

            if ($cumulative) {
                $query = $model->where('created_at', '<=', $endDate);
            } else {
                $query = $model->whereBetween('created_at', [$startDate, $endDate]);
            }

            if ($model instanceof $this->user_instance) {
                $query = $this->excludeSuperAdmins($query)
                    ->where('tenant_id', request()->user()->tenant_id);
            }

            if ($model instanceof $this->activity_instance) {
                $query = $this->excludeSuperAdminActivities($query)->distinct('user_id');
            }

            return [
                'count' => $query->count(),
                $unit => $label,
            ];
        });

        return [
            $items->pluck('count')->toArray(),
            $items->pluck($unit)->toArray(),
        ];
    }

    public function getRelationFilteredData(array $array)
    {
        $date_interval = formated_date_range($array, get_class($this->model));
        $query = $this->model->whereBetween('created_at', $date_interval);
        $total_query = $this->model->query();

        if ($this->model instanceof $this->user_instance) {
            $query = $this->excludeSuperAdmins($query)
                ->where('tenant_id', request()->user()->tenant_id);

            $total_query = $this->excludeSuperAdmins($total_query)
                ->where('tenant_id', request()->user()->tenant_id);
        }

        if ($this->model instanceof $this->activity_instance) {
            $query = $this->excludeSuperAdminActivities($query)
                ->where('operation', 'user_login')
                ->where('activitable_type', $this->user_instance);

            $total_query = $this->excludeSuperAdminActivities($total_query)
                ->where('operation', 'user_login')
                ->where('activitable_type', $this->user_instance);
        }

        if ($this->relation instanceof $this->supplier_instance) {
            $results = [];
            $grouped = collect();

            $connectivity_solution_id = SolutionType::where('code', SolutionTypesEnum::ConnectivityEthernet)->value('id');
            $items = $query->where('solution_type_id', $connectivity_solution_id)
                ->with(['items' => function ($query) {
                    $query->whereNotNull('data');
                }])
                ->get();

            $items->each(function ($quote) use (&$grouped) {
                $providers = $quote->items->map(function ($item) {
                    return data_get($item->data, 'providerKey');
                })->unique();

                foreach ($providers as $provider) {
                    $grouped[$provider] = $grouped->get($provider, collect())->push($quote);
                }
            });

            foreach ($grouped as $key => $group) {
                [$data, $labels] = $this->getParsedData(
                    $date_interval[0],
                    $date_interval[1],
                    $group
                );

                $results[$key] = [
                    'data' => $data,
                    'total' => $group->count(),
                    'label' => CloudMarketSuppliersEnum::tryFrom($key)?->name ?? 'Unknown',
                ];
            }

            $total = QuoteItem::selectRaw("COUNT(DISTINCT JSON_UNQUOTE(JSON_EXTRACT(data, '$.providerKey'))) as provider_count")
                ->whereBetween('created_at', $date_interval)
                ->value('provider_count');

            if (! isset($labels)) {
                $labels = [
                    ($date_interval[0]->diffInDays($date_interval[1]) < 1)
                        ? $date_interval[0]->subDay()->format('Y-m-d')
                        : $date_interval[0]->format('Y-m-d'),
                    $date_interval[1]->format('Y-m-d'),
                ];
            }

            uasort($results, function ($a, $b) {
                return $b['total'] <=> $a['total'];
            });

            return [
                'data' => $results ?? collect(),
                'total' => $total,
                'partial' => $grouped->count(),
                'labels' => $labels ?? [],
            ];
        }

        [$data, $labels] = $this->getParsedData($date_interval[0], $date_interval[1], null, false);

        return [
            'data' => $data,
            'labels' => $labels,
            'total' => $total_query->distinct()->pluck('user_id')->count(),
            'partial' => $query->distinct()->pluck('user_id')->count(),
        ];
    }
}
