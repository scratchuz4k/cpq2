<?php

namespace App\Models\General;

use App\Helpers\DataProvider;
use App\Helpers\FilterProvider;
use App\Models\Scopes\TenantScope;
use App\Trait\SetTenantId;
use Illuminate\Support\Collection;
use MongoDB\Laravel\Eloquent\Model;

class Blacklist extends Model
{
    use SetTenantId;

    protected $connection = 'mongodb_general';

    protected $collection = 'blacklists';

    protected $guarded = [];

    protected $filterable_columns = [
        'productName',
        'providerName',
        'connectivitySupplierGroup',
        'bearer',
        'speed',
        'contractTermMonths',
    ];


    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);
    }

    public function getBlacklistedData(Collection &$data): Collection
    {
        $items = self::all();
        $blacklisted_items = collect();

        foreach ($this->getBlacklistFilters() as $item) {
            $blacklisted_items = $blacklisted_items->merge(FilterProvider::get($data->toArray(), $item));        }

        return $blacklisted_items;
    }

    public function getBlacklistFilters(): array
    {
        $blacklist_items = $this::all()->toArray();
        $blacklist_filters = [];

        foreach ($blacklist_items as $blacklist_item) {
            foreach ($blacklist_item as $column => $value) {
                if ($value && in_array($column, $this->filterable_columns)) {
                    if (is_array($value)) {
                        if (! is_null($value['value'])) {
                            $blacklist_filters[$blacklist_item['id']][] = [
                                'column' => $column,
                                'operator' => $value['operator'],
                                'value' => $value['unit'] === 'Mb'
                                    ? $value['value']
                                    : (
                                    is_array($value['value'])
                                        ? array_map(fn ($item) => $item * 1000, $value['value'])
                                        : $value['value'] * 1000
                                    ),
                            ];
                        }

                        continue;
                    }
                    $blacklist_filters[$blacklist_item['id']][] = [
                        'column' => $column,
                        'operator' => 'in',
                        'value' => $value,
                    ];
                }
            }
        }

        return $blacklist_filters;
    }
}
