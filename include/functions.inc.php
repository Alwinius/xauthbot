<?php

/*
 * created by Alwin Ebermann (alwin@alwin.net.au)
 */

function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

function sendmessage($chatid, $message) {
    global $auth_token;
    $url = "https://api.telegram.org/bot" . $auth_token . "/";
    $sendto = $url . "sendmessage?chat_id=" . $chatid . "&parse_mode=Markdown&text=" . urlencode($message);
    file_get_contents($sendto);
    return true;
}

function checkrequest($appid, $ret) {
    global $db;
    if (ctype_digit($appid) && !filter_var("http://example.com/" . $ret, FILTER_VALIDATE_URL) === FALSE) {
        if (!$result = $db->query("SELECT id FROM apps WHERE id = $appid;")) {
            return false;
        } else {
            return true;
        }
    } else {
        return false;
    }
}

function generateentry($appid) {
    global $db;
    $activation = generateRandomString(20);
    if (!$result = $db->query("INSERT INTO `users` (`id`, `app`, `userid`, `activation`, `chatid`, username, first_name) VALUES ('', '" . $appid . "', '0', '" . $activation . "', '0', '', '');")) {
        return false;
    } else {
        $ret = $db->query("SELECT * FROM apps WHERE id=" . $appid);
        $row = $ret->fetch_assoc();
        return ["id" => $ret->insert_id, "activation" => $activation, "name" => $row["name"], "description" => $row["description"], "domain" => $row["domain"], "secureonly" => $row["secureonly"]];
    }
}
