<?php

/* 
 * created by Alwin Ebermann (alwin@alwin.net.au)
 */

include("include/config.inc.php");
include("include/functions.inc.php");

$content = file_get_contents("php://input");
$update = json_decode($content, true);

if(preg_match("\/start ([A-Za-z0-9]{20})", $update["message"]["text"], $matches)) {
    sendmessage($update["message"]["chat"]["id"], "success ".$matches[0]);
}

