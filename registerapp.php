<?php

/* 
 * created by Alwin Ebermann (alwin@alwin.net.au)
 */

include("include/config.inc.php");
include("include/functions.inc.php");

$post=  filter_input_array(INPUT_POST);
if(!isset($_POST["name"]) || !checkregvalues($post)):
?>
<form method="post">
    Name: <input name="name" maxlength="100"><br/>
    Description: <textarea name="description" row="3" maxlength="200"></textarea><br>
    Domain: <input name="domain" placeholder="sub.example.com" maxlength="100"> You'll be able to forward your users to this domain only.<br>
    Connect via https only: <input type="checkbox" name="secureonly" value="1"> Your users will be forwarded to the https version of your site directly.<br/>
    <input type="submit">
</form>
<?php
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