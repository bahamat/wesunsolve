<?php
 require_once("../libs/autoload.lib.php");
 require_once("../libs/config.inc.php");

 $m = mysqlCM::getInstance();
 if ($m->connect()) {
   die("SQL Error, please try again later or contact site admins");
 }

 $h = HTTP::getInstance();
 $h->parseUrl();

 if (!isset($_GET['u']) || empty($_GET['u'])) {
   die("Username not provided");
 }

 if (!isset($_GET['p']) || empty($_GET['p'])) {
   die("Password not provided");
 }

 $lm = loginCM::getInstance();
 if ($lm->login($_GET['u'], $_GET['p'])) {
   die("Authentication failed");
 }

 if (!isset($lm->o_login) || !$lm->o_login->data("apiAccess")) {
   die("Authorization failed");
 }

 if (!isset($_GET['action']) || empty($_GET['action'])) {
   die("No action specified");
 }

 $arg = false;
 if (isset($_GET['arg']) && !empty($_GET['arg'])) {
   $arg = $_GET['arg'];
 }

 $xml = new XMLMake();
 switch($_GET['action']) {
   case "l10p":
     $xml->push('Last10Patches');
     $table = "`patches`";
     $index = "`patch`, `revision`";
     $where = " WHERE `releasedate`!='' ORDER BY `patches`.`releasedate` DESC,`patches`.`patch` DESC,`patches`.`revision` DESC LIMIT 0,10";
     if ($idx = mysqlCM::getInstance()->fetchIndex($index, $table, $where))
     {
       foreach($idx as $t) {
         $g = new Patch($t['patch'], $t['revision']);
         $g->fetchFromId();
         $g->toXML(&$xml, $arg);
       }
     }
     $xml->pop();
     break;
   case 'register_srv':
     if (isset($_POST['name']) && !empty($_POST['name'])) {
       $name = $_POST['name'];
       $comment = '';
       if (isset($_POST['comment']) && !empty($_POST['comment'])) {
         $comment = $_POST['comment'];
       }
       $srv = new Server();
       $srv->id_owner = $lm->o_login->id;
       $srv->name = $name;
       if (!$srv->fetchFromFields(array('name', 'id_owner'))) {
	 die('Server Already exist');
       } else {
	 $srv->insert();
         IrcMsg::add("[API] User added server: ".$srv->name." to his account (".$lm->o_login->username.")", MSG_ADM);
	 die('Added');
       }
     } else {
       die('Missing server name');
     }
     break;
   case 'add_plevel':
     if (isset($_POST['showrev']) && !empty($_POST['showrev']) &&
	 isset($_POST['pkginfo']) && !empty($_POST['pkginfo']) && 
         isset($_POST['name']) && !empty($_POST['name']) &&
	 isset($arg) && !empty($arg)) {
	$s = new Server();
	$s->id_owner = $lm->o_login->id;
	$s->name = $arg;
	if ($s->fetchFromFields(array('name', 'id_owner'))) {
	  die('Server not found');
	}
	$showrev = explode(PHP_EOL, $_POST['showrev']);
	$pkginfo = explode(PHP_EOL, $_POST['pkginfo']);
	$pl = new PLevel();
	$pl->id_server = $s->id;
        $pl->name = $_POST['name'];
        if (!$pl->fetchFromFields(array('id_server', 'name'))) {
          die('Patch level name already taken for this server');
        }
        $pl->comment = 'Imported from API on '.time();
        $pl->insert();
	$pl->buildFromFiles($showrev, $pkginfo);
	IrcMsg::add("[API] User added Patch level: ".$pl->name." to his account (".$lm->o_login->username.")", MSG_ADM);
        echo "Level $pl added to $s with ".count($pl->a_patches)." patches and ".count($pl->a_srv4pkgs)." pkgs";
	die();
     } else {
       die('Missing Field');
     }
     break;
   default:
     die("Unknown function");
     break;
 }

 header("Content-type: text/xml");
 print $xml->getXml();

?>
