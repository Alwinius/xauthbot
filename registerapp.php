<?php

/* 
 * created by Alwin Ebermann (alwin@alwin.net.au)
 */

include("include/config.inc.php");
include("include/functions.inc.php");

$post=  filter_input_array(INPUT_POST);
createhead("xauthbot - Register your app");
if(!isset($_POST["name"]) || !checkregvalues($post)):
    createreg();
else:
    //process the data
    $res=  regapp($post);
    if ($res===FALSE) {
        creategeneralmessage("Error", 'There was an error registering your app. Please check all values.<br><br><input action="action" type="button" value="Back" class="btn btn-default" onclick="window.history.go(-1); return false;" />');
    } else {
        creategeneralmessage("Success", 'Your app was successfully registered.<br><br>Your ID: '.$res["id"].'<br>'
                . 'Your secret: '.$res["secret"].'<br><br>'
                . 'Check out <a href="https://github.com/Brom2/xauthbot">Github</a> on how to make requests to the API and connect users.');
    }
endif;

createfooter();

