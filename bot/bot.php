<?php

/* 
 * created by Alwin Ebermann (alwin@alwin.net.au)
 */

include("../include/config.inc.php");
include("../include/functions.inc.php");

$content = file_get_contents("php://input");
$update = json_decode($content, true);

if(preg_match("/\/start (?P<activation>[A-Za-z0-9]{20})/", $update["message"]["text"], $matches)) {
    if(!$result =$db->query("SELECT id FROM users WHERE  activation = '".$matches["activation"] . "'")) {
        sendmessage($update["message"]["chat"]["id"], "Authentication error ".$db->error);
    } else {
        if(!$result=$db->query("UPDATE `users` SET username = '" . $update["message"]["from"]["username"] . "', `chatid` = '". $update["message"]["chat"]["id"] . "', userid = '" . $update["message"]["from"]["id"] ."', first_name='".$update["message"]["from"]["first_name"] ."', activation='' WHERE `activation` = '".$matches["activation"]."';")) {
            sendmessage($update["message"]["chat"]["id"], "Update error: ".$db->error);
        }
        else {
            sendmessage($update["message"]["chat"]["id"], "Success! Go back to your browser now.");
        }
    }    
} else {
    sendmessage($update["message"]["chat"]["id"], "Unsupported message, for now.");
}