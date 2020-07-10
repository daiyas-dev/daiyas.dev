<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Dotenv\Exception\ValidationException;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;
use App\Events\PublicEvent;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

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
     * Overrides Illuminate\Foundation\Auth\AuthenticatesUsers username().
     * Get the login username to be used by the controller.
     *
     * @return string
     */
    public function username()
    {
        return 'ユーザーＩＤ';
    }
    public function password()
    {
        return 'パスワード';
    }

    /**
     * Overrides Illuminate\Foundation\Auth\AuthenticatesUsers validateLogin().
     * Validate the user login request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function validateLogin(Request $request)
    {
        $request->validate([
            'userid' => 'required|string',
            'password' => 'required|string',
        ]);
    }

    /**
     * Overrides Illuminate\Foundation\Auth\AuthenticatesUsers credentials().
     * Get the needed authorization credentials from the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    protected function credentials(Request $request)
    {
        //return $request->only('userid', 'password');
        return [
            $this->username() => $request->userid,
            $this->password() => $request->password
        ];
    }

    /**
     * Login
     */
    public function Login($request)
    {
        //ID/PW
        $vm = (object) $request->all();
        $UserId = $vm->userid;
        $Password = $vm->password;
        $CheckOnly = $vm->CheckOnly;

        if ($CheckOnly) {
            if (Auth::check()) {
                return [
                    'IsLogin' => true,
                    'UserId' => Auth::user()->担当者ＣＤ,
                    'UserNm' => Auth::user()->担当者名,
                    'BushoCd' => Auth::user()->部署->部署CD,
                    'BushoNm' => Auth::user()->部署->部署名,
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
                    'UserId' => Auth::user()->担当者ＣＤ,
                    'UserNm' => Auth::user()->担当者名,
                    'BushoCd' => Auth::user()->部署->部署CD,
                    'BushoNm' => Auth::user()->部署->部署名,
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
        $MenuList = DB::table('menus')
            // ->where('functionId', 'like', '%%')
            ->orderBy('functionId')
            ->orderBy('programId')
            ->get();

        return response()->json($MenuList);
    }

    public function Encrypt($request)
    {
        $ps = explode(',', $request->passwords);

        $ret = "";
        foreach ($ps as $p) {
            $enc = Hash::make($p);

            $ret = $ret . $p . " : " . $enc . "<br>";
        }

        return $ret;
    }
}
