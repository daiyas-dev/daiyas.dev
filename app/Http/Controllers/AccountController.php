<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Dotenv\Exception\ValidationException;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;
use App\Events\PublicEvent;
use Illuminate\Support\Facades\DB;

class AccountController extends Controller
{
    use AuthenticatesUsers{
        login as AuthLogin;
    }

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth')->except('login');
    }

    /**
     * Login
     */
    public function Login($request)
    {
        //ID/PW
        $vm = (object) $request->all();
        $UserId = $vm->name;
        $Password = $vm->password;
        $CheckOnly = $vm->CheckOnly;

        if ($CheckOnly) {
            if (Auth::check()) {
                return [
                    'IsLogin' => true,
                    'UserId' => Auth::user()->name,
                    'UserNm' => Auth::user()->name,
                    'Password' => '',
                    'BmnCd' => "bmn01",
                    'CsrfToken' => $request->session()->token(),
                ];
            } else {
                return ['IsLogin' => Auth::check()];
            }
        } else {
            try {
                //Laravel認証等ログイン処理
                $response = $this->AuthLogin($request);

                return [
                    'IsLogin' => true,
                    'UserId' => $UserId,
                    'UserNm' => $UserId,
                    'Password' => '',
                    'BmnCd' => 'bmn01',
                    'CsrfToken' => $request->session()->token(),
                ];
            } catch (Exception $e) {
                return [
                    'IsLogin' => false,
                    'message' => $e->getMessage(),
                ];
            }
        }
    }
    /**
     * Login
     */
    public function Logout($request)
    {
        //Laravel認証ログアウト処理
        Auth::logout();
        return;
    }

    /**
     * GetMenuList
     */
    public function GetMenuList()
    {
        //search menus
        $MenuList = DB::table('menus')->where('functionId', 'like', '%%')->get();

        return response()->json($MenuList);
    }
}
