<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use DB;
use Illuminate\Support\Carbon;

class DAI01030Controller extends Controller
{
    /**
     * Search
     */
    public function Search($vm)
    {
        $BushoCd = $vm->BushoCd;
        $CustomerCd = $vm->CustomerCd;
        $DeliveryDate = $vm->DeliveryDate;

        $sql = "
WITH 注文一覧 AS (
SELECT
	MTT.得意先ＣＤ,
	MTT.商品ＣＤ,
	MTT.単価,
	MP.商品名,
	CD.注文区分,
	CD.注文日付,
	CD.注文時間,
	CD.配送日,
	CD.明細行Ｎｏ,
	CD.商品区分,
	CD.入力区分,
	CD.現金個数,
	CD.現金金額,
	CD.掛売個数,
	CD.掛売金額,
	CD.備考１,
	CD.備考２,
	CD.備考３,
	CD.備考４,
	CD.備考５,
	CD.予備ＣＤ１,
	CD.予備金額1,
	CD.修正担当者ＣＤ,
	CD.修正日
FROM
	得意先単価マスタ MTT
	INNER JOIN 商品マスタ MP
		ON MP.商品ＣＤ = MTT.商品ＣＤ
	LEFT OUTER JOIN 注文データ CD
		ON  CD.得意先ＣＤ = MTT.得意先ＣＤ
		AND CD.商品ＣＤ = MTT.商品ＣＤ
		AND	CD.部署ＣＤ = $BushoCd
		AND CD.配送日 = '$DeliveryDate'
WHERE
	MTT.得意先ＣＤ = $CustomerCd
)
SELECT
	得意先ＣＤ,
	商品ＣＤ,
	商品名,
	単価,
	SUM(IIF(注文区分=1, 現金個数 + 掛売個数, 0)) AS 予定数,
	SUM(IIF(注文区分=0, 現金個数, 0)) AS 現金個数,
	SUM(IIF(注文区分=0, 現金金額, 0)) AS 現金金額,
	SUM(IIF(注文区分=0, 掛売個数, 0)) AS 掛売個数,
	SUM(IIF(注文区分=0, 掛売金額, 0)) AS 掛売金額,
	SUM(IIF(注文区分 IS NULL, 1, 0)) AS 全表示
FROM
	注文一覧
GROUP BY
	得意先ＣＤ,
	商品ＣＤ,
	単価,
	商品名
ORDER BY
	商品ＣＤ
        ";

        $DataList = DB::select($sql);

        return response()->json($DataList);
    }

    /**
     * Save
     */
    public function Save($request)
    {
        $params = $request->all();
        $targets = $params['targets'];

        //validation
        validator()->validate($targets, [
            'targets.*.*' => 'required',
        ]);

        //トランザクション開始
        DB::transaction(function () use ($targets) {
            collect($targets)->each(function ($target, $key) {
                $BushoCd = $target['部署CD'];
                $CourseCd = $target['コースＣＤ'];
                $ShohinCd = $target['商品CD'];
                $Amount = $target['個数'];
                $TargetDate = $target['対象日付'];

                $sql = "
IF EXISTS (
	SELECT
		持ち出し日付
	FROM
		モバイル_持ち出し入力
	WHERE
		持ち出し日付 = '$TargetDate'
	AND 部署CD = $BushoCd
	AND コースＣＤ = $CourseCd
	AND 商品ＣＤ = $ShohinCd
)
BEGIN
	UPDATE モバイル_持ち出し入力
	SET
		商品CD = $ShohinCd,
		個数 = $Amount,
		修正日 = GETDATE()
	WHERE
		持ち出し日付 = '$TargetDate'
	AND 部署CD = $BushoCd
	AND コースＣＤ = $CourseCd
	AND 商品ＣＤ = $ShohinCd
END
ELSE
	INSERT INTO モバイル_持ち出し入力
	VALUES ($BushoCd, $CourseCd, '$TargetDate', $ShohinCd, $Amount, GETDATE())
        ";

                DB::statement($sql);
            });
        });

        return response()->json([
            'result' => true,
        ]);
    }
}
