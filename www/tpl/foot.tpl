<?php if (isset($start_time)) {
 $stop_time = microtime();
 $stop_time = explode(" ", $stop_time);
 $stop_time = $stop_time[1] + $stop_time[0];
 $total = $stop_time - $start_time;
?>
 <div id="foot">
  <p><img src="http://ians.be/img/sponsors/debian_powered.png" alt="Powered by Debian"/>
     <img src="http://ians.be/img/sponsors/php_powered.png"  alt="Powered by PHP5"/>
     <img src="http://ians.be/img/sponsors/mysql_powered.png"  alt="Powered by MySQL"/>
     <img src="http://ians.be/img/sponsors/apache_powered.png"  alt="Powered by Apache"/>
  </p>
     
  <p>This page was generated in <?php echo round($total, 3); ?> seconds.</p>
 </div> 
<?php } ?>
<script type="text/javascript">
var pkBaseURL = (("https:" == document.location.protocol) ? "https://stats.espix.net/" : "http://stats.espix.net/");
document.write(unescape("%3Cscript src='" + pkBaseURL + "piwik.js' type='text/javascript'%3E%3C/script%3E"));
</script><script type="text/javascript">
try {
var piwikTracker = Piwik.getTracker(pkBaseURL + "piwik.php", 1);
piwikTracker.trackPageView();
piwikTracker.enableLinkTracking();
} catch( err ) {}
</script><noscript><p><img src="http://stats.espix.net/piwik.php?idsite=1" style="border:0" alt="" /></p></noscript>
  </body>
</html>
