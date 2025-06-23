<?php

namespace App\Http\Controllers;

use App\Export\Export;
use App\Helpers\DataProvider;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

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
