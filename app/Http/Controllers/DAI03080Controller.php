<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use DB;
use Illuminate\Support\Carbon;
use PDO;
class DAI03080Controller extends Controller
{
    //出力区分(金融機関)の指定
    const BANK_YAMAGUCHI = '0';
    const BANK_TOKYO_MITSUBISHI_UFJ = '1';

    private $DataListCount=0;
    private $Seikyukingaku=0;

    /**
     * 前回請求日を取得する
     */
    public function LatestSeikyuDateGet($request)
    {
        $BushoCd=$request->BushoCd;
        $sql = "
                SELECT
                FORMAT(MAX(SEIKYU.請求日付),'yyyy/MM/dd') AS 前回請求日
                FROM
                    請求データ SEIKYU
                WHERE
                    SEIKYU.部署ＣＤ = $BushoCd;
            ";
        $Result = DB::selectOne($sql);
        if($Result == null){
            $date = new Carbon();
            $Result = new \stdClass();
            $Result->前回請求日=$date->format('Y/m/d');
        }
        return response()->json($Result);
    }
    /**
     * 各種テーブルから銀行引落日を取得する
     */
    public function WithdrawalDateGet()
    {
        $sql = "
            SELECT
                サブ各種CD1 AS 銀行引落日
            FROM
                各種テーブル
            WHERE 各種CD=29
              AND 行NO=1
            ";
        $Result = DB::selectOne($sql);
        if($Result == null){
            $Result = new \stdClass();
            $Result->銀行引落日="1";
        }
        return response()->json($Result);
    }
    /**
     * Search
     */
    public function Search($vm)
    {
        $DataList=$this->getSeikyuData($vm);
        return response()->json($DataList);
    }
    /**
     * 請求データを検索する
     */
    private function getSeikyuData($vm)
    {
        $StartDate=$vm->StartDate;
        $EndDate=$vm->EndDate;

        //検索条件(部署ＣＤ)
        $BushoArray = $vm->BushoArray;
        $WhereBusho1="";
        $WhereBusho2="";
        $WhereBusho3="";
        if($BushoArray !=null && is_array($BushoArray) && 0<count($BushoArray))
        {
            $BushoList = implode(',',$BushoArray);
            $WhereBusho1 = " AND 請求データ.部署ＣＤ IN( $BushoList )";
            $WhereBusho2 = " AND 入金データ.部署ＣＤ IN( $BushoList )";
            $WhereBusho3 = " AND SEIKYU.部署ＣＤ IN( $BushoList )";
        }

        //検索条件(金融機関)
        $BankFormat=$vm->BankFormat;
        $WhereBank = $BankFormat==self::BANK_YAMAGUCHI ? " = 170" : " <> 170";

        //検索条件(締日)
        $Simebi1 = $vm->Simebi1 != null ? $vm->Simebi1 : "0";
        $Simebi2 = $vm->Simebi2 != null ? $vm->Simebi2 : "0";
        $Simebi3 = $vm->Simebi3 != null ? $vm->Simebi3 : "0";

        $sql = "
                WITH 請求日付_MAX請求データ AS
                (
                    SELECT MAX(請求日付) as 請求日付, 部署ＣＤ, 請求先ＣＤ
                    FROM 請求データ
                    WHERE 0=0
                        $WhereBusho1
                        AND 請求日付 >= '$StartDate'
                        AND 請求日付 <= '$EndDate'
                    GROUP BY 部署ＣＤ, 請求先ＣＤ
                )

                SELECT
                    SEIKYU.請求先ＣＤ
                    ,SEIKYU.部署ＣＤ
                    ,SEIKYU.今回請求額 - ISNULL(入金額, 0) AS 今回請求額
                    ,TOK.金融機関CD AS 引落銀行番号
                    ,KINYU.銀行名カナ AS 引落銀行名
                    ,TOK.金融機関支店CD AS 引落支店番号
                    ,KINYU_SHITEN.支店名カナ AS 引落支店名
                    ,TOK.口座種別 AS 預金種目
                    ,TOK.口座番号
                    ,TOK.口座名義人 AS 預金者名
                    ,SEIKYU.請求先ＣＤ AS 顧客番号
                    ,TOK.得意先名
                FROM
                    請求データ SEIKYU
                    INNER JOIN 請求日付_MAX請求データ SEIKYUMAX ON SEIKYUMAX.請求日付   = SEIKYU.請求日付
                                                                AND SEIKYUMAX.部署ＣＤ   = SEIKYU.部署ＣＤ
                                                                AND SEIKYUMAX.請求先ＣＤ = SEIKYU.請求先ＣＤ
                    LEFT OUTER JOIN 得意先マスタ TOK ON
                        SEIKYU.請求先ＣＤ = TOK.得意先ＣＤ
                    LEFT OUTER JOIN 金融機関名称 KINYU ON
                        KINYU.銀行CD = TOK.金融機関CD
                    LEFT OUTER JOIN 金融機関支店名称 KINYU_SHITEN ON
                        KINYU_SHITEN.銀行CD = TOK.金融機関CD
                        AND KINYU_SHITEN.支店CD = TOK.金融機関支店CD
                    LEFT OUTER JOIN
                    (
                        SELECT 部署ＣＤ,
                                得意先ＣＤ,
                                MAX(入金日付) AS 入金日付,
                                SUM(ISNULL(現金,0) + ISNULL(小切手,0) + ISNULL(振込,0) + ISNULL(バークレー,0) + ISNULL(その他,0) + ISNULL(相殺,0) + ISNULL(値引,0)) AS 入金額
                        FROM 入金データ
                        WHERE 0=0
                            $WhereBusho2
                            AND 入金日付 >= '$StartDate'
                        GROUP BY 部署ＣＤ,得意先ＣＤ
                    ) NYUKIN ON
                            SEIKYU.部署ＣＤ   = NYUKIN.部署ＣＤ
                        AND SEIKYU.請求先ＣＤ = NYUKIN.得意先ＣＤ
                        AND NYUKIN.入金日付   > SEIKYU.請求日付

                WHERE
                    0=0
                    $WhereBusho3
                    AND SEIKYU.請求日付 >= '$StartDate'
                    AND SEIKYU.請求日付 <= '$EndDate'
                    AND TOK.支払方法１ = 4
                    AND TOK.金融機関CD $WhereBank
                    AND SEIKYU.今回請求額 - ISNULL(入金額, 0) > 0
                    AND TOK.締日１ IN($Simebi1 , $Simebi2 , $Simebi3)
                    AND TOK.得意先ＣＤ = TOK.請求先ＣＤ
                ORDER BY
                    SEIKYU.部署ＣＤ,SEIKYU.請求先ＣＤ
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
     * 引落データファイルをダウンロードする
     */
    public function FileDownload($request)
    {
        //パラメータの取得
        $param['出力区分']=$request->BankFormat;
        $param['口座寄せ']=$request->IsKouzaYose;

        //銀行引落日の取得
        $date = Carbon::parse($request->WithdrawalDate);
        $param['銀行引落日']=$date->format('md');//月日(各2桁)

        //書式定義ファイルの読み込み
        switch($param['出力区分'])
        {
            case self::BANK_YAMAGUCHI:
            {
                //山口銀行
                $BankFormatFile='BankFormatYamaguchi.xml';
                break;
            }
            case self::BANK_TOKYO_MITSUBISHI_UFJ:
            {
                //三菱UFJ銀行
                $BankFormatFile='BankFormatmitubishi.xml';
                break;
            }
            default:
            {
                return "";
            }
        }

        //書式定義ファイルの読み込み
        $Filename=public_path() . '\\bankformat\\' . $BankFormatFile;
        $xml = simplexml_load_file($Filename);

        $BankHeader="";
        $BankData="";
        $BankTrailer="";
        $BankEnd="";

        foreach ($xml->Header->record as $format) {
            $BankHeader .= $this->getDataElement($format, $param, null);
        }
        $BankHeader .= "\r\n";

        $DataList=$this->getSeikyuData($request);
        if($param['口座寄せ']=="1"){
            //口座寄せをする
            $DataList=self::KouzaYose($xml->Data,$DataList);
        }
        $this->DataListCount=count($DataList);
        foreach ($DataList as $DataListRow) {
            foreach ($xml->Data->record as $format) {
                $BankData .= $this->getDataElement($format, $param, $DataListRow);
            }
            $BankData .= "\r\n";
        }
        foreach ($xml->Trailer->record as $format) {
            $BankTrailer .= $this->getDataElement($format, $param, null);
        }
        $BankTrailer .= "\r\n";

        foreach ($xml->End->record as $format) {
            $BankEnd .= $this->getDataElement($format, $param, null);
        }
        $BankEnd .= "\r\n";

        $RetBankData = $BankHeader . $BankData . $BankTrailer . $BankEnd;
        return mb_convert_encoding($RetBankData, "SJIS", "UTF-8");
    }
    /*
    * 口座寄せ処理
    * 同一の支店、口座の請求額を合算して1レコードに集計する。
    */
    private function KouzaYose($format,$DataList)
    {
        $DataListMarge=array();
        //書式定義ファイルより、引落支店番号、口座番号の文字長を取得する
        foreach ($format->record as $FormatRow) {
            if ($FormatRow['id']=='HikiotoshiBankCd') {
                $LenGinkoNo=$FormatRow['character'];
            }
            elseif ($FormatRow['id']=='HikiotoshiShitenBankCd') {
                $LenSitenNo=$FormatRow['character'];
            }
            elseif ($FormatRow['id']=='KozaNumber') {
                $LenKouzaNo=$FormatRow['character'];
            }
        }

        foreach($DataList as $DataListRow)
        {
            $key = str_pad($DataListRow['引落銀行番号'], (int)$LenGinkoNo, "0", STR_PAD_LEFT)
                 . str_pad($DataListRow['引落支店番号'], (int)$LenSitenNo, "0", STR_PAD_LEFT)
                 . str_pad($DataListRow['口座番号'], (int)$LenKouzaNo, "0", STR_PAD_LEFT);
            if(array_key_exists($key,$DataListMarge)){
                $DataListMarge[$key]['今回請求額'] += $DataListRow['今回請求額'];
            }
            else{
                $DataListMarge[$key]['請求先ＣＤ']=$DataListRow['請求先ＣＤ'];
                $DataListMarge[$key]['得意先名']=$DataListRow['得意先名'];
                $DataListMarge[$key]['今回請求額'] = $DataListRow['今回請求額'];
                $DataListMarge[$key]['引落銀行番号']=$DataListRow['引落銀行番号'];
                $DataListMarge[$key]['引落銀行名']=$DataListRow['引落銀行名'];
                $DataListMarge[$key]['引落支店番号']=$DataListRow['引落支店番号'];
                $DataListMarge[$key]['引落支店名']=$DataListRow['引落支店名'];
                $DataListMarge[$key]['預金種目']=$DataListRow['預金種目'];
                $DataListMarge[$key]['口座番号']=$DataListRow['口座番号'];
                $DataListMarge[$key]['預金者名']=$DataListRow['預金者名'];
                $DataListMarge[$key]['顧客番号']=$DataListRow['顧客番号'];
            }
        }
        return $DataListMarge;
    }

    /**
     * 引落データの要素の文字列(パディング調整済)を取得する
     * 引数：項目の定義内容(全件)
     * 引数：画面からの呼び出し時に渡されたパラメータ
     * 引数：SQLで取得した引落データ(1件)
     */
    private function getDataElement($format,$param,$DataListRow)
    {
        $retval="";

        //書式の取得
        $character=$format['character'];//長さ
        $value=$format['value'];//値
        $pad=$format['pad'];//パディング
        $padvalue= $format['padvalue'] == "s" ? " " : $format['padvalue'];//パディング時に埋める文字。sなら半角スペース

        //検索した値に置換
        $query_value=$this->getBankValue($value,$param,$DataListRow);

        //パディング
        $rpstr=preg_replace("/ |\)|[A-Z]|[a-z]|[0-9]/","",$query_value);
        $padlen=(int)$character + mb_strlen($rpstr)*2;//半角カナは3バイトなので、指定の$character数 + 半角カナ文字数*2の文字数を求める。
        if ($pad=="l") {
            $retval = str_pad($query_value, $padlen, $padvalue, STR_PAD_RIGHT);
        }
        else if ($pad=="r") {
            $retval = str_pad($query_value, $padlen, $padvalue, STR_PAD_LEFT);
        }
        else{
            $retval = $query_value;
        }
        return $retval;
    }

    /**
     * 引落データの要素に指定された値を取得する
     * 引数：項目の定義内容(1件)
     * 引数：画面からの呼び出し時に渡されたパラメータ
     * 引数：SQLで取得した引落データ(1件)
     */
    private function getBankValue($value,$param,$DataListRow)
    {
        if($value=="Control.G銀行引落日"){
            return $param["銀行引落日"];
        }
        else if (0 === strpos($value, "DB.")){
            $key=str_replace("DB.","",$value);
            if($key=="今回請求額"){
                $this->Seikyukingaku += array_key_exists($key,$DataListRow) ? $DataListRow[$key] : 0;
            }
            return array_key_exists($key,$DataListRow) ? $DataListRow[$key] : "";
        }
        else if (0 === strpos($value, "CONST.")){
            if (0 === strpos($value, "CONST.GKENSU")){
                return $this->DataListCount;
            }
            else if (0 === strpos($value, "CONST.GMONEY")){
                return $this->Seikyukingaku;
            }
            else{
                return "";
            }
        }
        else{
            return $value;
        }
    }
}
