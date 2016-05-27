<?php
/*
 * created by Alwin Ebermann (alwin@alwin.net.au)
 */

class Validator {

    public function hash($value) {
        if (strlen($value) == 64 && ctype_xdigit($value)) {
            return $value;
        } else {
            return false;
        }
    }
    public function action($value) {
        if(in_array($value, ["username", "first_name", "last_name", "logout", "message", "all", "userid"])) {
            return $value;
        } else {
            return false;
        }
    }

}

function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

function sendmessage($chatid, $message, $keyboard = []) {
    global $auth_token;
    $url = "https://api.telegram.org/bot" . $auth_token . "/";
    $sendto = $url . "sendmessage?chat_id=" . $chatid . "&parse_mode=Markdown&text=" . urlencode($message);
    if ($keyboard != []) {
        $sendto.='&reply_markup=' . json_encode($keyboard);
    }
    file_get_contents($sendto);
    return true;
}

function checkrequest($appid, $ret) {
    global $db;
    if (filter_var("http://example.com/" . $ret, FILTER_VALIDATE_URL) !== FALSE) {
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
    $ret = $db->query("SELECT * FROM apps WHERE id=" . $appid);
    $row = $ret->fetch_assoc();
    return ["activation" => $activation, "id"=>$row["id"], "name" => $row["name"], "description" => $row["description"], "domain" => $row["domain"], "secureonly" => $row["secureonly"]];
}

function checkhash($appid, $id, $action, $hash, $msg) {
    global $db;
    $res = $db->query("SELECT apps.secret FROM users INNER JOIN apps ON users.app= apps.id WHERE users.id=$id AND apps.id=$appid ");
    if ($res->num_rows == 1) {
        $info = $res->fetch_assoc();
        echo $info["secret"] . $appid . $id . $action . $msg;
        if (hash("sha256", $info["secret"] . $appid . $id . $action . $msg) === $hash) {
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
    $res = $db->query("SELECT username FROM users WHERE id=$id");
    $username = $res->fetch_assoc()["username"];
    $return = ["status" => "Success", "statuscode" => 200, "action" => "username", "username" => $username];
    return $return;
}

function checkregvalues($post) {
    if (ctype_alnum($post["name"]) && strlen($post["name"]) < 100 && strlen($post["description"]) < 200 && preg_match("/^(?!\-)(?:[a-zA-Z\d\-]{0,62}[a-zA-Z\d]\.){1,126}(?!\d+)[a-zA-Z\d]{1,63}$/", $post["domain"]) && in_array($post["secureonly"], ["", 1])) {
        return true;
    } else {
        return false;
    }
}

function regapp($post) {
    global $db;
    $res = $db->query("SELECT id FROM apps WHERE name='" . $post["name"] . "'");
    if ($res->num_rows === 1) {
        return false;
    } else {
        $secret = generateRandomString(20);
        if (!$result = $db->query("INSERT INTO apps(id, name, description, secret, domain, secureonly) VALUES('', '" . $post["name"] . "', '" . $db->real_escape_string($post["description"]) . "', '$secret', '" . $db->real_escape_string($post["domain"]) . "', '" . (($post["secureonly"] == 1) ? 1 : 0) . "');")) {
            return false;
        } else {
            return ["id" => $db->insert_id, "secret" => $secret];
        }
    }
}

function getactivelogins($user) {
    global $db;
    $entries = [];
    $result = $db->query("SELECT users.id, apps.name, users.nomsg FROM users INNER JOIN apps ON users.app = apps.id WHERE users.userid=" . $user);
    while ($row = $result->fetch_assoc()) {
        $entries[] = ["id" => $row["id"], "name" => $row["name"], "nomsg" => $row["nomsg"]];
    }
    return $entries;
}

function listlogins($logins) {
    if ($logins != []) {
        $keyboard = ["keyboard" => [["List all active logins"]]];
        $message = "You're logged in to the following sites:\n";
        foreach ($logins as $login) {
            $message.=$login["id"] . " - " . $login["name"] . "\n";
            if ($login["nomsg"] == 1) {
                $keyboard["keyboard"][] = ["Logout of " . $login["name"] . " (" . $login["id"] . ")", "Activate messages from (" . $login["id"] . ")"];
            } else {
                $keyboard["keyboard"][] = ["Logout of " . $login["name"] . " (" . $login["id"] . ")", "Stop messages from (" . $login["id"] . ")"];
            }
        }
        return [$message, $keyboard];
    } else {
        $message = "No authorized logins at the moment.";
        return [$message, []];
    }
}

function botlogout($user, $id) {
    global $db;
    $res = $db->query("SELECT apps.name FROM users INNER JOIN apps ON users.app = apps.id WHERE users.userid=" . $user . " AND `users`.`id` =" . $id);
    if ($res->num_rows == 1) {
        $db->query("DELETE FROM `xauthbot`.`users` WHERE `users`.`id` =" . $id . " AND userid=" . $user);
        if ($db->affected_rows === 1) {
            return $res->fetch_assoc()["name"];
        } else {
            return false;
        }
    } else {
        return false;
    }
}

function apilogout($user, $id) {
    global $db;
    $res = $db->query("SELECT apps.name FROM users INNER JOIN apps ON users.app = apps.id WHERE users.id=" . $user . " AND `users`.`app` =" . $id);
    if ($res->num_rows == 1) {
        $db->query("DELETE FROM `xauthbot`.`users` WHERE `users`.`app` =" . $id . " AND id=" . $user);
        if ($db->affected_rows === 1) {
            return $res->fetch_assoc()["name"];
        } else {
            return false;
        }
    } else {
        return false;
    }
}

function retfirst_name($id) {
    global $db;
    $res = $db->query("SELECT first_name FROM users WHERE id=$id");
    $first_name = $res->fetch_assoc()["first_name"];
    $return = ["status" => "Success", "statuscode" => 200, "action" => "first_name", "first_name" => $first_name];
    return $return;
}

function retlast_name($id) {
    global $db;
    $res = $db->query("SELECT last_name FROM users WHERE id=$id");
    $last_name = $res->fetch_assoc()["last_name"];
    $return = ["status" => "Success", "statuscode" => 200, "action" => "last_name", "last_name" => $last_name];
    return $return;
}

function retall($id) {
    global $db;
    $res = $db->query("SELECT first_name, last_name, username FROM users WHERE id=$id");
    $ret = $res->fetch_assoc();
    $return = ["status" => "Success", "statuscode" => 200, "action" => "all", "last_name" => $ret["last_name"], "first_name" => $ret["first_name"], "username" => $ret["username"]];
    return $return;
}

function retuserid($id) {
    global $db;
    $res = $db->query("SELECT userid FROM users WHERE id=$id");
    $userid = $res->fetch_assoc()["userid"];
    $return = ["status" => "Success", "statuscode" => 200, "action" => "userid", "userid" => $userid];
    return $return;
}

function checkuser($id, $app) {
    global $db;
    $res = $db->query("SELECT first_name FROM users WHERE id=" . $id . " AND app=" . $app);
    if ($res->num_rows == 1) {
        return true;
    } else {
        return false;
    }
}

function apisendmsg($msg, $id) {
    global $db;
    $res = $db->query("SELECT users.userid, apps.name, users.id FROM users INNER JOIN apps ON users.app=apps.id WHERE users.nomsg=0 AND users.id=" . $id);
    if ($res->num_rows == 1) {
        $user = $res->fetch_assoc();
        sendmessage($user["userid"], "New Message from " . $user["name"] . ":\n" . $msg . "\n\nTo stop messages from " . $user["name"] . " type /stopmsg " . $user["id"]);
        return true;
    } else {
        return false;
    }
}

function startstopmsg($user, $id, $act) {
    global $db;
    $res = $db->query("SELECT apps.name FROM users INNER JOIN apps ON users.app = apps.id WHERE userid=" . $user . " AND `users`.`id` =" . $id);
    if ($res->num_rows == 1) {
        $db->query("UPDATE `xauthbot`.`users` SET nomsg=$act WHERE `users`.`id` =" . $id . " AND userid=" . $user);
        if ($db->affected_rows === 1) {
            return $res->fetch_assoc()["name"];
        } else {
            return false;
        }
    } else {
        return false;
    }
}

function updateuser($user) {
    global $db;
    $username = (isset($user["username"])) ? $user["username"] : "";
    $last_name = (isset($user["last_name"])) ? $user["last_name"] : "";
    $result = $db->query("UPDATE `users` SET username = '" . $username . "', first_name='" . $user["first_name"] . "', last_name='" . $last_name . "' WHERE `userid` = '" . $user["id"] . "';");
    if ($db->affected_rows > 1) {
        return true;
    } else {
        return true; //dont know why this wont work otherwise
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
                    <p><?php echo htmlspecialchars($result["description"]); ?></p><br>
                    <p class="xead"><button id="btn" class="btn btn-default">Login with Telegram</button><br>
                        <br>Please click Start in the opening Telegram chat with the bot.</p><br/>
                    <p class="forward">You'll be forwarded to <a class="fward" href="<?php
        echo ($result["secureonly"] == 1) ? "https" : "http";
        echo "://" . $result["domain"] . "/" . $ret;
        echo '?id=">' . $result["name"] . '</a>.<br>Thank you for using this service.<div style="display:none;" id="activation">' . $result["activation"] . '</div><div style="display:none;" id="appid">' . $result["id"] . '</div></p>';
        ?>
                                                                 </div></div>
                                                                 <div id="trouble" class="modal fade" role="dialog">
                                                                 <div class="modal-dialog">
                                <!-- Modal content-->
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                                        <h4 class="modal-title">Having trouble connecting?</h4>
                                    </div>
                                    <div class="modal-body">
                                        <p>This is what you have to do:</p>
                                        <p>First press the green "Send Message" button.<br><img src="css/tgram.me.JPG"></p>
                                        <p>Then select tg in the opening popup (This is the desktop Telegram client)<br><img src="css/popup.JPG"></p>
                                        <p>Press Start in the Telegram desktop client. <img src="css/tgram.JPG"></p>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                    </div>
                                </div>

                            </div>
                </div>
    <?php
}

function createreg() {
    ?><div class="container">
                    <form method="post" id="regform">
                        <div class="form-group">
                            <label for="name">App name</label>
                            <input type="text" class="form-control" id="name" name="name" maxlength="100" placeholder="xAuthApp">
                        </div>
                        <div class="form-group">
                            <label for="desc">Description</label>
                            <textarea name="description" class="form-control" placeholder="This is a really great app..." maxlength="200" rows="3" id="desc"></textarea>
                            <p class="help-block">This will be displayed in the connection page.</p>
                        </div>
                        <div class="form-group">
                            <label for="domain">Domain</label>
                            <input type="text" class="form-control" id="domain" name="domain" maxlength="100" placeholder="sub.example.com">
                            <p class="help-block">Your users will be forwarded to this domain.</p>
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
                <script src="include/formValidation.min.js"></script>
                <script src="include/framework_bootstrap.min.js"></script>
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
            <p><?php echo $msg; ?></p><br>
        </div></div>
    <?php
}
