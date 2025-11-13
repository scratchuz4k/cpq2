<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Models\Activity;
use App\Models\Quote;
use App\Models\Supplier;
use App\Models\User;
use App\Policies\ReportPolicy;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends BaseController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny report', ReportPolicy::class);
        $data = [
            'users' => (new ReportService(User::class))->getData($request->all()),
            'active_users' => (new ReportService(Activity::class, User::class))->getData($request->all()),
            'quotes' => (new ReportService(Quote::class))->getData($request->all()),
            'quotes_suppliers' => (new ReportService(Quote::class, Supplier::class))->getData($request->all()),
        ];

        return response()->json($data);
    }

    public function getColumns(Request $request)
    {
        $this->authorize('viewAny report', ReportPolicy::class);

        $export = $request->input('export');

        if (! $export) {
            return response()->json(['error' => 'Export parameter is required'], 400);
        }

        $export_class = \App\Export\Export::class.$export;
        if (! class_exists($export_class)) {
            return response()->json(['error' => 'Export Class not found'], 404);
        }

        return response()->json([
            'columns' => array_map(fn ($col) => [
                'id' => $col,
                'title' => Str::title(str_replace('_', ' ', $col))], $export_class::getColumns()),
        ]);
    }

    public function export(Request $request)
    {
        $this->authorize('viewAny report', ReportPolicy::class);
        $export = $request->input('export');
        $export_class = \App\Export\Export::class.$export;

        if (! class_exists($export_class)) {
            return response()->json(['error' => 'Export Class not found'], 404);
        }

        $date_range = [];
        if (
            array_key_exists('date_interval', $request->all())
            && ! empty($request->date_interval)
        ) {
            $date_range = json_decode((string) $request->date_interval, true);
        }

        $columns = $request->input('columns')
            ? explode(',', (string) $request->input('columns'))
            : $export_class::getColumns();

        $date = now()->format('Y-m-d');
        $filename = "Export{$export}_export_{$date}.xlsx";

        try {
            return Excel::downloads(new $export_class($date_range, $columns), $filename);
        } catch (\Exception $e) {
            return handleException($e);
        }
    }
}
