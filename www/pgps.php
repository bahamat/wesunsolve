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

 if (!$lm->isLogged) {
   $content = new Template("./tpl/denied.tpl");
   goto screen;
 }
 $lo = $lm->o_login;
 $content = new Template("./tpl/pgps.tpl");
 $content->set("lo", $lo);

 $pubkey = $lo->data('pgpKeyID');
 $isThere = GPG::isKey($pubkey);
 $msg = '';
 $error = '';

 if (isset($_GET['setup']) && !empty($_GET['setup'])) {
   if ($_GET['setup'] == $lo->id) {
     /* setup the key */
     if ($isThere) { // Key id already present, check if it match email address
       if (!GPG::checkKey($pubkey, $lo->email)) {
         if (GPG::refreshKey($pubkey)) {
           $msg .= "The key $pubkey has been refreshed<br/>";
	 } else {
	   $error .= "We tried to refresh the key $pubkey but didn't successful, please either fix the key id or try again.<br/>";
	 }
       }
       if (GPG::checkKey($pubkey, $lo->email)) { // yes it match
         $msg .= "Your key looks fine, you don't need to setup it anymore...<br/>";
       } else { 
         
         $badFP = GPG::getFingerprint($pubkey);
         $error .= "The key $pubkey doesn't match your e-mail address, see below what we found...<br/>";
	 $isThere = false;
       }
     } else {
       // try to add the key to our keyring
       if (GPG::addKey($pubkey)) {
	 $msg .= "The key $pubkey has been added to the keyring..<br/>";
	 if (GPG::checkKey($pubkey, $lo->email)) { // yes it match
           $msg .= "Your key looks fine and match your account, please start using it!<br/>";
	   $isThere = true;
         } else { 
           $badFP = GPG::getFingerprint($pubkey);
           $error .= "The key $pubkey doesn't match your e-mail address, see below what we found...<br/>";
         }
       } else {
         $error .= "We could not add your key $pubkey to our keyring, please correct the keyid or retry...<br/>";
       }
     }
   }
 }

 if ($isThere) {
   if (GPG::checkKey($pubkey, $lo->email)) {
     $pgpFp = GPG::getFingerprint($pubkey);
     $content->set('pgpFp', $pgpFp);
     $msg .= "Your key match your email address successfully!<br/>";
   } else {
     $badFP = GPG::getFingerprint($pubkey);
     $error .= "The key $pubkey doesn't match your e-mail address, see below what we found...<br/>";
     $error .= "You can still try to refresh it by <a href=\"/pgps/setup/".$lo->id."\">running setup</a> again<br/>";
   }
 } else {
   $msg .= 'Your key hasn\'t been found in the keyring, if you\'ve just set it up, <a href="/pgps/setup/'.$lo->id.'">click here</a><br/>';
 }


 if (isset($msg)) $content->set("msg", $msg);
 if (isset($error)) $content->set("error", $error);
 if (isset($badFP)) $content->set("badFP", $badFP);

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
