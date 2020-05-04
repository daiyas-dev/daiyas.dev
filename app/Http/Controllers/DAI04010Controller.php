<?php

namespace App\Http\Controllers;

use App\Models\管理マスタ;
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

        $model = new 管理マスタ();
        $model->fill($params);

        $data = collect($model)->all();

        DB::beginTransaction();
        try {

            $MngKey = $params['管理ＫＥＹ'];

            if (!isset($MngKey)) {
                $MngKey = 管理マスタ::query()->max('管理ＫＥＹ') + 1;
            }

            $data['会社名'] = $data['会社名'] ?? '';
            $data['会社名カナ'] = $data['会社名カナ'] ?? '';
            $data['郵便番号'] = $data['郵便番号'] ?? '';
            $data['住所'] = $data['住所'] ?? '';
            $data['電話番号'] = $data['電話番号'] ?? '';
            $data['ＦＡＸ'] = $data['ＦＡＸ'] ?? '';
            $data['代表取締役'] = $data['代表取締役'] ?? '';

            DB::table('管理マスタ')->updateOrInsert(
                ['管理ＫＥＹ' => $MngKey],
                $data
            );

            DB::commit();
        } catch (Exception $exception) {
            DB::rollBack();
            throw $exception;
        }

        $savedData = array_merge(['管理マスタ' => $params['管理ＫＥＹ']], $data);

        return response()->json([
            'result' => true,
            'model' => $savedData,
        ]);
    }
}
