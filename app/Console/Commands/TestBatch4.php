<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Libs\DataSend;

//社内DBからモバイル・Web受注へ送信(クラステスト用)
class TestBatch4 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'batch:test4';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //下記はSQL送信テスト用の文字列です。
        $sql = "
                    SELECT
                        0 AS No  ,     ----- 行番号
                        T1.ファイル名,
                        T1.ファイル日付 AS ファイル日時,
                        T1.処理日付,
                        M1.銀行名       AS 銀行,
                        M2.支店名       AS 本支店,
                        CASE WHEN 口座種別 = 1 THEN '普通'
                             WHEN 口座種別 = 2 THEN '当座'
                             WHEN 口座種別 = 3 THEN '定期'
                             WHEN 口座種別 = 9 THEN 'その他'
                        END AS 種別,
                        口座番号,
                        振込合計金額,
                        CASE WHEN T2.MEISAI_COUNT = T3.MEISAI_OK_COUNT THEN '◯'
                        ELSE '×'
                        END AS 結果,
                        '' AS 詳細      ----- 詳細ボタン
                    FROM
                        振込見出 T1
                    LEFT OUTER JOIN 金融機関名称     M1 ON (T1.金融機関ＣＤ = M1.銀行CD)
                    LEFT OUTER JOIN 金融機関支店名称 M2 ON (T1.金融機関ＣＤ = M2.銀行CD AND T1.金融機関支店ＣＤ = M2.支店CD)

                    LEFT OUTER JOIN (
                        SELECT 部署CD, ファイル名, COUNT(部署CD) AS MEISAI_COUNT
                        FROM 振込明細
                        WHERE 部署ＣＤ = 101
                        GROUP BY 部署CD, ファイル名
                    ) T2 ON (T1.部署CD = T2.部署CD AND T1.ファイル名 = T2.ファイル名)

                    LEFT OUTER JOIN (
                        SELECT 部署CD, ファイル名, COUNT(部署CD) AS MEISAI_OK_COUNT
                        FROM 振込明細
                        WHERE 部署ＣＤ = 101
                        AND 結果 = 1
                        GROUP BY 部署CD, ファイル名
                    ) T3 ON (T1.部署CD = T3.部署CD AND T1.ファイル名 = T3.ファイル名)

                    WHERE
                        T1.部署ＣＤ = 101
                    AND
                        T1.処理日付 BETWEEN '2019/01/01' AND '2019/12/31'
                    ORDER BY T1.ファイル日付 DESC
                ";

        $ds = new DataSend();
        $ds->Send($sql);
    }
}
