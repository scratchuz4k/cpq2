<?php

namespace App\Http\Controllers\Web;

use Inertia\Inertia;
use App\Models\Order;
use Inertia\Controller;

class DashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('Dashboard');
    }
}
