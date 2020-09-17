<?php

header('Content-Type: application/json; charset=UTF-8');
$req = count($_REQUEST) > 0 ? $_REQUEST : json_decode(file_get_contents("php://input"), true);

if (!isset($req)) {
    print json_encode([], JSON_PRETTY_PRINT);
    return;
}

$BushoCd = $req['BushoCd'];
$CustomerCd = $req['CustomerCd'];
$DeliveryDate = $req['DeliveryDate'];

if (!isset($CustomerCd) || !is_numeric($CustomerCd) || !ctype_digit($CustomerCd)) {
    print json_encode([], JSON_PRETTY_PRINT);
    return;
}

$dsn = 'sqlsrv:server=127.0.0.1;database=daiyas';
$user = 'daiyas';
$password = 'daiyas';

$pdo = new PDO($dsn, $user, $password);
//修正担当者CDを取得
$sql = "
            SELECT TOP 1
                CD.修正担当者ＣＤ,
                CD.修正日
            FROM
                注文データ CD
            WHERE
                CD.得意先ＣＤ = $CustomerCd
            AND CD.配送日 = '$DeliveryDate'
            ORDER BY
                修正日 DESC
        ";
$stmt = $pdo->query($sql);
$OrderData = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$OrderData){
    return;
}

$EditorCd=$OrderData['修正担当者ＣＤ'];
$len=strlen($EditorCd);
//修正担当者CDを取り出す
if($len<=3)
{
    //何もしない
}
else if(4<=$len && substr($EditorCd,0,1)==1)
{
    if ($len==4) {
        $EditorCd=(int)substr($EditorCd, 1, 3);
    }
    else if ($len==7)
    {
        $EditorCd=(int)substr($EditorCd, 5, 3);
    }
    else if (8<=$len)
    {
        $EditorCd=(int)substr($EditorCd, 7, 3);
    }
}
//修正担当者名を取得
$sql = "
            SELECT
                TM.担当者名
            FROM
                担当者マスタ TM
            WHERE
                TM.担当者ＣＤ = $EditorCd
        ";
$stmt = $pdo->query($sql);
$Editor = $stmt->fetch(PDO::FETCH_ASSOC);
$pdo = null;
$EditorNm=$Editor['担当者名'];

$Result = ["修正担当者ＣＤ"=>$EditorCd,
           "修正担当者名"=>$EditorNm,
           "修正日"=>$OrderData['修正日']
          ];
print json_encode($Result, JSON_PRETTY_PRINT);
