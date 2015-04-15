<?php
/**
 * File used to store application settings
 * @author Gouverneur Thomas <tgo@espix.net>
 * @copyright Copyright (c) 2007-2009, Gouverneur Thomas
 * @version 1.0
 * @package includes
 * @subpackage config
 * @category config
 */

/** DO NOT EDIT **/
global $config;

/**
 * SiteName, to be used by cookies
 * @var string
 */
$config['sitename'] = 'wesunsolve';

/**
 * Root path of the whole sunsolve project
 * @var string
 */
$config['rootpath'] = '/srv/sunsolve';

/**
 * Path for daemon log file
 * @var string
 */
$config['ws2d']['log'] = '/srv/sunsolve/logs/ws2d.log';

/**
 * Pid file for ws2d daemon
 * @var string
 */
$config['ws2d']['pid'] = '/srv/sunsolve/logs/ws2d.pid';

/**
 * Patches repository
 * @var string
 */
$config['ppath'] = $config['rootpath'].'/repository/patches';

/**
 * Bugs repository
 * @var string
 */
$config['bidpath'] = $config['rootpath'].'/repository/bugids';

/**
 * Bundle repository
 * @var string
 */
$config['bndlpath'] = $config['rootpath'].'/repository/bundles';

/**
 * Various file repository
 * @var string
 */
$config['variouspath'] = $config['rootpath'].'/repository/various';

/**
 * ws2_patchdiag.xref archive directory
 * @var string
 */
$config['ws2pdiag'] = $config['rootpath'].'/repository/ws2_pdiag';

/**
 * patchdiag.xref archive directory
 * @var string
 */
$config['pdiagpath'] = $config['rootpath'].'/repository/patchdiag';

/**
 * IPS archive directory
 * @var string
 */
$config['ipspath'] = $config['rootpath'].'/repository/ips';

/**
 * IPS Repo list
 * @var array
 */
$config['ipslist'] = array(
			   'default' => 'support',
			   'glassfish_support',
			   'ha-cluster',
			   'ha-cluster_support',
			   'sstudio',
			   'sstudio_support',
			   'exadata'
			  );

/**
 * patchdiag.xref archive directory
 * @var string
 */
$config['ckpath'] = $config['rootpath'].'/repository/checksums';

 /**
 * Temporary workdir
 * @var string
 */
$config['tmppath'] = $config['rootpath'].'/tmp';

/**
 * Extracted directory path
 * @var string
 */
$config['extpath'] = $config['rootpath'].'/repository/extracted/patches';

/**
 * Path to id_rsa file for master sync
 * @var string
 */
$config['rsapath'] = $config['rootpath'].'/etc/id_rsa';

/**
 * Master hostname
 * @var string
 */
$config['ws2master'] = "hihat.vpn.espix.org";

/**
 * Path of the graphviz dot utility
 * @var string
 */
$config['dotpath'] = '/usr/bin/dot';

/**
 * Path of GPG client
 * @var string
 */
$config['gpgbin'] = '/usr/bin/gpg';

/**
 * Path of GPG client
 * @var string
 */
$config['gpghome'] = '/srv/sunsolve/pgp';

/**
 * Path of GPG client
 * @var string
 */
$config['gpgopt'] = '--no-tty --always-trust --batch --no-secmem-warning --no-greeting --no-mdc-warning --no-permission-warning --homedir '.$config['gpghome'];


/**
 * URL of patchdiag.xref file
 * @var string
 */
$config['patchdiag'] = 'https://getupdates.oracle.com/reports/patchdiag.xref';

/**
 * Base URL to gather patches
 * @var string
 */
$config['patchurl'] = 'https://getupdates.oracle.com/all_unsigned';

/**
 * Base URL to gather bundles
 * @var string
 */
$config['bndlurl'] = 'https://getupdates.oracle.com/patch_cluster';

/**
 * Base URL to gather READMEs
 * @var string
 */
$config['readmeurl'] = 'https://getupdates.oracle.com/readme';

/**
 * URL to gather CHECKSUMS file
 * @var string
 */
$config['checksumurl'] = 'https://getupdates.oracle.com/reports/CHECKSUMS';

/**
 * Base URL to download bug descriptions
 * @var string
 */
//$config['bugurl'] = 'https://support.oracle.com/CSP/main/article?cmd=show&type=BUG&bugProductSource=Sun&productFamily=Sun&id=';
//$config['bugurl'] = 'https://support.oracle.com/epmos/faces/ui/km/BugDisplay.jspx?bugProductSource=Sun&recommended=true&id=';
$config['bugurl'] = 'https://support.oracle.com/epmos/faces/ui/km/BugDisplay.jspx?bugProductSource=Sun&id=';

/**
 * UI Settings
 */

/**
 * Default number of patches per page to show
 * @var int
 */
$config['apiAccess'] = 0;

/**
 * Default number of patches per page to show
 * @var int
 */
$config['serversPerPage'] = 50;

/**
 * Default number of patches per page to show
 * @var int
 */
$config['cvePerPage'] = 20;

/**
 * Default number of patches per page to show
 * @var int
 */
$config['patchPerPage'] = 20;

/**
 * Default number of bugs per page to show
 * @var int
 */
$config['bugsPerPage'] = 50;

/**
 * Default resolution
 * @var int
 */
$config['resolution'] = 3;


/**
 * Piwik Stats 
 */

/**
 * Piwik site ID
 * @var int
 */
$config['piwikId'] = 1;

/**
 * Piwik URL
 * @var string
 */
$config['piwikUri'] = 'http://stats.espix.net/';

/**
 * Piwik token
 * @var string
 */
$config['piwikToken'] = 'STRIPPED';

/**
 * Google API token
 * @var string
 */
$config['googleApiKey'] = 'STRIPPED';

/**
 * Google ShortUrl service
 * @var string
 */
$config['googleShortUrl'] = 'https://www.googleapis.com/urlshortener/v1';

/**
 * WS2 ShortUrl Service
 * @var string
 */
$config['ws2ShortUrl'] = 'http://ws2.be/gen.php?u=%s';

/**
 * Twitter config
 */

/**
 * Twitter User Token
 * @var string
 */
$config['twUserTok'] = 'STRIPPED';

/**
 * Twitter User Token Secret
 * @var string
 */
$config['twUserTokPriv'] = 'STRIPPED';


/**
 * Twitter Consumer Key
 * @var string
 */
$config['twConsKey'] = 'STRIPPED';



/**
 * Twitter Consumer Secret
 * @var string
 */
$config['twConsSec'] = 'STRIPPED';


/**
 * MOS Settings 
 */

/**
 * MOS Username
 * @var string
 */
$config['MOSuser'] = 'user@mail.net';

/**
 * MOS Password
 * @var string
 */
$config['MOSpass'] = 'larry123';

/**
 * Admin contact 
 */

/**
 * 
 * @var string
 */
$config['admin'] = "admin@wesunsolve.net";

/**
 * Address for outgoing e-mail
 * @var string
 */
$config['mailFrom'] = 'admin@wesunsolve.net';

/**
 * Name for outgoing e-mail
 * @var string
 */
$config['mailSubject'] = '[WeSunSolve]';

/**
 * Name for outgoing e-mail
 * @var string
 */
$config['mailName'] = 'We Sun Solve';

/**
 * Text version of email being sent as HTML
 * @var string
 */
$config['txtMailVersion'] = <<< EOF
You need to have a MUA capable of rendering HTML to read
the WeSunSolve emails.

You can consult the website http://wesunsolve.net if you
are not able to read this email, the information sent to you
should also be on the website...
EOF;

/**
 * Header of mailling list mails
 * @var string
 */
$config['mlist']['header'] = <<< EOF
<html>
 <head>
  <style type="text/css">
body {
  /* background-color: #ffffff; */
  background-color: #ddd;
  color: #000000;
  font-family: sans-serif;
  font-style: normal;
  font-size: 0.9em;
}
a {
  color: #3e6b8a;
  text-decoration: none;
}

a:hover {
  text-decoration: underline;
}
.red {
  color: red;
}
.orange {
  color: #ff9900;
}
.green {
  color: green;
}
.greentd {
  background-color: green;
}
.greentd a {
  color: #000000;
}
.orangetd {
  background-color: #ff9900;
}
.browntd {
  background-color: brown;
  color: #ffffff;
}
.browntd a {
  color: #ffffff;
}
.redtd {
  background-color: red;
  color: #ffffff;
}
.redtd a {
  color: #ffffff;
}
.violettd {
  background-color: violet;
  color: #ffffff;
}
.violettd a {
  color: #ffffff;
}
#legend {
  font-size: 0.8em;
  font-weight: bold;
}
h2 {
  margin-left: 2px;
  margin-bottom: 10px;
  margin-top: 5px;
}
h3 {
  margin-left: 7px;
  margin-bottom: 10px;
  margin-top: 5px;
}
h4 {
  margin-left: 12px;
  margin-bottom: 10px;
  margin-top: 5px;
}
h5 {
  margin-left: 20px;
  margin-bottom: 1px;
  margin-top: 3px;
}
.buglist li {
  background-image: url(http://wesunsolve.net/img/bug.png);
  background-repeat: no-repeat;
  background-position: 0;
  padding-left: 20px;
  margin-left:0px;
  padding-top: 2px;
  padding-bottom: 2px;
}
ul.buglist {
  list-style-type:none;
}
.buglist ul {
  list-style-type:none;
  list-style-position: inside;
  vertical-align: middle;
  padding: 0px;
  margin-left: 0px;
  padding-bottom: 0px;
}
.toclist li {
  background-image: url(http://wesunsolve.net/img/tag_red.png);
  background-repeat: no-repeat;
  background-position: 0;
  padding-left: 20px;
  margin-left:0px;
  padding-top: 2px;
  padding-bottom: 2px;
}
ul.toclist {
  list-style-type:none;
}
.toclist ul {
  list-style-type:none;
  list-style-position: inside;
  vertical-align: middle;
  padding: 0px;
  margin-left: 0px;
  padding-bottom: 0px;
}
  </style>
 </head>
 <body bgcolor="#ffffff">

EOF;

/**
 * Footer of mailling list mails
 * @var string
 */
$config['mlist']['footer'] = <<< EOF
  <p>--<br/>
<a href="http://wesunsolve.net">WeSunSolve!</a> mailling list system.<br/>
You are receiving this e-mail because you are member of <a href="http://wesunsolve.net">WeSunSolve!</a> community and
you have requested this mailling list subscription using your <a href="http://wesunsolve.net/panel">Panel</a> on WeSunSolve.net.
If you have question, suggestion or feedback regarding this mailling, don't hesitate to <a href="http://wesunsolve.net/contact">contact us</a>.
  </p>
 </body>
</html>
EOF;




/**
 * MySQL config 
 */

/**
 * Host string of MySQL Server
 * @var string
 */
$config['mysql']['host'] = '10.42.253.106';

/**
 * Username to connect to MySQL
 * @var string
 */
$config['mysql']['user'] = 'sunsolve';

/**
 * Password of MySQL connection
 * @var string
 */
$config['mysql']['pass'] = 'STRIPPED';

/**
 * Port of MySQL server
 * @var int
 */
$config['mysql']['port'] = 3306;

/**
 * Database name
 * @var string
 */
$config['mysql']['db'] = 'sunsolve';

/**
 * File to Log every query, FALSE to disable
 * @var string
 */
$config['mysql']['DEBUG'] = FALSE;

/**
 * File to Log SQL Errors, FALSE to disable
 * @var string
 */
$config['mysql']['ERRLOG'] = "/tmp/mysqlerr.log";

/**
 * File to Log null queries, FALSE to disable
 * @var string
 */
$config['mysql']['LOGNULL'] = FALSE;

/**
 * Curl settings 
 */

/**
 * Curl timeout
 * @var int
 */
$config['curl']['timeout'] = 10;

/**
 * Use a proxy with CURL ?
 * @var string
 */
$config['curl']['proxy'] = '';


/**
 * Various
 */

/**
 * Should we show the generation duration on page ?
 * @var bool
 */
$config['webgui']['time'] = true;

/**
 * default DateTime format
 * @var string
 */
$config['datetimeFormat'] = 'Y-m-d H:i:s';

/**
 * default Date format
 * @var string
 */
$config['dateFormat'] = 'Y-m-d';

/**
 * Website version
 * @var string
 */
$config['version'] = '2.0';

/**
 * DO NOT EDIT BELOW
 */

if ($config['webgui']['time']) {
 $start_time = microtime();
 $start_time = explode(" ",$start_time);
 $start_time = $start_time[1] + $start_time[0];
}

?>
