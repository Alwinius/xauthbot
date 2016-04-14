<?php

/*
 * created by Alwin Ebermann (alwin@alwin.net.au)
 */

include("include/config.inc.php");
include("include/functions.inc.php");


if (checkrequest(filter_input(INPUT_GET, "appid"), filter_input(INPUT_GET, "ret"))) {
    $result = generateentry(filter_input(INPUT_GET, "appid"));
    if (!$result === False) {
        echo 'Use the following link to connect to the application ' . $result["name"] . ' (' . $result["description"] . '): <a href="https://telegram.me/xauthbot?start=' . $result["activation"] . '" target="_blank">Click</a><br/>';
        echo '<br/>Finally, click here  <a href="';
        echo ($result["secureonly"] == 1) ? "https" : "http";
        echo "://" . $result["domain"] . "/" . filter_input(INPUT_GET, "ret");
        echo '?id='.$result["id"].'">here</a> to return to ' . $result["name"] . ' after you activated the bot.';
    } else {
        echo 'Could not create database entry';
    }
} else {
    echo 'Input validation failed';
}