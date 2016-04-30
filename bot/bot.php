<?php

/* 
 * created by Alwin Ebermann (alwin@alwin.net.au)
 */

include("../include/config.inc.php");
include("../include/functions.inc.php");

$content = file_get_contents("php://input");
$update = json_decode($content, true);

if(preg_match("/\/start (?P<activation>[A-Za-z0-9]{20})-(?P<appid>[0-9]{1,4})/", $update["message"]["text"], $matches)) {
    $result =$db->query("SELECT id FROM users WHERE userid = ".$update["message"]["chat"]["id"]." AND appid = ".$matches["appid"]);
    if($result->num_rows==0) {
        //create entry with activation in it
        $res=$db->query("INSERT INTO `xauthbot`.`users` (`id`, `app`, `userid`, `activation`, `first_name`, `username`, `last_name`, `nomsg`) VALUES (NULL, '". $matches["appid"] . "', '" . $update["message"]["chat"]["id"] .  "', '" . $matches["activation"] . "', '', '', '', '0');");
        if($db->affected_rows==1 && updateuser($update["message"]["from"])) {
            sendmessage($update["message"]["chat"]["id"], "Success! Go back to your browser now.");
        } else {
            sendmessage($update["message"]["chat"]["id"], "Update error");
        }
    } else {
        //update the activation field
        $result2=$db->query("UPDATE users SET activation='". $matches["activation"] . "' WHERE userid=".$update["message"]["from"]["id"]." AND appid=".$matches["appid"]);
        if($db->affected_rows==1 && updateuser($update["message"]["from"])) {
            sendmessage($update["message"]["chat"]["id"], "Success! Go back to your browser now.");
        } else {
            sendmessage($update["message"]["chat"]["id"], "Update error");
        }
    }    
} else if($update["message"]["text"]=="/start") {
    $message="Welcome to the xauthbot! You can use this bot to login to websites without username or password, simply with your Telegram account.\n"
            . "If your favourite site is not yet supported, ask the administrator now.";
    sendmessage($update["message"]["chat"]["id"], $message, ["one_time_keyboard"=>TRUE, "keyboard"=>[["Test"], ["zweitereihe", "drei"]]]);
} else if($update["message"]["text"]=="/list" || $update["message"]["text"]=="List all active logins") {
    updateuser($update["message"]["from"]);
    $message =  listlogins(getactivelogins($update["message"]["from"]["id"]));
    sendmessage($update["message"]["chat"]["id"], $message[0], $message[1]);
} else if(preg_match("/\/logout (?P<id>[0-9]{1,11})/", $update["message"]["text"], $matches) || preg_match("/Logout of .+ \((?P<id>[0-9]{1,11})\)/", $update["message"]["text"], $matches)) {
    if(($res=botlogout($update["message"]["from"]["id"], $matches["id"]))!==FALSE) {
        $message1="You were successfully logged out of ".$res."\n\n";
        $message=listlogins(getactivelogins($update["message"]["from"]["id"]));
        sendmessage($update["message"]["chat"]["id"], $message1.$message[0], $message[1]);
    } else {
        $message1="Error logging out. Maybe you're already logged out.\n\n";
        $message=listlogins(getactivelogins($update["message"]["from"]["id"]));
        sendmessage($update["message"]["chat"]["id"], $message1.$message[0], $message[1]);
    }
} else if($update["message"]["text"]=="/logout") {
    updateuser($update["message"]["from"]);
    sendmessage($update["message"]["chat"]["id"], "Please specify the id directly after the command.");
} else if(preg_match("/\/stopmsg (?P<id>[0-9]{1,11})/", $update["message"]["text"], $matches) || preg_match("/Stop messages from \((?P<id>[0-9]{1,11})\)/", $update["message"]["text"], $matches)) {
    updateuser($update["message"]["from"]);
    if(($ret=startstopmsg($update["message"]["from"]["id"], $matches["id"], 1))!==FALSE) {
        $keyboard=  listlogins(getactivelogins($update["message"]["from"]["id"]));
        sendmessage($update["message"]["chat"]["id"], "You'll get no more messages from ".$ret, $keyboard[1]);
    } else {
        sendmessage($update["message"]["chat"]["id"], "Sorry, this didn't work.");
    }
} else if(preg_match("/\/startmsg (?P<id>[0-9]{1,11})/", $update["message"]["text"], $matches) || preg_match("/Activate messages from \((?P<id>[0-9]{1,11})\)/", $update["message"]["text"], $matches)) {
    updateuser($update["message"]["from"]);
    if(($ret=startstopmsg($update["message"]["from"]["id"], $matches["id"], 0))!==FALSE) {
        $keyboard=  listlogins(getactivelogins($update["message"]["from"]["id"]));
        sendmessage($update["message"]["chat"]["id"], "Message blockade for ".$ret . ' lifted.', $keyboard[1]);
    } else {
        sendmessage($update["message"]["chat"]["id"], "Sorry, this didn't work.");
    }
}

else {
    sendmessage($update["message"]["chat"]["id"], "Unsupported message, for now.");
}