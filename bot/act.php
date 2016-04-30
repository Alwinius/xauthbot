<?php

/* 
 * created by Alwin Ebermann (alwin@alwin.net.au)
 */

include("../include/config.inc.php");
include("../include/functions.inc.php");

if(isset($_POST["act"]) && strlen($_POST["act"])==20 && ctype_alnum($_POST["act"])) {
    $res=$db->query("SELECT id FROM users WHERE activation='".$_POST["act"]."'");
    if($res->num_rows>=1) {
        echo $res->fetch_assoc()["id"];
    } else {
        echo "false";
    }
}
