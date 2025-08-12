<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\BaseController;
use App\Models\Activity;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class ActivityController extends BaseController
{
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

    protected $entityLabels = [
        'App\\Models\\Quote' => 'Quote Properties',
        'App\\Models\\QuoteItem' => 'Line Item',
        'App\\Models\\QuoteDiscount' => 'Discount',
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

                $rows[] = [
                    'username' => User::find($item->user->id)->name,
                    'activity_id' => $item->id,
                    'operation' => $item->operation,
                    'activitable_type' => $this->entityLabels[$item->activitable_type] ?? null,
                    'activitable_id' => $item->activitable_id,
                    'timestamp' => $item->updated_at,
                    'changed_field' => $field,
                    'old' => (is_scalar($from) || is_null($from)) ? $from : json_encode($from),
                    'new' => (is_scalar($to) || is_null($to)) ? $to : json_encode($to),
                ];
            }
        }

        return new Collection($rows);
    }
}
