<?php

namespace App\Http\Controllers\Api;

use App\Helpers\DataProvider;
use App\Http\Controllers\BaseController;
use App\Http\Requests\NotificationUpdateRequest;
use App\Http\Resources\NotificationResource;
use App\Models\Notification;
use App\Services\NotificationService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
        $dataProvider = new DataProvider($this->model);
        try {
            return $dataProvider->group(NotificationResource::class);
        } catch (Exception $e) {
            return handleException($e);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Notification $notification)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(NotificationUpdateRequest $request, Notification $notification)
    {

        try {
            $api = DB::transaction(fn () => $this->notificationService->update($notification, $request->all()));
        } catch (\Exception $e) {
            return handleException($e);
        }

        return new NotificationResource($notification);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Notification $notification)
    {
        //
    }

    public function markAllAsRead()
    {
        try {
            $this->notificationService->markAllAsRead(Auth::user());
        } catch (\Exception $e) {
            return handleException($e);
        }

        return response()->json(['message' => 'All notifications marked as read']);
    }
}
