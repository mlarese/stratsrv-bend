<?
require_once "Mobile_Detect.php";

    $detect = new Mobile_Detect;

    $isandroid = $detect->version('Android')>0;

    if(!$isandroid)
        header("location: sms://3471248851&body=SKY Invia SMS e sarai contattato da uno dei nostri operatori");
    else
        header("location: sms://3471248851?body=SKY Invia SMS e sarai contattato da uno dei nostri operatori");

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Redirector</title>
</head>
<body>

</body>
</html>
