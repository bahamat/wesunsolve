<?php
 require_once("../../../libs/autoload.lib.php");
 require_once("../../../libs/config.inc.php");
 $m = mysqlCM::getInstance();
 if ($m->connect()) {
    die($argv[0]." Error with SQL db: ".$m->getError()."\n");
 }

 /* Fetch last patches */
  $bundles = array();
  $table = "`bundles`";
  $index = "`id`";
  $where = " WHERE `lastmod`!='0' ORDER BY `lastmod` DESC LIMIT 0,20";

  if (($idx = mysqlCM::getInstance()->fetchIndex($index, $table, $where)))
  {
    foreach($idx as $t) {
      $g = new Bundle($t['id']);
      $g->fetchFromId();
      array_push($bundles, $g);
    }
  }

 $now = date("D, j M Y G:i:s T");

 header("Content-Type: application/xml; charset=ISO-8859-1"); 
 HTTP::Piwik("Last bundles updates RSS Feed");


?> 
<rss version="2.0">
  <channel>
    <title>We sun solve - Last bundles</title>
    <link>http://wesunsolve.net</link>
    <description>Last bundles released</description>
    <language>en-us</language>
    <pubDate><?php echo $now; ?></pubDate>
    <lastBuildDate><?php echo $now; ?></lastBuildDate>
    <docs>http://wesunsolve.net/rss/bundles</docs>
    <generator>We Sun Solve</generator>
    <managingEditor>tgo@espix.net</managingEditor>
    <webMaster>tgo@espix.net</webMaster>
<?php foreach ($bundles as $p) { ?>
    <item>
     <title><?php echo $p->filename; ?>: <?php echo $p->synopsis; ?></title>
     <link>http://wesunsolve.net/bundle/id/<?php echo $p->id; ?></link>
     <description><?php echo $p->synopsis; ?></description>
     <pubDate><?php if ($p->lastmod) echo date("D, j M Y G:i:s T", $p->lastmod); ?></pubDate>
    </item>
<?php } ?>
  </channel>
</rss>
