<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Dotenv\Exception\ValidationException;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;
use App\Events\PublicEvent;

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
        //TODO: dummy MenuList
        $MenuList = json_decode('[
            {
              "Title": "お知らせ登録",
              "FunctionId": "PID00",
              "ProgramId": "PID0002"
            },
            {
              "Title": "メニュー1",
              "FunctionId": "PID01",
              "ProgramId": ""
            },
            {
              "Title": "メニュー1-1",
              "FunctionId": "PID01",
              "ProgramId": "PID0101"
            },
            {
              "Title": "メニュー1-2",
              "FunctionId": "PID01",
              "ProgramId": "PID0102"
            },
            {
              "Title": "通知送信",
              "FunctionId": "PID02",
              "ProgramId": "PID0201"
            },
            {
              "Title": "日次処理",
              "FunctionId": "DAI01",
              "ProgramId": ""
            },
            {
              "Title": "日次処理-1（持出数一覧表）",
              "FunctionId": "DAI01",
              "ProgramId": "DAI0101"
            },
            {
                "Title": "日次処理-2（日配持出入力）",
                "FunctionId": "DAI01",
                "ProgramId": "DAI0102"
            },
            {
                "Title": "日次処理-3（注文入力）",
                "FunctionId": "DAI01",
                "ProgramId": "DAI0103"
            }
        ]');

        return response()->json($MenuList);
    }
}
