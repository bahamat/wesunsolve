<?php
  $h = HTTP::getInstance();
  if (!$h->css) $h->fetchCSS();
?>  
    <div id="d_content">
     <h2 class="grid_10 push_1 alpha omega">Figures and numbers</h2>
     <div class="clear"></div>
     <div class="grid_<?php echo ($h->css->s_total - $h->css->s_menu); ?> alpha omega">
      <div class="d_content_box">
       <div style="height: 30px" class="push_<?php echo $h->css->p_snet; ?> grid_<?php echo $h->css->s_snet; ?>">
        <div class="addthis_toolbox addthis_default_style" id="snet">
         <a class="addthis_button_facebook"></a>
         <a class="addthis_button_twitter"></a>
         <a class="addthis_button_email"></a>
         <a class="addthis_button_print"></a>
         <a class="addthis_button_google_plusone"></a>
        </div>
       </div>
       <div class="clear clearfix"></div>
    <h3>Visitors</h3>
     <p>Monthly visits graph:</p>
     <div style="text-align: center;"><img src="./static/visits_month.png" alt="Monthly visits graph"/></div>
     <p>Yearly visits graph:</p>
     <div style="text-align: center;"><img src="./static/visits_year.png" alt="Monthly visits graph"/></div>
    <h3>Database</h3>
    <p>
      Here are the stats of our database:
    </p>
      <ul class="listtick">
       <li>Number of registered users: <?php echo MysqlCM::getInstance()->count("login"); ?></li>
       <li>Number of patches registered: <?php echo MysqlCM::getInstance()->count("patches"); ?></li>
       <li>Number of readmes version gathered: <?php echo MysqlCM::getInstance()->count("p_readmes"); ?></li>
       <li>Number of checksums registered: <?php echo MysqlCM::getInstance()->count("checksums"); ?></li>
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

$index = "SUM(`size`/1024/1024) as total";
$table = "`bundles`";
$where="";
if (($idx = mysqlCM::getInstance()->fetchIndex($index, $table, $where))) {
  $total += $idx[0]['total'];
}
?>
       <li>Total size of the patches repository: <?php echo round($total, 2); ?> MBytes (<?php echo round($total / 1024, 2); ?> GBytes)</li>
       <li>Number of Keywords: <?php echo MysqlCM::getInstance()->count("keywords"); ?></li>
       <li>Number of Files: <?php echo MysqlCM::getInstance()->count("files"); ?></li>
       <li>Number of Packages: <?php echo MysqlCM::getInstance()->count("pkg"); ?></li>
       <li>Number of CVE: <?php echo MysqlCM::getInstance()->count("cve"); ?></li>
      </ul>
   </div><!-- d_content_box -->
  </div><!-- grid_19 -->
 </div><!-- d_content -->
