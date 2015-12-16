<?php

$result = $wpdb->get_row("SELECT * FROM paypal_payments ", ARRAY_A);
var_dump(sizeof($result));
//$result = $wpdb->get_results("SELECT * FROM paypal_payments", ARRAY_A);
var_dump($result);

/*
* Template Name: checking paypal payment
*/
$actual_link = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
$query_str = parse_url($actual_link, PHP_URL_QUERY);
parse_str($query_str, $query_params);

$log = fopen("logs/ipn.log", "a");
fwrite($log, "\n\nipn - " . gmstrftime ("%b %d %Y %H:%M:%S", time()) . "\n");
fwrite($log,"buyer_user_id"."=".$query_params['buyer_user_id']."\n");

$req = "cmd=_notify-validate";

foreach($_POST as $key=>$val)
{
    $req.= "&".$key."=".urlencode($val);
    fwrite($log,$key."=".$val."\n");
}

$header = "POST http://www.paypal.com/cgi-bin/webscr HTTP/1.0\r\n";
$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
$header .= "Content-Length: " . strlen ($req) . "\r\n\r\n";
$fp = fsockopen ("www.sandbox.paypal.com", 80, $errno, $errstr, 30);

if (!$fp)
{
    echo "$errstr ($errno)";
    fwrite($log, "Failed to open HTTP connection!\n");
    fwrite($log, $errstr." ".$errno);
    fclose ($log);
    return;
}

fputs ($fp, $header . $req);

$res="";
while (!feof($fp))
    $res .= fgets ($fp, 1024);
fclose ($fp);

if (strpos($res, "VERIFIED")===FALSE)
{
    fwrite($log,"ERROR - UnVERIFIIED payment\r\nPayPal response:");
    fwrite($log,$res);
    fclose($log);
    return;
}

fwrite($log,"payment VERIFIIED\r\n");

if ($_POST["payment_status"]!="Completed")
{
    if ($_POST["payment_status"]=="Pending" )
    {
        fwrite($log,"ERROR - payment status is not Completed - $_POST[payment_status] | $_POST[pending_reason]\r\n");
        fclose($log);
// а тут отмечаем заказ как оплаченный, но требующий подтверждение оплаты со стороны плательщика
// такое бывает редко, но все же бывает и лучше подстраховаться.

        return;
    }

    fwrite($log,"ERROR - payment status is not Completed - $_POST[payment_status] | $_POST[pending_reason]\r\n");
    fclose($log);
    return;
    //update order status

}

fwrite($log,"OK - payment received $_POST[item_number].\r\n");
fclose($log);
// тут отмечаем заказ оплаченным.
// деньги уже на счету продавца

?>