<?php

/*
 * created by Alwin Ebermann (alwin@alwin.net.au)
 */

include("include/config.inc.php");
include("include/functions.inc.php");


if (checkrequest(filter_input(INPUT_GET, "appid"), filter_input(INPUT_GET, "ret"))) {
    $result = generateentry(filter_input(INPUT_GET, "appid"));
    if (!$result === False) {
        createhead("xauthbot - Connect");
        createconnectbody($result, filter_input(INPUT_GET, "ret"));
        createfooter();
    } else {
        echo 'Could not create database entry';
    }
} else {
    echo 'Input validation failed';
}