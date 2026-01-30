<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\BaseController;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DashboardController extends BaseController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        if ($user->can('viewAny quote') || $user->can('viewOwn quote')) {
            return Inertia::location(route('quotes.index'));
        }

        return Inertia::render('Dashboard');
    }

    /**
     * Display a listing of the resource.
     */
    public function reporting()
    {
        return Inertia::render('Settings/Reporting');
    }

    /**
     * Display a listing of the resource.
     */
    public function settings()
    {
        return Inertia::render('Settings/Index');
    }
}
