<?php

namespace App\Http\Controllers\Web;

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Models\EmailTemplate;
use App\Http\Controllers\BaseController;

class EmailTemplateController extends BaseController
{
    protected $model = EmailTemplate::class;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render(component: 'Emails/Index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
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
    public function show(EmailTemplate $emailTemplate)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(EmailTemplate $emailTemplate)
    {
        return Inertia::render(component: 'Emails/Edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, EmailTemplate $emailTemplate)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(EmailTemplate $emailTemplate)
    {
        //
    }
}
