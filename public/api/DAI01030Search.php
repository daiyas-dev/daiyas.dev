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

require("DAI01030SearchFunc.php");
$DataList = Search();

print json_encode($DataList, JSON_PRETTY_PRINT);
