   <div class="content">
    <h2>Welcome to SunSolve</h2>
    <p><b>DISCLAMER:</b> This website and the informations shown here are provided "as-is" and aren't an official source.
	We could not be held responsible for accuracy of the infromation provided here.</p>
    <h3>Why ?</h3>
    <p>When the merge of Sun and Oracle has been made, we lost a lot of tools that were present on the famous SunSolve. Like the ability
       to search efficiently for patches, bugids, sun alerts and so on. This website has the aim to provide part of this informations by
       indexing all the patches and bugids that we could gather publicly. We want to allow easy search to ease the life of solaris sysadmins.
    </p>
    <h4>Site news</h4>
<?php
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
?>
    <ul>
<?php foreach($news as $n) { ?>
      <li><i><?php echo date('d/m/Y', $n->date); ?></i>: <?php echo $n->synopsis; ?> - <?php if (!empty($n->link)) echo "<a href=\"".$n->link."\">Link</a>"; ?></li>
<?php } ?>
    </ul>
    <h4>Database overview</h4>
    <p>
      Here are the stats of our database:
    </p>
      <ul>
       <li>Number of patches registered: <?php echo MysqlCM::getInstance()->count("patches"); ?></li>
       <li>Number of OBSOLETED patches: <?php echo MysqlCM::getInstance()->count("patches", "WHERE `status`='OBSOLETE'"); ?></li>
       <li>Number of Unresolved patches: <?php echo MysqlCM::getInstance()->count("patches", "WHERE `synopsis`=''"); ?></li>
       <li>Number of BugIDs registered: <?php echo MysqlCM::getInstance()->count("bugids"); ?></li>
       <li>Number of BugIDs details available: <?php echo MysqlCM::getInstance()->count("bugids", "WHERE `available`='1'"); ?></li>
       <li>Patches released past year: <?php echo MysqlCM::getInstance()->count("patches", "WHERE `releasedate` > ".(time() - (3600*24*31*12))); ?></li>
       <li>Patches released past month: <?php echo MysqlCM::getInstance()->count("patches", "WHERE `releasedate` > ".(time() - (3600*24*31))); ?></li>
       <li>Patches released past week: <?php echo MysqlCM::getInstance()->count("patches", "WHERE `releasedate` > ".(time() - (3600*24*7))); ?></li>
<?php
$index = "SUM(`filesize`/1024/1024) as total";
$table = "`patches`";
$where="";
if (($idx = mysqlCM::getInstance()->fetchIndex($index, $table, $where))) {
  $total = $idx[0]['total'];
}
?>
       <li>Total size of the patches repository: <?php echo round($total, 2); ?> MBytes (<?php echo round($total / 1024, 2); ?> GBytes)</li>
       <li>Number of Keywords: <?php echo MysqlCM::getInstance()->count("keywords"); ?></li>
       <li>Number of Files: <?php echo MysqlCM::getInstance()->count("files"); ?></li>
      </ul>
    <hr/>
     <address></address>
   </div>
