<?php
 require_once("../libs/autoload.lib.php");
 require_once("../libs/config.inc.php");

 $m = mysqlCM::getInstance();
 if ($m->connect()) {
   HTTP::getInstance()->errMysql();
 }
 $lm = loginCM::getInstance();
 $lm->startSession();

 $h = HTTP::getInstance();
 $h->parseUrl();

 $lo = $lm->o_login;
 if (!$lm->isLogged) {
   $content = new Template("./tpl/denied.tpl");
   goto screen;
 }
 $lo->fetchData();
 if (isset($_POST['save'])) { /* Try to find every settings and update its value if necessary */
   $msg = "";
   foreach ($lo->_plist as $name => $param) {
     /* Gather settings info and default value if necessary */
     if (!isset($_POST[$name]) || empty($_POST[$name])) {
       $newval = false;
     } else {
       $newval = $_POST[$name];
     }
     $min = $param['min'];
     $desc = $param['desc'];
     $max = $param['max'];
     if (isset($param['objvar'])) {
       $val = $lo->{$name};
     } else {
       $val = $lo->data($name);
     }
     if (isset($param['values'])) {
       $values = $param['values'];
     }
     if (!$val && isset($config[$name])) {
       $val = $config[$name];
     }
     switch ($param['type']) {
       case "E":
        if($val == $newval) {
	  continue;
	}
	if (isset($param['objvar'])) {
          $lo->{$name} = $newval;
          $lo->update();
        } else {
          $lo->setData($name, $newval);
        }
        $msg .= "\"$desc\" Parameters has been updated with value $newval<br/>\n";
       break;
       case "B":
        if ($val == $newval) {
	  continue;
	}
        if (isset($param['objvar'])) {
	  $lo->{$name} = $newval;
	  $lo->update();
	} else {
	  $lo->setData($name, $newval);
	}
	$msg .= "\"$desc\" Parameters has been updated with value $newval<br/>\n";
       break;
       case "N":
        if ($val == $newval) {
	  continue; // nothing to change
	}
        if ($newval < $min || $newval > $max) {
          $error = "Value for \"$desc\" is not between defined limits ($min < n < $max)";
	  $content = new Template("./tpl/settings.tpl");
	  $content->set("error", $error);
 	  $content->set("login", $lo->username);
	  $content->set("lo", $lo);
	  goto screen;
	}
        if (isset($param['objvar'])) {
          $lo->{$name} = $newval;
          $lo->update();
        } else {
          $lo->setData($name, $newval);
        }
	$msg .= "\"$desc\" Parameters has been updated with value $newval<br/>\n";
	break;
       default:
	continue;
        break;
     }

   }
 }
 $content = new Template("./tpl/settings.tpl");
 $content->set("login", $lo->username);
 $content->set("lo", $lo);
 if (isset($msg)) $content->set("msg", $msg);

screen:
 $index = new Template("./tpl/index.tpl");
 $head = new Template("./tpl/head.tpl");
 $menu = new Template("./tpl/menu.tpl");
 $foot = new Template("./tpl/foot.tpl");
 $foot->set("start_time", $start_time);

 $index->set("head", $head);
 $index->set("menu", $menu);
 $index->set("content", $content);
 $index->set("foot", $foot);
 echo $index->fetch();
?>
