<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use DB;
use Illuminate\Support\Carbon;
use PDO;

class DAI04050Controller extends Controller
{
    /**
     * Search
     */
    public function Search($request)
    {
        $Utilities = new UtilitiesController();
        return response()->json($Utilities->SearchTankaList($request));
    }
}
