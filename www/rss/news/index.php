<?php
 require_once("../../../libs/autoload.lib.php");
 require_once("../../../libs/config.inc.php");
 $m = mysqlCM::getInstance();
 if ($m->connect()) {
    die($argv[0]." Error with SQL db: ".$m->getError()."\n");
 }

 /* Fetch last news */
  $news = array();
  $table = "`rss_news`";
  $index = "`id`";
  $where = " ORDER BY `date` DESC LIMIT 0,20";

  if (($idx = mysqlCM::getInstance()->fetchIndex($index, $table, $where)))
  {
    foreach($idx as $t) {
      $g = new News($t['id']);
      $g->fetchFromId();
      array_push($news, $g);
    }
  }

 $now = date("D, j M Y G:i:s T");
 header("Content-Type: application/xml; charset=ISO-8859-1"); 

 HTTP::Piwik("Site News RSS Feed");

?> 
<rss version="2.0">
  <channel>
    <title>We sun solve - Site News</title>
    <link>http://wesunsolve.net</link>
    <description>Latest site news</description>
    <language>en-us</language>
    <pubDate><?php echo $now; ?></pubDate>
    <lastBuildDate><?php echo $now; ?></lastBuildDate>
    <docs>http://wesunsolve.net/rss/news</docs>
    <generator>We Sun Solve</generator>
    <managingEditor><?php echo $config['mailFrom']; ?></managingEditor>
    <webMaster><?php echo $config['mailFrom']; ?></webMaster>
<?php foreach ($news as $n) { ?>
    <item>
     <title><?php echo $n->synopsis; ?></title>
     <link><?php echo $n->link ?></link>
     <pubDate><?php if ($n->date) echo date("D, j M Y G:i:s T", $n->date); ?></pubDate>
    </item>
<?php } ?>
  </channel>
</rss>
