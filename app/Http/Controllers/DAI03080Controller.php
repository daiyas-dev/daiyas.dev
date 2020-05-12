<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use DB;
use Illuminate\Support\Carbon;
use PDO;

class DAI03080Controller extends Controller
{
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
        $DateStart = $vm->DateStart;
        $DateEnd = $vm->DateEnd;

        //検索条件(部署ＣＤ)
        $BushoArray = $vm->BushoArray;
        $WhereBusho1="";
        if($BushoArray !=null && is_array($BushoArray) && 0<count($BushoArray))
        {
            $BushoList = implode(',',$BushoArray);
            $WhereBusho1=" AND 得意先マスタ.部署ＣＤ IN( $BushoList )";
        }

        $sql = "
        WITH 請求日付_MAX請求データ AS
        (
        SELECT MAX(請求日付) as 請求日付, 部署ＣＤ, 請求先ＣＤ
          FROM 請求データ
         WHERE 部署ＣＤ >= 101
           AND 部署ＣＤ <= 101
           AND 請求日付 >= '2019/09/01'
           AND 請求日付 <= '2019/09/30'
        GROUP BY 部署ＣＤ, 請求先ＣＤ
        )

        SELECT
          SEIKYU.請求先ＣＤ
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
               WHERE 部署ＣＤ >= 101
                 AND 部署ＣＤ <= 101
                 AND 入金日付 >= '2019/09/01'
              GROUP BY 部署ＣＤ,得意先ＣＤ
          ) NYUKIN ON
                SEIKYU.部署ＣＤ   = NYUKIN.部署ＣＤ
            AND SEIKYU.請求先ＣＤ = NYUKIN.得意先ＣＤ
            AND NYUKIN.入金日付   > SEIKYU.請求日付

        WHERE
          SEIKYU.部署ＣＤ >= 101
          AND SEIKYU.部署ＣＤ <= 101
          AND SEIKYU.請求日付 >= '2019/09/01'
          AND SEIKYU.請求日付 <= '2019/09/30'
          AND TOK.支払方法１ = 4
          AND TOK.金融機関CD  = 170

          AND SEIKYU.今回請求額 - ISNULL(入金額, 0) > 0

          AND TOK.締日１ IN(99,15,0)
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
        $BankHeader="";
        $BankData="";
        $BankTrailer="";
        $BankEnd="";

        $param['銀行引落日']="1224";//TODO:ダミーデータ

        $filename=public_path().'\bankformat\BankFormatYamaguchi.xml';
        $xml = simplexml_load_file($filename);

        $DataList=$this->getSeikyuData($request);
        foreach ($DataList as $DataListRow) {
            foreach ($xml->Header->record as $format) {
                $BankHeader .= $this->getDataElement($format, $param, $DataListRow);
            }
            foreach ($xml->Data->record as $format) {
                $BankData .= $this->getDataElement($format, $param, $DataListRow);
            }
            foreach ($xml->Trailer->record as $format) {
                $BankTrailer .= $this->getDataElement($format, $param, $DataListRow);
            }
            foreach ($xml->End->record as $format) {
                $BankEnd .= $this->getDataElement($format, $param, $DataListRow);
            }
        }

        $RetBankData = $BankHeader ."\n". $BankData ."\n". $BankTrailer ."\n". $BankEnd;
        return $RetBankData;
    }

    /**
     * 引落データの要素の文字列(パディング調整済)を取得する
     */
    private function getDataElement($format,$param,$DataListRow)
    {
        $retval="";

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
            $retval=   str_pad($query_value, $padlen, $padvalue, STR_PAD_RIGHT);
        }
        else if ($pad=="r") {
            $retval=   str_pad($query_value, $padlen, $padvalue, STR_PAD_LEFT);
        }
        else{
            $retval=  $query_value;
        }
        return $retval;
    }

    /**
     * 引落データの要素に指定された値を取得する
     */
    private function getBankValue($value,$param,$DataListRow)
    {
        if($value=="Control.G銀行引落日"){
            return $param["銀行引落日"];
        }
        else if (0 === strpos($value, "DB.")){
            $key=str_replace("DB.","",$value);
            return array_key_exists($key,$DataListRow) ? $DataListRow[$key] : "";
        }
        else if (0 === strpos($value, "CONST.")){
            $key=str_replace("CONST.","",$value);
            return 123456;//TODO:ダミーデータ
        }
        else{
            return $value;
        }
    }
}
