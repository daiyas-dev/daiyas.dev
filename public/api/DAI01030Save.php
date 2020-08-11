<?php
header('Content-Type: application/json; charset=UTF-8');
$req = count($_REQUEST) > 0 ? $_REQUEST : json_decode(file_get_contents("php://input"), true);

if (!isset($req)) {
    print json_encode([], JSON_PRETTY_PRINT);
   return;
}

$skip = [];

$BushoCd = $req['BushoCd'];
$CustomerCd = $req['CustomerCd'];
$DeliveryDate = $req['DeliveryDate'];

if (!!isset($req['CourseCd'])) {
    $CourseCd = $req['CourseCd'];
}

$SaveList = $req['SaveList'];

$pdo = null;
try {
    $dsn = 'sqlsrv:server=127.0.0.1;database=daiyas';
    $user = 'daiyas';
    $password = 'daiyas';

    $pdo = new PDO($dsn, $user, $password);

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $pdo->beginTransaction();

    date_default_timezone_set('Asia/Tokyo');
    $date = date("Y-m-d H:i:s");
    foreach ($SaveList as $rec) {
        if (isset($rec['修正日']) && !!$rec['修正日']) {
            $SelectSql = "
                SELECT
                    *
                FROM
                    注文データ
                WHERE
                    注文区分=?
                AND 注文日付=?
                AND 部署ＣＤ=?
                AND 得意先ＣＤ=?
                AND 配送日=?
                AND 明細行Ｎｏ=?
            ";

            $stmt = $pdo->prepare($SelectSql);
            $stmt->execute(
                array(
                    $rec['注文区分'],
                    $rec['注文日付'],
                    $rec['部署ＣＤ'],
                    $rec['得意先ＣＤ'],
                    $rec['配送日'],
                    $rec['明細行Ｎｏ']
                )
            );
            $r = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (count($r) != 1) {
                $skip[] = ["target" => $rec, "current" => null];
                continue;
            } elseif ($rec['修正日'] != $r[0]['修正日']) {
                $skip[] = ["target" => $rec, "current" => $r[0]];
                continue;
            }
        }
    }

    $DeleteSql = "
        DELETE 注文データ
        WHERE
            注文区分=?
        AND 注文日付=?
        AND 部署ＣＤ=?
        AND 得意先ＣＤ=?
        AND 配送日=?
    ";
    $stmt = $pdo->prepare($DeleteSql);
    $stmt->execute(
        array(
            $rec['注文区分'],
            $rec['注文日付'],
            $rec['部署ＣＤ'],
            $rec['得意先ＣＤ'],
            $rec['配送日'],
        )
    );

    $no = 0;
    foreach ($SaveList as $rec) {
        $no++;
        if (!!isset($rec['現金個数']) && !!isset($rec['掛売個数'])) {
            $data = $rec;
            $data['修正日'] = $date;
            $data['明細行Ｎｏ'] = $no;
            $data['備考１'] = $data['備考１'] ?? '';
            $data['備考２'] = $data['備考２'] ?? '';
            $data['備考３'] = $data['備考３'] ?? '';
            $data['備考４'] = $data['備考４'] ?? '';
            $data['備考５'] = $data['備考５'] ?? '';
            $data['特記_社内用'] = $data['特記_社内用'] ?? '';
            $data['特記_配送用'] = $data['特記_配送用'] ?? '';
            $data['特記_通知用'] = $data['特記_通知用'] ?? '';

            $InsertSql = "
                INSERT INTO 注文データ
                VALUES (
                     ?
                    ,?
                    ,?
                    ,?
                    ,?
                    ,?
                    ,?
                    ,?
                    ,?
                    ,?
                    ,?
                    ,?
                    ,?
                    ,?
                    ,?
                    ,?
                    ,?
                    ,?
                    ,?
                    ,?
                    ,?
                    ,?
                    ,?
                    ,?
                    ,?
                    ,?
                    ,?
                    ,?
                    ,?
                )
            ";

            $stmt = $pdo->prepare($InsertSql);
            $stmt->execute(
                array(
                    $data['注文区分'],
                    $data['注文日付'],
                    $data['注文時間'],
                    $data['部署ＣＤ'],
                    $data['得意先ＣＤ'],
                    $data['配送日'],
                    $data['明細行Ｎｏ'],
                    $data['商品ＣＤ'],
                    $data['商品区分'],
                    $data['入力区分'],
                    $data['現金個数'],
                    $data['現金金額'],
                    $data['掛売個数'],
                    $data['掛売金額'],
                    $data['備考１'],
                    $data['備考２'],
                    $data['備考３'],
                    $data['備考４'],
                    $data['備考５'],
                    $data['予備金額１'],
                    $data['予備金額２'],
                    $data['予備ＣＤ１'],
                    $data['予備ＣＤ２'],
                    $data['修正担当者ＣＤ'],
                    $data['修正日'],
                    $data['Web受注ID'] ?? null,
                    $data['特記_社内用'],
                    $data['特記_配送用'],
                    $data['特記_通知用']
                )
            );
        }
    }
    if (count($skip) > 0) {
        $pdo->rollBack();
    } else {
        $pdo->commit();
    }

    require("DAI01030SearchFunc.php");

    $Result = [
        'result' => true,
        "edited" => count($skip) > 0,
        "current" => count($skip) > 0 ? Search() : [],
    ];
    print json_encode($Result, JSON_PRETTY_PRINT);

} catch (Exception $e) {
    $pdo->rollBack();
    throw $e;
} finally {
    $pdo = null;
}

