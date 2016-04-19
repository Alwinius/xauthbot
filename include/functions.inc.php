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
    if (!$result = $db->query("INSERT INTO `users` (`id`, `app`, `userid`, `activation`, username, first_name, last_name) VALUES ('', '" . $appid . "', '0', '" . $activation . "', '', '', '');")) {
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

function getactivelogins($user) {
    global $db;
    $entries=[];
    $result=$db->query("SELECT users.id, apps.name FROM users INNER JOIN apps ON users.app = apps.id WHERE users.userid=".$user);
    while($row = $result->fetch_assoc()){
        $entries[]=["id"=>$row["id"], "name"=>$row["name"]];
    }
    return $entries;
}

function listlogins($logins) {
    if($logins!=[]) {
        $message="You're logged in to the following sites:\n";
        foreach ($logins as $login) {
            $message.=$login["id"]." - ".$login["name"]."\n";
        }
        $message.="Use /logout [ID] to log out of one of these sites.";
        return $message;
    }
    else {
        $message="No authorized logins at the moment.";
        return $message;
    }
}

function botlogout($user, $id) {
    global $db;
    $res=$db->query("SELECT apps.name FROM users INNER JOIN apps ON users.app = apps.id WHERE users.userid=".$user." AND `users`.`id` =".$id);
    if($res->num_rows==1) {
        $db->query("DELETE FROM `xauthbot`.`users` WHERE `users`.`id` =".$id." AND userid=".$user);
        if($db->affected_rows===1) {
            return $res->fetch_assoc()["name"];
        } else {
            return false;
        }
    }
    else {
        #return $db->num_rows;
        return false;
    }
}


// design

function createhead($title) {
    ?>
<!DOCTYPE html>
<html lang="en" ng-app="scrobbler">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <meta name="description" content="">
    <meta name="author" content="Alwin Ebermann">

    <title><?php echo $title; ?></title>
    <!--<link rel="icon" type="image/png" href="//lastfm.ldkf.de/favicon.png">-->
    <!-- Bootstrap core CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">
    <!-- Custom styles for this template -->
    <link href="css/main.css" rel="stylesheet">
    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head><body>
<?php
}

function createconnectbody($result, $ret) {
    ?>
    <div class="container">

      <div class="connectmessage">
          <h1><?php echo $result["name"]; ?></h1>
          <p><?php  echo htmlspecialchars($result["description"]); ?></p><br>
          <p class="xead"><button id="btn" class="btn btn-default">Login with Telegram</button><br>
          <br>Please click Start in the opening Telegram chat with the bot.</p><br/>
          <p class="forward">You'll be forwarded to <a class="fward" href="<?php echo ($result["secureonly"] == 1) ? "https" : "http";
        echo "://" . $result["domain"] . "/" . $ret;
        echo '?id='.$result["id"].'">'.$result["name"].'</a>.<br>Thank you for using this service.<div style="display:none;" id="activation">'.$result["activation"].'</div></p>';?>
      </div></div>
      <?php
}

function createreg() {
    ?><div class="container">
    <form method="post">
  <div class="form-group">
    <label for="name">App name</label>
    <input type="text" class="form-control" id="name" name="name" maxlength="100" placeholder="xAuthApp">
    <p class="help-block">Only letters and numbers please.</p>
  </div>
  <div class="form-group">
    <label for="desc">Description</label>
    <textarea name="description" class="form-control" placeholder="This is a really great app..." maxlength="200" rows="3" id="desc"></textarea>
  </div>
  <div class="form-group">
    <label for="domain">Domain</label>
    <input type="text" class="form-control" id="domain" name="domain" maxlength="100" placeholder="sub.example.com">
    <p class="help-block">You'll be able to forward your users to this domain only.</p>
  </div>
  <div class="checkbox">
    <label>
      <input type="checkbox" name="secureonly" value="1" checked="checked"> Connect via https only
    </label>
      <p class="help-block">Your users will be directly forwarded to the https version of your site.</p>
  </div>
  <button type="submit" class="btn btn-default">Submit</button>
</form>
    
    
    </div>
<?php
}

function createfooter() {
    ?>
      <footer class="footer">
      <div class="container">
          <p class="text-muted">Created by <a href="https://alwin.net.au">Alwin Ebermann</a> - <a href="https://ldkf.de/site-notice.html">Site Notice</a> - <a href="https://ldkf.de/privacy.html">Privacy</a> - <a href="https://github.com/Brom2/xauthbot">Developer</a></p>
      </div>
    </footer>


    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
<!--    <script>window.jQuery || document.write(\'<script src="js/jquery.min.js"><\/script>\')</script>
    <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.4.8/angular.min.js"></script>-->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js" integrity="sha384-0mSbJDEHialfmuBBQP6A4Qrprq5OVfW37PRR3j5ELqxss1yVqOtnepnHVP9aJ7xS" crossorigin="anonymous"></script>   
    <script src="include/main.js"></script>
    </body>
</html>
  <?php
}

function creategeneralmessage($heading, $msg) {
        ?>
    <div class="container">

      <div class="connectmessage">
          <h1><?php echo $heading; ?></h1>
          <p><?php  echo $msg; ?></p><br>
      </div></div>
      <?php
}