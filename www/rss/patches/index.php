<?php
 require_once("../../../libs/autoload.lib.php");
 require_once("../../../libs/config.inc.php");
 $m = mysqlCM::getInstance();
 if ($m->connect()) {
    die($argv[0]." Error with SQL db: ".$m->getError()."\n");
 }

 /* Fetch last patches */
  $patches = array();
  $table = "`patches`";
  $index = "`patch`, `revision`";
  $where = " WHERE `releasedate`!='' ORDER BY `releasedate` DESC LIMIT 0,80";

  if (($idx = mysqlCM::getInstance()->fetchIndex($index, $table, $where)))
  {
    foreach($idx as $t) {
      $g = new Patch($t['patch'], $t['revision']);
      $g->fetchFromId();
      array_push($patches, $g);
    }
  }
 $now = date("D, j M Y G:i:s T");

 header("Content-Type: application/xml; charset=ISO-8859-1"); 
 HTTP::Piwik("Latest Patches RSS Feed");

?> 
<rss version="2.0">
  <channel>
    <title>We sun solve - Latest patches</title>
    <link>http://wesunsolve.net</link>
    <description>Latest patches</description>
    <language>en-us</language>
    <pubDate><?php echo $now; ?></pubDate>
    <lastBuildDate><?php echo $now; ?></lastBuildDate>
    <docs>http://wesunsolve.net/rss/patches</docs>
    <generator>We Sun Solve</generator>
    <managingEditor><?php echo $config['mailFrom']; ?></managingEditor>
    <webMaster><?php echo $config['mailFrom']; ?></webMaster>
<?php foreach ($patches as $p) { ?>
    <item>
     <title><?php echo $p->name(); ?>: <?php echo $p->synopsis; ?></title>
     <link>http://wesunsolve.net/patch/id/<?php echo $p->name(); ?></link>
     <description><?php echo $p->synopsis; ?></description>
     <pubDate><?php if ($p->releasedate) echo date("D, j M Y G:i:s T", $p->releasedate); ?></pubDate>
    </item>
<?php } ?>
  </channel>
</rss>
