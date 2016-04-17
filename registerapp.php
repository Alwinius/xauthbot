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
        echo 'Error registering your app.';
    } else {
        print('Success');
        print('Your ID: '.$res["id"].', your secret: '.$res["secret"]);
    }
endif;

createfooter();

