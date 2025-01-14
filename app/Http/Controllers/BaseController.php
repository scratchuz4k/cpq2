<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class BaseController extends Controller
{
    use AuthorizesRequests, ValidatesRequests;

    protected $model;
    public function __construct()
    {
        if ($this->model) {
            $this->authorizeResource($this->model);
        }
    }
}
