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
        return ["id" => $db->insert_id, "activation" => $activation, "name" => $row["name"], "description" => $row["description"], "domain" => $row["domain"], "secureonly" => $row["secureonly"]];
    }
}

function basiccheckapirequest($appid, $id, $action, $hash, $actions) {
    if(ctype_digit($appid) && ctype_digit($id) && in_array($action, $actions) && strlen($hash) == 64 && ctype_xdigit($hash)) {
        return true;
    } else {
        return false;
    }
}

function checkhash($appid, $id, $action, $hash) {
    global $db;
    $res=$db->query("SELECT apps.secret FROM users INNER JOIN apps ON users.app= apps.id WHERE users.id=$id AND apps.id=$appid AND activation = '' ");
    if($res->num_rows===1) {
        $info = $res->fetch_assoc();
        if(hash("sha256", $info["secret"].$appid.$id.$action)===$hash) {
            return true;
        } else {
            return false;
        }
    } else {
        return false;
    }
}

function retuser($id) {
    global $db;
    $res=$db->query("SELECT username FROM users WHERE id=$id");
    $username=$res->fetch_assoc()["username"];
    $return=["status"=>"Success", "statuscode"=>200, "action"=>"username", "username"=>$username];
    return json_encode($return);
}

function checkregvalues($post) {
    if(ctype_alnum($post["name"]) && strlen($post["name"])<100 && strlen($post["description"])<200 && preg_match("/^(?!\-)(?:[a-zA-Z\d\-]{0,62}[a-zA-Z\d]\.){1,126}(?!\d+)[a-zA-Z\d]{1,63}$/", $post["domain"]) && in_array($post["secureonly"], ["", 1])) {
        return true;
    } else {
        return false;
    }
}

function regapp($post) {
    global $db;
    $res=$db->query("SELECT id FROM apps WHERE name='".$post["name"]."'");
    if($res->num_rows===1) {
        return false;
    } else {
        $secret=generateRandomString(20);
        if(!$result=$db->query("INSERT INTO apps(id, name, description, secret, domain, secureonly) VALUES('', '".$post["name"]."', '".$db->real_escape_string($post["description"])."', '  $secret', '".$db->real_escape_string($post["domain"])."', '" . (($post["secureonly"]==1) ? 1:0) . "');")) {
            return false;
        } else {
            return ["id"=>$db->insert_id, "secret"=>$secret];
        }
    }
}