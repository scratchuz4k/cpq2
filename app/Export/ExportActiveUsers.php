<?php

namespace App\Export;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ExportActiveUsers implements FromCollection, WithHeadings, ShouldAutoSize
{
    public function __construct(protected array $dateRange, protected array $columns){}

    public function collection()
    {
        $this->columns = array_map(fn($col) => "users.$col", $this->columns);
        $query = User::join('activities', function($join) {
            $join->on('users.id', '=', 'activities.user_id')
                ->where('activities.operation', 'user_login')
                ->where('activities.activitable_type', 'App\Models\User')
                ->whereBetween('activities.created_at', formated_date_range($this->dateRange, User::class));
            })
            ->select(array_merge(
                $this->columns,
                [DB::raw('count(activities.id) as login_count')]
            ))
            ->where('users.id', '!=', 1)
            ->where('users.tenant_id', request()->user()->tenant_id)
            ->groupBy($this->columns);

        $data = $query->get();

        if ($data->count()) {
            return $data;
        } else {
            throw new \Exception('No data Found!');
        }
    }

    public function headings(): array
    {
        $headings = array_map(
            fn($col) => Str::title(str_replace('_', ' ', $col)),
            $this->columns
        );

        return array_merge($headings, ['Login Count']);
    }

    public static function getColumns()
    {
        $user = new User;
        $columns = array_diff(
            Schema::getColumnListing($user->getTable()),
            $user->getHidden()
        );
        return array_values($columns);
    }
}
