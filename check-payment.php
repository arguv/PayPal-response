<?php

if(strpos($_SERVER['HTTP_USER_AGENT'], 'paypal.com') > 0) {

    $actual_link = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    $query_str = parse_url($actual_link, PHP_URL_QUERY);
    parse_str($query_str, $query_params);

    $user_key = $query_params['buyer_user_id'];

    $log = fopen("logs/ipn.log", "a");

    fwrite($log, "\n\nipn - " . gmstrftime ("%b %d %Y %H:%M:%S", time()) . "\n");

    if($user_key) {

        fwrite($log,"buyer_user_id"."=".$user_key."\n");
        $req = "cmd=_notify-validate";

        foreach($_POST as $key=>$val)
        {
            $req.= "&".$key."=".urlencode($val);
            fwrite($log,$key."=".$val."\n");
        }

        if ($_POST["payment_status"] == "Completed")
        {


        }

/*===========================================================================================*/

        if ($_POST["payment_status"] != "Completed")
        {
            if ($_POST["payment_status"]=="Pending" )
            {
                fwrite($log,"ERROR - payment status is not Completed - $_POST[payment_status] | $_POST[pending_reason]\r\n");
                fclose($log);
                // а тут отмечаем заказ как оплаченный, но требующий подтверждение оплаты со стороны плательщика
                // такое бывает редко, но все же бывает и лучше подстраховаться.
                die;

            }

            fwrite($log,"ERROR - payment status is not Completed - $_POST[payment_status] | $_POST[pending_reason]\r\n");
            fclose($log);
            //update order status
            die;

        }

        fwrite($log,"OK - payment received $_POST[item_number].\r\n");

            // тут отмечаем заказ оплаченным.
            // деньги уже на счету продавца

    }
    fwrite($log, 'pageEnd');
    fclose($log);
}

?>