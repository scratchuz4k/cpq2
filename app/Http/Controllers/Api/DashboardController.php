<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Models\Activity;
use App\Models\Quote;
use App\Models\Supplier;
use App\Models\User;
use App\Policies\ExportPolicy;
use App\Services\DashboardService;
use Illuminate\Http\Request;

class DashboardController extends BaseController
{
    /**
     * Display a listing of the resource.
     */
    public function reporting(Request $request)
    {
        $this->authorize('viewAny export', ExportPolicy::class);
        $data = [
            'users' => (new DashboardService(User::class))->getData($request->all()),
            'active_users' => (new DashboardService(Activity::class, User::class))->getData($request->all()),
            'quotes' => (new DashboardService(Quote::class))->getData($request->all()),
            'quotes_suppliers' => (new DashboardService(Quote::class, Supplier::class))->getData($request->all())
        ];

        return response()->json($data);
    }
}
