<?php

namespace App\Http\Controllers;

use App\Models\クレームデータ;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use DB;
use Illuminate\Support\Carbon;
use PDO;

class DAI04010Controller extends Controller
{
    /**
     * Load
     */
    public function Load($request) {
        $CustomerCd = $request->CustomerCd;

        $sql = "
            SELECT
                *
            FROM
                管理マスタ
            WHERE
                管理ＫＥＹ=1
        ";

        $dsn = 'sqlsrv:server=127.0.0.1;database=daiyas';
        $user = 'daiyas';
        $password = 'daiyas';

        $pdo = new PDO($dsn, $user, $password);
        $stmt = $pdo->query($sql);
        $Result = $stmt->fetch(PDO::FETCH_ASSOC);
        $pdo = null;

        return $Result;
    }

    /**
     * Save
     */
    public function Save($request)
    {
        $params = $request->all();

        $model = new クレームデータ();
        $model->fill($params);

        $data = collect($model)->all();

        DB::beginTransaction();
        try {

            $ClaimId = $params['クレームID'];

            if (!isset($ClaimId)) {
                $ClaimId = クレームデータ::query()->max('クレームID') + 1;
            }

            DB::unprepared('SET IDENTITY_INSERT クレームデータ ON');
            DB::table('クレームデータ')->updateOrInsert(
                ['クレームID' => $ClaimId],
                $data
            );
            DB::unprepared('SET IDENTITY_INSERT クレームデータ OFF');

            DB::commit();
        } catch (Exception $exception) {
            DB::rollBack();
            throw $exception;
        }

        $savedData = array_merge(['クレームID' => $params['クレームID']], $data);

        return response()->json([
            'result' => true,
            'model' => $savedData,
        ]);
    }
}
