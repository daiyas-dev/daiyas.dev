<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ConnectionTestController extends Controller
{
    public function index()
    {
      return "connection ok";
    }
}
