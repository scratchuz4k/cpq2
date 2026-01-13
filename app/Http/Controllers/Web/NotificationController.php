<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\BaseController;
use App\Models\Notification;
use App\Services\NotificationService;
use Inertia\Inertia;

class NotificationController extends BaseController
{
    protected $model = Notification::class;

    public function __construct(protected NotificationService $notificationService)
    {
        parent::__construct();
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('Notifications/Index');
    }
}
