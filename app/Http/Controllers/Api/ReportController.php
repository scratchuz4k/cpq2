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
}
