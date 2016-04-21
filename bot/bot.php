<?php

/* 
 * created by Alwin Ebermann (alwin@alwin.net.au)
 */

include("../include/config.inc.php");
include("../include/functions.inc.php");

$content = file_get_contents("php://input");
$update = json_decode($content, true);

if(preg_match("/\/start (?P<activation>[A-Za-z0-9]{20})/", $update["message"]["text"], $matches)) {
    $result =$db->query("SELECT id FROM users WHERE  activation = '".$matches["activation"] . "'");
    if($result->num_rows==0) {
        sendmessage($update["message"]["chat"]["id"], "Authentication error ");
    } else {
        $result=$db->query("UPDATE users SET activation='', userid='".$update["message"]["from"]["id"]."' WHERE activation='".$matches["activation"]);
        if($result->affected_rows==1 && !updateuser($update["message"]["from"])) {
            sendmessage($update["message"]["chat"]["id"], "Update error");
        } else {
            sendmessage($update["message"]["chat"]["id"], "Success! Go back to your browser now.");
        }
    }    
} else if($update["message"]["text"]=="/start") {
    $message="Welcome to the xauthbot! You can use this bot to login to websites without username or password, simply with your Telegram account.\n"
            . "If your favourite site is not yet supported, ask the administrator now.";
    sendmessage($update["message"]["chat"]["id"], $message);
} else if($update["message"]["text"]=="/list") {
    updateuser($update["message"]["from"]);
    $message=  listlogins(getactivelogins($update["message"]["from"]["id"]));
    sendmessage($update["message"]["chat"]["id"], $message);
} else if(preg_match("/\/logout (?P<id>[0-9]{1,11})/", $update["message"]["text"], $matches)) {
    if(($res=botlogout($update["message"]["from"]["id"], $matches["id"]))!==FALSE) {
        $message="You were successfully logged out of ".$res."\n\n";
        $message.=listlogins(getactivelogins($update["message"]["from"]["id"]));
        sendmessage($update["message"]["chat"]["id"], $message);
    }
    else {
        $message="Error logging out. Maybe you're already logged out.\n\n";
        $message.=listlogins(getactivelogins($update["message"]["from"]["id"]));
        sendmessage($update["message"]["chat"]["id"], $message);
    }
} else if($update["message"]["text"]=="/logout") {
    updateuser($update["message"]["from"]);
    sendmessage($update["message"]["chat"]["id"], "Please specify the id directly after the command.");
} else if(preg_match("/\/stopmsg (?P<id>[0-9]{1,11})/", $update["message"]["text"], $matches)) {
    updateuser($update["message"]["from"]);
    if(($ret=stopmsg($update["message"]["from"]["id"], $matches["id"]))!==FALSE) {
        sendmessage($update["message"]["chat"]["id"], "You'll get no more messages from ".$ret);
    } else {
        sendmessage($update["message"]["chat"]["id"], "Sorry, this didn't work.");
    }
}
else {
    sendmessage($update["message"]["chat"]["id"], "Unsupported message, for now.");
}