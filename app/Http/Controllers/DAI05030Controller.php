<?php
namespace App\Http\Controllers;

use App\Models\売掛データ;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use DB;
use Illuminate\Support\Carbon;
use PDO;

class DAI05030Controller extends Controller
{
    /**
     * Search
     */
    public function Search($vm)
    {
        $BushoCd = $vm->BushoCd;
        $SeikyuNengetsu = preg_replace('/年|月/','/',$vm->TargetDate);
        $SeikyuHidsuke = $SeikyuNengetsu.'01';

        $sql = "
        SELECT
            日付,
            売掛データ.請求先ＣＤ,
            前月残高,
            今月入金額,
            差引繰越額,
            今月売上額,
            今月残高,
            得意先マスタ.得意先名
        FROM
            [売掛データ]
            INNER JOIN [得意先マスタ] ON
                得意先マスタ.得意先ＣＤ = 売掛データ.請求先ＣＤ
            AND 得意先マスタ.部署ＣＤ = 売掛データ.部署ＣＤ
        WHERE
            日付 = '$SeikyuHidsuke'
        AND 売掛データ.部署ＣＤ = $BushoCd
        order by
            売掛データ.請求先ＣＤ, 日付
        ";

        //$DataList = DB::select($sql);
        $dsn = 'sqlsrv:server=localhost;database=daiyas';
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
        DB::beginTransaction();

        try {
            $params = $request->all();
            $date = Carbon::now()->format('Y-m-d H:i:s');
            $ShuseiTantoCd=$params['ShuseiTantoCd'];
            $SaveList = $params['SaveList'];

            $SeikyuNengetsu = preg_replace('/年|月/','/',$params['TargetDate']);
            $UrikakeHiduke = $SeikyuNengetsu.'01';//売掛日付：請求年月の01日

            //更新実施
            foreach($SaveList as $SaveItem)
            {
                $seikyuCd=$SaveItem['請求先ＣＤ'];
                $kingaku=$SaveItem['今月残高'];

                $sql="
                IF EXISTS (
                    SELECT
                        請求先ＣＤ
                    FROM 売掛データ
                    WHERE
                        日付 = '$UrikakeHiduke'
                    AND 請求先ＣＤ = $seikyuCd
                    AND 今月残高 <> $kingaku
                ) BEGIN
                    UPDATE　売掛データ
                    SET
                        今月残高 = $kingaku,
                        修正担当者ＣＤ = $ShuseiTantoCd,
                        修正日 = '$date'
                    WHERE
                        日付 = '$UrikakeHiduke'
                    AND 請求先ＣＤ = $seikyuCd
                END
                ";
                $result = DB::update($sql);
            }

            DB::commit();

        } catch (Exception $exception) {
            DB::rollBack();
            throw $exception;
        }

        return response()->json([
            'result' => true,
            //'lastupdatedate'=>$this->LastUpdateDateSearch($request),
            //'edited' => $this->Search($request),
        ]);
    }
}
