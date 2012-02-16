<?php
 require_once("../../../libs/autoload.lib.php");
 require_once("../../../libs/config.inc.php");
 $m = mysqlCM::getInstance();
 if ($m->connect()) {
    die($argv[0]." Error with SQL db: ".$m->getError()."\n");
 }

 /* Fetch last pkg */
  $pkgs = array();
  $table = "`pkg`, `jt_pkg_ips` jt";
  $index = "`id`";
  $where = " WHERE jt.id_pkg=pkg.id AND jt.id_ips=1 ORDER BY `pkg`.`pstamp` DESC LIMIT 0,20";


  if (($idx = mysqlCM::getInstance()->fetchIndex($index, $table, $where)))
  {
    foreach($idx as $t) {
      $g = new Pkg($t['id']);
      $g->fetchFromId();
      array_push($pkgs, $g);
    }
  }
 $now = date("D, j M Y G:i:s T");

 header("Content-Type: application/xml; charset=ISO-8859-1"); 
 HTTP::Piwik("Latest Solaris 11 Packages RSS Feed");

?> 
<rss version="2.0">
  <channel>
    <title>We sun solve - Latest Solaris 11 Packages</title>
    <link>http://wesunsolve.net</link>
    <description>Latest Solaris 11 Packages</description>
    <language>en-us</language>
    <pubDate><?php echo $now; ?></pubDate>
    <lastBuildDate><?php echo $now; ?></lastBuildDate>
    <docs>http://wesunsolve.net/rss/s11pkg</docs>
    <generator>We Sun Solve</generator>
    <managingEditor><?php echo $config['mailFrom']; ?></managingEditor>
    <webMaster><?php echo $config['mailFrom']; ?></webMaster>
<?php foreach ($pkgs as $p) { ?>
    <item>
     <title><?php echo $p->name(); ?>: <?php echo $p->summary; ?></title>
     <link>http://wesunsolve.net/pkg/id/<?php echo $p->id; ?></link>
     <description><?php echo $p->desc; ?></description>
     <pubDate><?php if ($p->pstamp) echo date("D, j M Y G:i:s T", $p->pstamp); ?></pubDate>
    </item>
<?php } ?>
  </channel>
</rss>
