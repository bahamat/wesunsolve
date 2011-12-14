<?php
 require_once("../../../libs/autoload.lib.php");
 require_once("../../../libs/config.inc.php");
 $m = mysqlCM::getInstance();
 if ($m->connect()) {
    die($argv[0]." Error with SQL db: ".$m->getError()."\n");
 }

 /* Fetch last pkg */
 $bf = array();
 $table = "(select id,bugid,pstamp from pkg left join jt_pkg_bugids on pkg.id=jt_pkg_bugids.id_pkg where id_pkg is not null order by pkg.pstamp DESC) as p";
 $index = "`bugid`, `id`";
 $where = "GROUP BY `bugid` LIMIT 0,20";

if (($idx = mysqlCM::getInstance()->fetchIndex($index, $table, $where)))
  {
    foreach($idx as $t) {
      $g = new Bugid($t['bugid']);
      $g->fetchFromId();
      $g->o_fixed = new Pkg($t['id']);
      $g->o_fixed->fetchFromId();
      array_push($bf, $g);
    }
  }
 $now = date("D, j M Y G:i:s T");

 header("Content-Type: application/xml; charset=ISO-8859-1"); 
 HTTP::Piwik("Latest Solaris 11 Bugs fixed RSS Feed");

?> 
<rss version="2.0">
  <channel>
    <title>We sun solve - Latest Solaris 11 Bugs fixed</title>
    <link>http://wesunsolve.net</link>
    <description>Latest Solaris 11 Bugs fixed</description>
    <language>en-us</language>
    <pubDate><?php echo $now; ?></pubDate>
    <lastBuildDate><?php echo $now; ?></lastBuildDate>
    <docs>http://wesunsolve.net/rss/lbugfix</docs>
    <generator>We Sun Solve</generator>
    <managingEditor><?php echo $config['mailFrom']; ?></managingEditor>
    <webMaster><?php echo $config['mailFrom']; ?></webMaster>
<?php foreach ($bf as $p) { ?>
    <item>
     <title><?php echo $p->id; ?>: <?php echo $p->synopsis; ?></title>
     <link>http://wesunsolve.net/bugid/id/<?php echo $p->id; ?></link>
     <description>Fixed by <?php echo $p->o_fixed->name(); ?></description>
     <pubDate><?php if ($p->o_fixed->pstamp) echo date("D, j M Y G:i:s T", $p->o_fixed->pstamp); ?></pubDate>
    </item>
<?php } ?>
  </channel>
</rss>
