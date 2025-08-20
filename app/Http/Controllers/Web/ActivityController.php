<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\BaseController;
use App\Models\Activity;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class ActivityController extends BaseController
{
    protected $entityLabels = [
        'App\\Models\\Quote' => 'Quote Properties',
        'App\\Models\\QuoteItem' => 'Line Item',
        'App\\Models\\QuoteDiscount' => 'Discount',
    ];

    protected $model = Activity::class;

    protected $fieldsToExport = [
        'total',
        'margin',
        'price',
        'discount',
        'quantity',
        'contract_length',
        'name',
        'status',
    ];

    /**
     * Display a listing of the resource.
     */
    public function index() {}

    /**
     * Show the form for creating a new resource.
     */
    public function create() {}

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) {}

    /**
     * Display the specified resource.
     */
    public function show(Activity $activity)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Activity $activity) {}

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Activity $activity) {}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Activity $activity)
    {
        //
    }

    protected function transformExportData(Collection $data): Collection
    {
        $data = $data->sortByDesc(function ($item) {
            return $item->updated_at;
        });

        $rows = [];

        foreach ($data as $item) {
            $rawChanges = [];
            if (is_array($item->data)) {
                $rawChanges = $item->data;
            } elseif (is_object($item->data)) {
                $rawChanges = (array) $item->data;
            }

            foreach ($rawChanges as $change) {
                $field = is_array($change) ? ($change['field'] ?? null) : ($change->field ?? null);
                if (! $field || ! in_array($field, $this->fieldsToExport, true)) {
                    continue;
                }

                $from = is_array($change) ? ($change['from'] ?? null) : ($change->from ?? null);
                $to = is_array($change) ? ($change['to'] ?? null) : ($change->to ?? null);

                if (empty($from) && empty($to)) {
                    continue;
                }

                $rows[] = [
                    'user_id' => $item->user->id,
                    'username' => $item->user->name,
                    'operation' => $item->operation,
                    'activitable_type' => $this->entityLabels[$item->activitable_type] ?? null,
                    'activitable_id' => $item->activitable_id,
                    'product_name' => $this->getProductName($item),
                    'changed_field' => $field,
                    'old' => (is_scalar($from) || is_null($from)) ? $from : json_encode($from),
                    'new' => (is_scalar($to) || is_null($to)) ? $to : json_encode($to),
                    'timestamp' => $item->updated_at,
                ];
            }
        }

        return new Collection($rows);
    }

    public function getProductName($item)
    {
        switch ($item->activitable_type) {
            case 'App\\Models\\QuoteItem':
                return $item->activitable->name;
            case 'App\\Models\\QuoteDiscount':
                return 'Global discount';
            default:
                return '#';
        }
    }
}
