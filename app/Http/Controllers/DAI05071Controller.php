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
    public function GetBushoInfo($request)
    {
        $BushoCd = $request->BushoCd;
        $DataList=$this->GetBushoInfoExec($BushoCd);
        return response()->json($DataList);
    }
    private function GetBushoInfoExec($BushoCd)
    {
        $sql = "
                    SELECT
                        M1.部署名,
                        M2.銀行名,
                        M3.支店名,
                        M1.口座番号1 AS 口座番号,
                        M1.口座名義人1 AS 口座名義人,
                        M1.金融機関CD1 AS 金融機関CD,
                        M1.金融機関支店CD1 AS 金融機関支店CD,
                        M1.口座種別1 AS 口座種別CD,
                        CASE WHEN 口座種別1 = 1 THEN '普通'
                            WHEN 口座種別1 = 2 THEN '当座'
                            WHEN 口座種別1 = 3 THEN '定期'
                            WHEN 口座種別1 = 9 THEN 'その他'
                        END AS 種別
                    FROM  部署マスタ M1
                        LEFT OUTER JOIN 金融機関名称     M2 ON (M1.金融機関ＣＤ1 = M2.銀行CD)
                        LEFT OUTER JOIN 金融機関支店名称 M3 ON (M1.金融機関ＣＤ1 = M3.銀行CD AND M1.金融機関支店ＣＤ1 = M3.支店CD)
                    WHERE 部署CD = $BushoCd
                ";
        $dsn = 'sqlsrv:server=127.0.0.1;database=daiyas';
        $user = 'daiyas';
        $password = 'daiyas';
        $pdo = new PDO($dsn, $user, $password);
        $stmt = $pdo->query($sql);
        $DataList = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $pdo = null;
        return $DataList;

    }
    /**
     * Search
     */
    public function Search($request)
    {
        $BushoCd = $request->BushoCd;
        $FurikomiFileName = $request->FurikomiFileName;
        $sql = "
                    SELECT
                          振込明細.部署ＣＤ
                        , 振込明細.ファイル名
                        , 振込明細.レコード番号
                        , 振込明細.店番
                        , 振込明細.取引店
                        , 振込明細.全銀科目コード
                        , 振込明細.預金種類コード
                        , 振込明細.預金種類
                        , 振込明細.口座番号
                        , 振込明細.口座名義
                        , 振込明細.照会期間
                        , 振込明細.照会方法
                        , 振込明細.操作日
                        , 振込明細.操作時刻
                        , 振込明細.取引日
                        , 振込明細.指定日
                        , 振込明細.取引区分
                        , 振込明細.依頼人名
                        , 振込明細.金融機関名
                        , 振込明細.支店名
                        , 振込明細.EDI情報
                        , 振込明細.入金金額
                        , 振込明細.振込手数料
                        , 振込明細.得意先ＣＤ
                        , CASE 振込明細.依頼人登録区分 WHEN 1 THEN 'true' ELSE 'false'END AS F依頼人登録区分
                        , CASE 振込明細.入金登録区分 WHEN 1 THEN 'true' ELSE 'false'END AS F入金登録区分
                        , 振込明細.入金日
                        , 振込明細.入金伝票Ｎｏ
                        , 振込明細.結果
                        , 振込明細.修正担当者ＣＤ
                        , 振込明細.修正日
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
            */
            $FurikomiList=$params['FurikomiList'];
            $BushoInfo=$this->GetBushoInfoExec($params['BushoCd'])[0];
            $RecordNo=0;
            foreach($FurikomiList as $SaveItem)
            {
                if($SaveItem['入金登録区分']==true)
                {
                    $this->SaveMidashi($params,$BushoInfo);
                    $this->SaveMeisai($params,$BushoInfo,$RecordNo,$SaveItem);
                    if($SaveItem['依頼人登録区分']==true)
                    {
                        $this->SaveIrainin($params,$SaveItem);
                    }
                    $this->SaveNyukin($params,$SaveItem);
                }
                $RecordNo++;
            }
            DB::commit();
        } catch (Exception $exception) {
            DB::rollBack();
            throw $exception;
        }

        return response()->json([
            "result" => true,
        ]);
    }
    /**
     * Save(振込見出テーブルの更新)
     */
    private function SaveMidashi($params,$BushoInfo)
    {
        //パラメータの取得
        $BushoCd=$params['BushoCd'];
        $TargetDate=$params['TargetDate'];
        $FurikomiFileName = $params['FurikomiFileName'];
        $FurikomiFileDate = $params['FurikomiFileDate'];
        $FurikomiKingaku = $params['FurikomiKingaku'];
        $ShuseiTantoCd = $params['ShuseiTantoCd'];

        $Busho_BankCd=$BushoInfo['金融機関CD'];
        $Busho_SitenCd=$BushoInfo['金融機関支店CD'];
        $Busho_KouzaSyubetuCd=$BushoInfo['口座種別CD'];
        $Busho_KouzaBango=$BushoInfo['口座番号'];

        $sql = " SELECT 1
                    FROM 振込見出
                    WHERE 部署ＣＤ = $BushoCd
                    AND ファイル名 = '$FurikomiFileName'
                ";
        $ExistsMidashi = DB::selectOne($sql);
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
                        '$TargetDate',
                        '$FurikomiFileDate',
                        '$Busho_BankCd',
                        '$Busho_SitenCd',
                        '$Busho_KouzaSyubetuCd',
                        '$Busho_KouzaBango',
                        '$FurikomiKingaku',
                        '$ShuseiTantoCd',
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
                            処理日付         = '$TargetDate',
                            ファイル日付     = '$FurikomiFileDate',
                            金融機関ＣＤ     = '$Busho_BankCd',
                            金融機関支店ＣＤ = '$Busho_SitenCd',
                            口座種別        = '$Busho_KouzaSyubetuCd',
                            口座番号         = '$Busho_KouzaBango',
                            振込合計金額      = '$FurikomiKingaku',
                            修正担当者ＣＤ    = '$ShuseiTantoCd',
                            修正日           = CURRENT_TIMESTAMP
                        WHERE 部署ＣＤ       = $BushoCd
                            AND ファイル名   = '$FurikomiFileName'
                    ";
            DB::update($UpdSql);
        }
    }
    /**
     * Save(振込明細テーブルの更新)
     */
    private function SaveMeisai($params,$BushoInfo,$RecordNo,$SaveItem)
    {
        //パラメータの取得
        $BushoCd=$params['BushoCd'];
        $FurikomiFileName = $params['FurikomiFileName'];
        $ShuseiTantoCd = $params['ShuseiTantoCd'];
/*
        $TargetDate=$params['TargetDate'];
        $FurikomiFileDate = $params['FurikomiFileDate'];
        $FurikomiKingaku = $params['FurikomiKingaku'];
*/
        $Busho_SitenNm=$BushoInfo['支店名'];
/*
        $Busho_BankCd=$BushoInfo['金融機関CD'];
        $Busho_SitenCd=$BushoInfo['金融機関支店CD'];
        $Busho_KouzaSyubetuCd=$BushoInfo['口座種別CD'];
        $Busho_KouzaBango=$BushoInfo['口座番号'];
        $Busho_KouzaMeiginin=$BushoInfo['口座名義人'];
*/
        $ZenginKamokuCd=$SaveItem['全銀科目コード'];
        $YokinSyuruiCd=$SaveItem['預金種類コード'];
        $YokinSyuruiNm=$SaveItem['預金種類'];
        $KouzaBango=$SaveItem['口座番号'];
        $KouzaMeiginin=$SaveItem['口座名義'];
        $SyoukaiKikan=$SaveItem['照会期間'];
        $SyoukaiHouhou=$SaveItem['照会方法'];
        $TorihikiBi=$SaveItem['取引日'];
        $SiteiBi=$SaveItem['指定日'];
        $TorihikiKubun=$SaveItem['取引区分'];
        $IraininNm=$SaveItem['依頼人名'];
        $KinyukikanNm=$SaveItem['金融機関名'];
        $SitenCd=$SaveItem['店番'];
        $SitenNm=$SaveItem['支店名'];
        $EDIInfo=$SaveItem['EDI情報'];
        $NyukinKingaku=$SaveItem['入金金額'];
        $FurikomiTesuryo=$SaveItem['振込手数料'];
        $CustomerCd=$SaveItem['得意先ＣＤ'];
        $IraininTourokuKubun=$SaveItem['依頼人登録区分'];
        $NyukinTourokuKubun=$SaveItem['入金登録区分'];
        $NyukinBi=$SaveItem['入金日'];
        $NyukinDenpyoBango=$SaveItem['入金伝票Ｎｏ'];
        $Kekka=$SaveItem['結果'];

        $sql = " SELECT 1
                    FROM 振込明細
                    WHERE 部署ＣＤ   = $BushoCd
                        AND ファイル名 = '$FurikomiFileName'
                        AND レコード番号 = $RecordNo
                ";
        $ExistsMeisai = DB::selectOne($sql);
        if ($ExistsMeisai==null) {
        $InsSql = "
                INSERT INTO 振込明細(
                    部署ＣＤ,
                    ファイル名,
                    レコード番号,
                    店番,
                    取引店,
                    全銀科目コード,
                    預金種類コード,
                    預金種類,
                    口座番号,
                    口座名義,
                    照会期間,
                    照会方法,
                    操作日,
                    操作時刻,
                    取引日,
                    指定日,
                    取引区分,
                    依頼人名,
                    金融機関名,
                    支店名,
                    EDI情報,
                    入金金額,
                    振込手数料,
                    得意先ＣＤ,
                    依頼人登録区分,
                    入金登録区分,
                    入金日,
                    入金伝票Ｎｏ,
                    結果,
                    修正担当者ＣＤ,
                    修正日
                ) VALUES (
                    $BushoCd,
                    '$FurikomiFileName',
                    $RecordNo,
                    '$SitenCd',
                    '$Busho_SitenNm',
                    '$ZenginKamokuCd',
                    '$YokinSyuruiCd',
                    '$YokinSyuruiNm',
                    '$KouzaBango',
                    '$KouzaMeiginin',
                    '$SyoukaiKikan',
                    '$SyoukaiHouhou',
                    CURRENT_TIMESTAMP,
                    CURRENT_TIMESTAMP,
                    '$TorihikiBi',
                    '$SiteiBi',
                    '$TorihikiKubun',
                    '$IraininNm',
                    '$KinyukikanNm',
                    '$SitenNm',
                    '$EDIInfo',
                    '$NyukinKingaku',
                    '$FurikomiTesuryo',
                    '$CustomerCd',
                    '$IraininTourokuKubun',
                    '$NyukinTourokuKubun',
                    '$NyukinBi',
                    '$NyukinDenpyoBango',
                    '$Kekka',
                    '$ShuseiTantoCd',
                    CURRENT_TIMESTAMP
                )
            ";
            DB::insert($InsSql);
        }
        else{
            $UpdSql = "
                    UPDATE
                    振込明細
                SET
                    店番             = '$SitenCd',
                    取引店           = '$Busho_SitenNm',
                    全銀科目コード   = '$ZenginKamokuCd',
                    預金種類コード   = '$YokinSyuruiCd',
                    預金種類         = '$YokinSyuruiNm',
                    口座番号         = '$KouzaBango',
                    口座名義         = '$KouzaMeiginin',
                    照会期間         = '$SyoukaiKikan',
                    照会方法         = '$SyoukaiHouhou',
                    操作日           = CURRENT_TIMESTAMP,
                    操作時刻         = CURRENT_TIMESTAMP,
                    取引日           = '$TorihikiBi',
                    指定日           = '$SiteiBi',
                    取引区分         = '$TorihikiKubun',
                    依頼人名         = '$IraininNm',
                    金融機関名       = '$KinyukikanNm',
                    支店名           = '$SitenNm',
                    EDI情報          = '$EDIInfo',
                    入金金額         = '$NyukinKingaku',
                    振込手数料       = '$FurikomiTesuryo',
                    得意先ＣＤ       = '$CustomerCd',
                    依頼人登録区分   = '$IraininTourokuKubun',
                    入金登録区分     = '$NyukinTourokuKubun',
                    入金日           = '$NyukinBi',
                    入金伝票Ｎｏ     = '$NyukinDenpyoBango',
                    結果             = '$Kekka',
                    修正担当者ＣＤ   = '$ShuseiTantoCd',
                    修正日           = CURRENT_TIMESTAMP
                WHERE 部署ＣＤ       = $BushoCd
                    AND ファイル名   = '$FurikomiFileName'
                    AND レコード番号 = $RecordNo
                ";
            DB::update($UpdSql);
        }
    }
    /**
     * Save(得意先振込依頼人の更新)
     */
    private function SaveIrainin($params,$SaveItem)
    {
        //パラメータの取得
        $ShuseiTantoCd = $params['ShuseiTantoCd'];

        $CustomerCd=$SaveItem['得意先ＣＤ'];
        $IraininNm=$SaveItem['依頼人名'];

        $sql = " SELECT 1
                    FROM 得意先振込依頼人名
                    WHERE 得意先ＣＤ   = $CustomerCd
                    AND 振込依頼人名 = '$IraininNm'
                ";
        $ExistsFurikominin = DB::selectOne($sql);
        if ($ExistsFurikominin==null) {
            $InsSql = "
                        INSERT INTO 得意先振込依頼人名(
                            依頼人ID,
                            得意先ＣＤ,
                            振込依頼人名,
                            修正担当者ＣＤ,
                            修正日
                        ) VALUES (
                            ( SELECT ISNULL(依頼人ID,0) + 1 FROM (SELECT MAX(依頼人ID) AS 依頼人ID FROM 得意先振込依頼人名 ) 依頼人ID ) ,
                            $CustomerCd,
                            '$IraininNm',
                            $ShuseiTantoCd,
                            CURRENT_TIMESTAMP
                        )
            ";
            DB::insert($InsSql);
        }
    }
    /**
     * Save(入金データのインサート処理)
     */
    private function SaveNyukin($params,$SaveItem)
    {
        //パラメータの取得
        $ShuseiTantoCd = $params['ShuseiTantoCd'];

        $NyukinBi=$SaveItem['入金日'];
        $NyukinDenpyoBango=$SaveItem['入金伝票Ｎｏ'];
        $CustomerCd=$SaveItem['得意先ＣＤ'];
        $NyukinKubun='2';
        $Genkin='0';
        $Kogitte='0';
        $Furikomi=$SaveItem['入金金額'];
        $Barkrey='0';
        $Sonota='0';
        $Sousai=$SaveItem['振込手数料'];
        $Nebiki='0';
        $Tekiyo='';
        $Biko='';
        $SeikyuHizuke='';
        $YobiKingaku1='0';
        $YobiKingaku2='0';
        $YobiCD1='0';
        $YobiCD2='0';

        $sql = " SELECT 1
                    FROM 入金データ
                    WHERE 伝票Ｎｏ = $NyukinDenpyoBango
                ";
        $ExistsFurikominin = DB::selectOne($sql);
        if ($ExistsFurikominin==null) {
            $InsSql = "
                        INSERT INTO 入金データ(
                            入金日付,
                            部署ＣＤ,
                            伝票Ｎｏ,
                            得意先ＣＤ,
                            入金区分,
                            現金,
                            小切手,
                            振込,
                            バークレー,
                            その他,
                            相殺,
                            値引,
                            摘要,
                            備考,
                            請求日付,
                            予備金額１,
                            予備金額２,
                            予備ＣＤ１,
                            予備ＣＤ２,
                            修正担当者ＣＤ,
                            修正日
                        ) VALUES (
                            '$NyukinBi',
                            (SELECT 部署ＣＤ FROM 得意先マスタ WHERE 得意先ＣＤ = $CustomerCd),
                            $NyukinDenpyoBango,
                            $CustomerCd,
                            $NyukinKubun,
                            $Genkin,
                            $Kogitte,
                            $Furikomi,
                            $Barkrey,
                            $Sonota,
                            $Sousai,
                            $Nebiki,
                            '$Tekiyo',
                            '$Biko',
                            '$SeikyuHizuke',
                            $YobiKingaku1,
                            $YobiKingaku2,
                            $YobiCD1,
                            $YobiCD2,
                            $ShuseiTantoCd,
                            CURRENT_TIMESTAMP
                        )
                    ";
            DB::insert($InsSql);
        }
        else{
            $UpdSql = "
                        UPDATE
                            入金データ
                        SET
                            振込          = $Furikomi,
                            相殺          = $Sousai,
                            得意先ＣＤ     = $CustomerCd,
                            修正担当者ＣＤ = $ShuseiTantoCd,
                            修正日         = CURRENT_TIMESTAMP
                        WHERE 伝票Ｎｏ    = $NyukinDenpyoBango
                        ";
            DB::update($UpdSql);
        }
    }
}
