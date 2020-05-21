<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use DB;
use Exception;
use Illuminate\Support\Carbon;
use PDO;

class DAI05071Controller extends Controller
{

    /**
     * Search
     */
    public function Search($request)
    {
        $BushoCd = $request->BushoCd;
        $FurikomiFileName = $request->FurikomiFileName;
        $sql = "
                    SELECT
                        　振込明細.*
                        , 得意先マスタ.得意先名
                    FROM 振込明細
                         LEFT JOIN 得意先マスタ ON 得意先マスタ.得意先ＣＤ = 振込明細.得意先ＣＤ
                    WHERE 振込明細.部署ＣＤ   = $BushoCd
                      AND 振込明細.ファイル名 = '$FurikomiFileName'
                ";

        $dsn = 'sqlsrv:server=127.0.0.1;database=daiyas';
        $user = 'daiyas';
        $password = 'daiyas';

        $pdo = new PDO($dsn, $user, $password);
        $stmt = $pdo->query($sql);
        $DataList = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $pdo = null;
        return response()->json($DataList);
    }
    /**
     * Save
     */
    public function Save($request)
    {
        $params = $request->all();

        DB::beginTransaction();
        try {
            /*
            $date = Carbon::now()->format('Y-m-d H:i:s');
            $AdjustmentID=$params['AdjustmentID'];
            $CustomerCd=$params['CustomerCd'];
            $AdjustmentDate=$params['AdjustmentDate'];
            $AdjustmentReason=$params['AdjustmentReason'];
            $ProductCd=$params['ProductCd'];
            $TicketZanSa=$params['TicketZanSa'];
            $SVTicketZanSa=$params['SVTicketZanSa'];
            $Kingaku=$params['Kingaku'];
            $ShuseiTantoCd=$params['ShuseiTantoCd'];
            */

            $BushoCd=$params['BushoCd'];
            $FurikomiFileName = $params['FurikomiFileName'];

            //振込見出テーブルの更新
            $sql = " SELECT 1
                        FROM 振込見出
                        WHERE 部署ＣＤ = $BushoCd
                        AND ファイル名 = '$FurikomiFileName'
                    ";
            $ExistsMidashi = DB::selectOne($sql);

            //TODO:ひとまずここまで実行させる
            return response()->json([
                "result" => true,
            ]);

            if ($ExistsMidashi==null) {
                $InsSql = "
                        INSERT INTO 振込見出(
                            部署ＣＤ,
                            ファイル名,
                            処理日付,
                            ファイル日付,
                            金融機関ＣＤ,
                            金融機関支店ＣＤ,
                            口座種別,
                            口座番号,
                            振込合計金額,
                            修正担当者ＣＤ,
                            修正日
                        ) VALUES (
                            $BushoCd,
                            '$FurikomiFileName',
                            @処理日付,
                            @ファイル日付,
                            @金融機関ＣＤ,
                            @金融機関支店ＣＤ,
                            @口座種別,
                            @口座番号,
                            @振込合計金額,
                            @修正担当者ＣＤ,
                            CURRENT_TIMESTAMP
                        )
                    ";
                DB::insert($InsSql);
            }
            else{
                $UpdSql = "
                            UPDATE
                                振込見出
                            SET
                                処理日付         = @処理日付,
                                ファイル日付     = @ファイル日付,
                                金融機関ＣＤ     = @金融機関ＣＤ,
                                金融機関支店ＣＤ = @金融機関支店ＣＤ,
                                口座種別         = @口座種別,
                                口座番号         = @口座番号,
                                振込合計金額      = @振込合計金額,
                                修正担当者ＣＤ    = @修正担当者ＣＤ,
                                修正日           = CURRENT_TIMESTAMP
                            WHERE 部署ＣＤ       = $BushoCd
                                AND ファイル名   = '$FurikomiFileName'
                          ";
                DB::update($UpdSql);
            }

            //振込明細テーブルの更新

            //得意先振込依頼人の登録処理

            //入金データのインサート処理

            DB::commit();
        } catch (Exception $exception) {
            DB::rollBack();
            throw $exception;
        }

        return response()->json([
            "result" => true,
        ]);
    }
}
