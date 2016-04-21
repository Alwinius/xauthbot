<?php

/* 
 * created by Alwin Ebermann (alwin@alwin.net.au)
 */

include("include/config.inc.php");
include("include/functions.inc.php");

//incomming: appid, id (userid), action, hash (secretappididaction, sha256)
$appid=filter_input(INPUT_POST, "appid");
$id= filter_input(INPUT_POST, "id");
$action=  filter_input(INPUT_POST, "action");
$hash=  filter_input(INPUT_POST, "hash");
$actions=["username", "first_name", "last_name", "logout", "message"];
$msg=(isset($_POST["msg"])) ? $_POST["msg"]:"";

if(basiccheckapirequest($appid,$id, $action, $hash, $actions) && checkhash($appid, $id, $action, $hash, $msg) && checkuser($id, $appid)) {
    switch ($action) {
        case "username":
            echo retuser($id);
            break;
        case "first_name":
            echo retfirst_name($id);
            break;
        case "last_name":
            echo retlast_name($id);
            break;
        case "logout":
            if(($ret=apilogout($id, $appid))===FALSE) {
                echo json_encode(["status"=>"Not logged out", "statuscode"=>402, "action"=>"logout"]);
            } else {
                echo json_encode(["status"=>"Success", "statuscode"=>200, "name"=>$ret, "action"=>"logout"]);
            }
            break;
        case "message":
            if($msg!='') {
                if(apisendmsg($msg, $id)) {
                    echo json_encode(["status"=>"Success", "statuscode"=>200, "action"=>"message"]);
                } else {
                    echo json_encode(["status"=>"User rejects messages", "statuscode"=>405]);
                }
            } else {
                echo json_encode(["status"=>"Syntax Error, no message provided", "statuscode"=>403]);
            }
            break;
    }
}
else {
    echo json_encode(["status"=>"Syntax Error, check manual", "statuscode"=>400]);
}