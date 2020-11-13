<?php

function send_notification ($tokens, $message)
{
    $url = 'https://fcm.googleapis.com/fcm/send';
    $fields = array(
        'registration_ids' => $tokens,
        'data' => $message
    );
    $key = "AAAA0WICkkA:APA91bF2kVcXbBgpe0NmoFTeVGlsZwyFOsRGVYcWuOqFSVFZYgwfh009DBcEHBDisxuBx7FbF0JvzTtFBR0XaPDk4rlqqb-3CFdgtmnyk39OxU6Ey9gcXt5aKHbUhaR1ZHMMr0XyeDJE";
    $headers = array(
        'Authorization:key =' . $key,
        'Content-Type: application/json'
    );

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
    $result = curl_exec($ch);
    if ($result === FALSE) {
        die('Curl failed: ' . curl_error($ch));
    }
    curl_close($ch);
    return $result;
}





$tokens = array();
$tokens[0] = "cAgpv6gGThOC1R0QlZd3Xk:APA91bH4kjFQewRUhIzh6QLD-jtvsVSEa077t_2u8cN9uSB5bR2miguiAW3Qxu6JsqhuApA0a3vldt49k6uBnvv5s1CA5zdbmK1tVFNfVbodwC55-deSiM4DzVwiEdZY7WhiACOGJMy-";

$myMessage = "Message Test";
if ($myMessage == ""){
    $myMessage = "Newly registered.";
}

$message = array("message" => $myMessage);
$message_status = send_notification($tokens, $message);
echo $message_status;


?>
