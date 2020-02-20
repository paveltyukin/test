<?php
set_time_limit(0);
ini_set('memory_limit', '512M');

require_once('../config_db.php');
require_once('mysql.class.php');

$config = array(
    "mail_adv_buy" => "sales@*******.ru",
    "mail_adv_partner" => "sales@*******.ru",
    "mail_adv_condition" => "sales@*******.ru",

    "mail_pub_partner" => "pub@*******.ru",
    "mail_pub_condition" => "pub@*******.ru",

    "main_from" => "noreply@*******.ru"
);

$headers  = "Content-type: text/html; charset=utf-8 \n";
$headers .= "From: <{$config["main_from"]}>\n";

$db = new db_mysql(HOST, USERNAME, PASSWORD, DBNAME);
$db->query("set names utf8");

$q = "SELECT mail_id, mail_to, mail_title, mail_body FROM k_mail WHERE mail_status='notsend';";
$res = $db->query($q);
$id_good = "";
$id_fail = "";
while($row = $db->fetch_array($res)){
    $m = (mail($config[$row['mail_to']], $row['mail_title'], $row['mail_body'], $headers));
    if ($m){
        $id_good .= (($id_good == "") ? "" : ", ") .$row["mail_id"];
    } else {
        $id_fail .= (($id_fail == "") ? "" : ", ") .$row["mail_id"];
    }
}

if ($id_good != ""){
    $db->query("UPDATE k_mail SET mail_status='send' WHERE mail_id IN ({$id_good});");
}

if ($id_fail != ""){

    $db->query("UPDATE k_mail SET mail_status='error', mail_repeat=mail_repeat+1 WHERE mail_id IN ({$id_fail});");
}

?>