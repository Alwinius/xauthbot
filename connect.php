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
        createhead('xauthbot - Error');
        creategeneralmessage("Error", "Database error<br><br>Please notify the administrator to resolve this issue.<br><br>"
                . '<a href="mailto:alwin@ldkf.de" class="btn btn-default">Write the admin</a> <input action="action" type="button" value="Back" class="btn btn-default" onclick="window.history.go(-1); return false;" />');
        createfooter();
    }
} else {
    createhead('xauthbot - Error');
    creategeneralmessage("Error", "Input validation failed!<br><br>Check your URL and go back in your browser history to resolve this issue.<br>"
            . '<br><input action="action" type="button" value="Back" class="btn btn-default" onclick="window.history.go(-1); return false;" />');
    createfooter();
}