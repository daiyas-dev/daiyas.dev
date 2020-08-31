<?php

namespace App\Http\Controllers;

use App\Libs\DataSendWrapper;
use App\Models\得意先マスタ;
use App\Models\CTelToCust;
use App\Models\得意先履歴テーブル;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use DB;
use Exception;
use Illuminate\Support\Carbon;

class DAI04041Controller extends Controller
{
    /**
     * Save
     */
    public function Save($request)
    {
        $params = $request->all();

        $model = new 得意先マスタ();
        $model->fill($params);

        $CustomerCd = $params['得意先ＣＤ'];
        $data = collect($model)->all();
        $newData = array_merge(['得意先ＣＤ' => $CustomerCd], $data);

        //更新対象外のカラム
        if ($data['支払方法１'] <> '4') {
            unset($newData['金融機関CD']);
            unset($newData['金融機関支店CD']);
            unset($newData['記号番号']);
            unset($newData['口座種別']);
            unset($newData['口座番号']);
            unset($newData['口座名義人']);
        };

        $isNew = $params['IsNew'];
        $duplicate = "";

        // トランザクション開始
        try {
            DB::beginTransaction();
            if($isNew){
                //新規
                try {
                    DB::table('得意先マスタ')->insert($newData);
                } catch (Exception $exception) {
                    $errMs = $exception->getCode();

                    //主キー重複
                    if($errMs == "23000"){
                        $duplicate = $CustomerCd;
                    } else {
                        throw $exception;
                    }
                }
            }else{
                //修正
                DB::table('得意先マスタ')->where('得意先ＣＤ', $CustomerCd)->update($newData);
            }

            DB::commit();

            //モバイルSvを更新
            $Message = $params['Message'];
            $ds = new DataSendWrapper();

            $sql="select * from 得意先マスタ where 得意先ＣＤ=$CustomerCd";
            $CustomerData = (array)DB::selectOne($sql);
            $del_sql="delete from CustomerMaster where customer_code=$CustomerCd";
            $ds->Insert('得意先マスタ',$CustomerData,true,$newData['部署CD'],null,null, $Message,$del_sql);

        } catch (Exception $exception) {
            DB::rollBack();
            throw $exception;
        }


        $savedData = array_merge(['得意先ＣＤ' => $params['得意先ＣＤ']], $data);

        return response()->json([
            'result' => true,
            'model' => $savedData,
            'duplicate' => $duplicate,
        ]);
    }

    /**
     * Delete
     */
    public function Delete($request)
    {
        $CustomerCd = $request->CustomerCd;

        // トランザクション開始
        DB::transaction(function() use ($CustomerCd) {

            DB::table('得意先マスタ')->where('得意先ＣＤ', '=', $CustomerCd)->delete();

        });

        $params = $request->all();
        $model = new 得意先マスタ();
        $model->fill($params);
        $data = collect($model)->all();
        $newData = array_merge(['得意先ＣＤ' => $CustomerCd], $data);
        //モバイルSvを更新
        $Message = $params['Message'];
        $ds = new DataSendWrapper();
        $ds->Delete('得意先マスタ',$newData,true,null,null,null, $Message);

        return response()->json([
            'result' => true,
        ]);
    }

    /**
     * GetCustomerListForDetail
     */
    public function GetCustomerListForDetail($request)
    {
        $CustomerCd = $request->CustomerCd;
        $query = 得意先マスタ::query()
            ->when(
                $CustomerCd,
                function($q) use ($CustomerCd){
                    return $q->where('得意先ＣＤ', $CustomerCd);
                }
            );

        $CustomerList = $query->get();

        return response()->json($CustomerList);
    }

    /**
     * GetCourseListForCustomer
     */
    public function GetCourseListForCustomer($request)
    {
        $CustomerCd = $request->CustomerCd;
        $BushoCd = $request->BushoCd;

        $sql = "
            SELECT
                コースマスタ.コース区分,
                各種テーブル.各種名称,
                コーステーブル.コースＣＤ,
                コースマスタ.コース名

            FROM [コーステーブル]

            INNER JOIN [コースマスタ]
                ON コースマスタ.コースＣＤ = コーステーブル.コースＣＤ
                AND コースマスタ.部署ＣＤ = コーステーブル.部署ＣＤ

                INNER JOIN [得意先マスタ]
                ON 得意先マスタ.得意先ＣＤ = コーステーブル.得意先ＣＤ
                AND 得意先マスタ.部署ＣＤ = コーステーブル.部署ＣＤ

            INNER JOIN [各種テーブル]
                ON 各種テーブル.各種CD = 19
                AND 各種テーブル.行NO = コースマスタ.コース区分

            WHERE
                コーステーブル.得意先ＣＤ = $CustomerCd
                AND コーステーブル.部署ＣＤ = $BushoCd

            ORDER BY コースマスタ.コース区分,コーステーブル.コースＣＤ
        ";

        $CourseList = DB::select($sql);

        return response()->json($CourseList);
    }

    /**
     * GetFreeCustomerCdList
     */
    public function GetFreeCustomerCdList($request)
    {
        $StartNo = $request->StartNo;
        $EndNo = $request->EndNo;
        $WhereStartNo = !!$StartNo ? " AND $StartNo <= T1.digits" : "";
        $WhereEndNo = !!$EndNo ? " AND T1.digits <= $EndNo" : "";
        $top = !$StartNo || !$EndNo ? "TOP 10" : "";
        $startNumber = !!$StartNo ? $StartNo : 0;

        $sql = "
            WITH CTE(連番) AS
            (
                SELECT $startNumber UNION ALL SELECT 連番 + 1 FROM CTE
            )

            SELECT $top T1.digits
            FROM ( SELECT TOP 50000 連番 AS digits FROM CTE ) T1

            LEFT OUTER JOIN 得意先マスタ TOK
                ON T1.digits = TOK.得意先ＣＤ

            WHERE
                0=0
                $WhereStartNo
                $WhereEndNo
                AND TOK.得意先ＣＤ IS NULL
                AND T1.digits <> 0
                AND T1.digits < 10000000

            ORDER BY digits
            OPTION (MAXRECURSION 0)
        ";

        $CdList = DB::select($sql);

        return response()->json($CdList);
    }

    /**
     * GetTelToCustList
     */
    public function GetTelToCustList($request)
    {
        $CustomerCd = $request->CustomerCd;
        if($CustomerCd == null) return [];

        $query = CTelToCust::query()
            ->when(
                $CustomerCd,
                function($q) use ($CustomerCd){
                    return $q->where('Tel_CustNo', $CustomerCd);
                }
            )
            ->orderBy('Tel_RepFlg', 'asc')
            ->orderBy('Tel_TelNo', 'asc')
            ;

        $TelList = $query->get();


        return response()->json($TelList);
    }

    /**
     * SaveTelList
     */
    public function SaveTelList($request)
    {
        $CustomerCd = $request->CustomerCd;
        $params = $request->all();
        $data = $params['SaveList'];

        // トランザクション開始
        DB::transaction(function () use ($CustomerCd, $data) {

            $cnt = DB::table('C_TelToCust')->where('Tel_CustNo', $CustomerCd) ->count();
            if ($cnt == 0) {
                DB::table('C_TelToCust')->insert($data);
            } else {
                DB::table('C_TelToCust')->where('Tel_CustNo', $CustomerCd)->delete();
                DB::table('C_TelToCust')->insert($data);
            }

            // //確認用：削除予定
            // $query = CTelToCust::query()
            //     ->when(
            //         $CustomerCd,
            //         function($q) use ($CustomerCd){
            //             return $q->where('Tel_CustNo', $CustomerCd);
            //         }
            //     );

            // $TelList = $query->get();
            // $xxx = $TelList->toJson();
            // throw new \Exception('hogehoge');
        });

        $savedData = $data;

        return response()->json([
            'result' => true,
            'model' => $savedData,
        ]);
    }

    /**
     * UpdateHistoryList
     */
    public function UpdateHistoryList($request)
    {
        $params = $request->all();
        $CustomerCd = $params['得意先ＣＤ'];

        //得意先履歴ID取得
        $sql = "
            SELECT ISNULL(MAXID,0) + 1 AS SerialNumber
            FROM (
                SELECT MAX(得意先履歴ID) AS MAXID
                FROM 得意先履歴テーブル WHERE 得意先CD = $CustomerCd
            ) MAXID
        ";

        $CdList = DB::selectOne($sql);
        $SerialNumber = $CdList->SerialNumber;
        $saveData = array_merge(['得意先履歴ID' => $SerialNumber], $params);

        // トランザクション開始
        DB::transaction(function () use ($CustomerCd, $saveData) {

            DB::table('得意先履歴テーブル')->insert($saveData);

        });

        return response()->json($saveData);
    }
}
