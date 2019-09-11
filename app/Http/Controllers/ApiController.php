<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ApiController extends Controller
{
    //Common Action Function
    public function Invoke(Request $request, $conroller_name, $function_name) {
        $app = app();
        $controller = $app->make('\App\Http\Controllers\\' . $conroller_name . 'Controller');
        //$parameters = (object)$request->all();

        return $controller->callAction($function_name, array($request));
    }
}
