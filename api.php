<?php

/* 
 * created by Alwin Ebermann (alwin@alwin.net.au)
 */

include("include/config.inc.php");
include("include/functions.inc.php");

//incomming: appid, id (userid), action, hash (secretappididaction, sha256)
$appid=filter_input(INPUT_GET, "appid");
$id= filter_input(INPUT_GET, "id");
$action=  filter_input(INPUT_GET, "action");
$hash=  filter_input(INPUT_GET, "hash");
$actions=["username", "all", ];

if(basiccheckapirequest($appid,$id, $action, $hash, $actions) && checkhash($appid, $id, $action, $hash)) {
    switch ($action) {
        case "username":
            echo retuser($id);
            break;
        case "all":
            echo json_encode(["status"=>"Not implemented", "statuscode"=>401]);
            break;
    }
}
else {
    echo json_encode(["status"=>"Syntax Error, check manual", "statuscode"=>400]);
}