<?php

namespace App\Export;

use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ExportUsers implements FromCollection, ShouldAutoSize, WithHeadings
{
    public function __construct(protected array $dateRange, protected array $columns) {}

    public function collection()
    {
        $query = User::select($this->columns)
            ->where('id', '!=', 1)
            ->where('tenant_id', request()->user()->tenant_id)
            ->whereBetween('created_at', formated_date_range($this->dateRange, User::class));

        if ($query->count()) {
            return $query->get();
        } else {
            throw new \Exception('No data Found!');
        }
    }

    public function headings(): array
    {
        return array_map(
            fn ($col) => Str::title(str_replace('_', ' ', $col)),
            $this->columns
        );
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
