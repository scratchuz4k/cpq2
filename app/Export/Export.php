<?php

namespace App\Export;

use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class Export implements FromCollection, WithHeadings, ShouldAutoSize
{
    protected $model;
    protected $user_instance = 'App\\Models\\User';

    public function __construct(
        protected string $model_name,
        protected array $columns,
        protected array $filters
    ){
        $modelClass = "App\\Models\\" . $model_name;
        $this->model = new $modelClass;
    }
    public function collection()
    {
        $dateColumn = 'created_at';
        $query = $this->model
            ->select($this->columns)
            ->where('tenant_id', request()->user()->tenant_id);

        if (array_key_exists('date_column', $this->filters) && !empty($this->filters['date_column'])) {
            $dateColumn = Str::snake($this->filters['date_column']);
        }

        if (!empty($this->filters['dates'])) {
            $start = $this->filters['dates']['start'];
            $end = $this->filters['dates']['end'];

            if ($start) {
                $query->whereBetween(
                    $dateColumn,
                    [
                        Carbon::parse($this->filters['dates']['start']),
                        Carbon::parse($this->filters['dates']['end'])
                    ]
                );
            }elseif ($end) {
                $query->where($dateColumn, '<=', $end);
            }
        }

        if ($this->model instanceof $this->user_instance) {
            $query->where('id', '!=', 1);
        }
        return $query->get();
    }

    public function headings(): array
    {
        return array_map(
            fn($col) => Str::title(str_replace('_', ' ', $col)),
            $this->columns
        );
    }

    public static function getColumns(array $models)
    {
        $columns = [];
        foreach ($models as $modelClass) {
            $model = new $modelClass;

            $visibleColumns = array_diff(
                Schema::getColumnListing($model->getTable()),
                $model->getHidden()
            );

            foreach ($visibleColumns as $column) {
                $columns[] = class_basename($model) . '.' . $column;
            }
        }
        return $columns;
    }
}
