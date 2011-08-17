<?php if (isset($start_time)) {
   $stop_time = microtime();
   $stop_time = explode(" ", $stop_time);
   $stop_time = $stop_time[1] + $stop_time[0];
   $total = $stop_time - $start_time;
 }
?>
    </div><!-- grid_19 --> 
 
  </div><!-- container_24 --> 
 
  <div class="container_24"> 
   <div id="footer" class="grid_24 d_bar"> 
    <p><?php if (isset($total)) echo round($total, 3)." seconds |"; ?> &copy; 2011 <a href="http://wesunsolve.net">We Sun Solve!</a> | Hosting by <a href="http://www.espix.org">Espix Network</a> | <a href="/disclaimer">disclaimer</a> | <a href="/contact">contact</a></p> 
   </div><!-- d_footer --> 
  </div><!-- container_24 --> 

  <!-- Piwik -->
  <script type="text/javascript">
    var pkBaseURL = (("https:" == document.location.protocol) ? "https://stats.espix.net/" : "http://stats.espix.net/");
    document.write(unescape("%3Cscript src='" + pkBaseURL + "piwik.js' type='text/javascript'%3E%3C/script%3E"));
  </script>
  <script type="text/javascript">
    try {
    var piwikTracker = Piwik.getTracker(pkBaseURL + "piwik.php", 1);
    piwikTracker.trackPageView();
    piwikTracker.enableLinkTracking();
    } catch( err ) {}
  </script><noscript><p><img src="http://stats.espix.net/piwik.php?idsite=1" style="border:0" alt="" /></p></noscript>
  <!-- End Piwik Tracking Code -->
 </body> 
</html>
