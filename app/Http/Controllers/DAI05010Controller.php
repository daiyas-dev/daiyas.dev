<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use DB;
use Illuminate\Support\Carbon;

class DAI05010Controller extends Controller
{
    public function SearchCustomerPrice($vm)
    {
        $BushoCd = $vm->BushoCd;
        $WhereBushoCd = !!$BushoCd ? " AND コーステーブル.部署ＣＤ=$BushoCd" : "";

        $sql = "
                WITH WITH_コース AS(
                    SELECT
                        　コーステーブル.コースＣＤ
                        , コースマスタ.コース名
                        , ＳＥＱ
                        , コースマスタ.担当者ＣＤ
                        , 担当者マスタ.担当者名
                        , コーステーブル.得意先ＣＤ
                        , 得意先マスタ.得意先名
                        , 得意先マスタ.売掛現金区分
                        , 得意先マスタ.部署ＣＤ
                        , 部署マスタ.部署名
                    FROM
                        [コーステーブル]
                        INNER JOIN [コースマスタ]
                            ON コースマスタ.コースＣＤ = コーステーブル.コースＣＤ
                            AND コースマスタ.部署ＣＤ = コーステーブル.部署ＣＤ
                        INNER JOIN [得意先マスタ]
                            ON 得意先マスタ.得意先ＣＤ = コーステーブル.得意先ＣＤ
                            AND 得意先マスタ.部署ＣＤ = コーステーブル.部署ＣＤ
                        INNER JOIN [部署マスタ]
                            ON 得意先マスタ.部署ＣＤ = 部署マスタ.部署ＣＤ
                        INNER JOIN [担当者マスタ]
                            ON コースマスタ.担当者ＣＤ = 担当者マスタ.担当者ＣＤ
                            AND コースマスタ.部署ＣＤ = 担当者マスタ.所属部署ＣＤ
                    WHERE
                            0=0
                            $WhereBushoCd
                ),
                WITH_得意先単価 AS (
                    SELECT
                      tokui.得意先ＣＤ
                    , syohin.商品ＣＤ
                    , syohin.商品名
                    , RIGHT ('000' + CONVERT(varchar, syohin.商品ＣＤ), 3) + ' ' + syohin.商品名 表示商品名
                    , tokui.単価
                    , syohin.商品区分
                    FROM
                        [得意先単価マスタ] tokui
                        inner join 商品マスタ syohin
                            on tokui.商品ＣＤ = syohin.商品ＣＤ
                )
                SELECT
                     WITH_コース.*
                    ,WITH_得意先単価.商品ＣＤ
                    ,WITH_得意先単価.商品名
                    ,WITH_得意先単価.表示商品名
                    ,WITH_得意先単価.単価
                FROM
                     WITH_コース
                     LEFT JOIN WITH_得意先単価 ON WITH_得意先単価.得意先ＣＤ=WITH_コース.得意先ＣＤ
                ORDER BY
                     WITH_コース.部署ＣＤ
                    ,WITH_コース.コースＣＤ
                    ,WITH_コース.ＳＥＱ
                    ,WITH_コース.得意先ＣＤ
                    ,WITH_得意先単価.商品ＣＤ
            ";

        $DataList = DB::select($sql);
        return $DataList;
    }
    public function SearchCourse($vm)
    {
        $BushoCd = $vm->BushoCd;
        $WhereBushoCd = !!$BushoCd ? " AND コーステーブル.部署ＣＤ=$BushoCd" : "";

        $sql = "
                SELECT
                    　コーステーブル.コースＣＤ
                    , コースマスタ.コース名
                    , ＳＥＱ
                    , コースマスタ.担当者ＣＤ
                    , 担当者マスタ.担当者名
                    , コーステーブル.得意先ＣＤ
                    , 得意先マスタ.得意先名
                    , 得意先マスタ.売掛現金区分
                    , 得意先マスタ.部署ＣＤ
                    , 部署マスタ.部署名
                FROM
                    [コーステーブル]
                    INNER JOIN [コースマスタ]
                        ON コースマスタ.コースＣＤ = コーステーブル.コースＣＤ
                        AND コースマスタ.部署ＣＤ = コーステーブル.部署ＣＤ
                    INNER JOIN [得意先マスタ]
                        ON 得意先マスタ.得意先ＣＤ = コーステーブル.得意先ＣＤ
                        AND 得意先マスタ.部署ＣＤ = コーステーブル.部署ＣＤ
                    INNER JOIN [部署マスタ]
                        ON 得意先マスタ.部署ＣＤ = 部署マスタ.部署ＣＤ
                    INNER JOIN [担当者マスタ]
                        ON コースマスタ.担当者ＣＤ = 担当者マスタ.担当者ＣＤ
                        AND コースマスタ.部署ＣＤ = 担当者マスタ.所属部署ＣＤ
                WHERE
                        0=0
                        $WhereBushoCd
                ORDER BY
                     得意先マスタ.部署ＣＤ
                    ,コーステーブル.コースＣＤ
                    ,コーステーブル.ＳＥＱ
                    ,コーステーブル.得意先ＣＤ
            ";
        $DataList = DB::select($sql);
        return $DataList;
    }

    /**
     * Search
     */
    public function Search($request)
    {
        return response()->json(
            [
                [
                    "CourseData" => $this->SearchCourse($request),
                    "PriceData" => $this->SearchCustomerPrice($request),
                ]
            ]
        );
    }
}
