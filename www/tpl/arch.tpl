<?php
  $h = HTTP::getInstance();
  if (!$h->css) $h->fetchCSS();
?>  
    <div id="d_content">
     <h2 class="grid_10 push_1 alpha omega">Architecture details</h2>
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
     <h3>Database server</h3>
      <p style="text-align: center">
      <img src="https://noc.espix.net/graphs/graphs/graph_354_5.png" alt="VPN Traffic"/><br/>
      <img src="https://noc.espix.net/graphs/graphs/graph_352_5.png" alt="DB Server Memory"/><br/>
      <img src="https://noc.espix.net/graphs/graphs/graph_350_5.png" alt="DB Server CPU"/><br/>
      <img src="https://noc.espix.net/graphs/graphs/graph_351_5.png" alt="DB Server Load Avg"/><br/>
      </p>
    <h3>Processing and web server</h3>
      <p style="text-align: center">
      <img src="https://noc.espix.net/graphs/graphs/graph_432_5.png" alt="VPN Traffic"/><br/>
      <img src="https://noc.espix.net/graphs/graphs/graph_431_5.png" alt="Inet Traffic"/><br/>
      <img src="https://noc.espix.net/graphs/graphs/graph_430_5.png" alt="FE Server Memory"/><br/>
      <img src="https://noc.espix.net/graphs/graphs/graph_428_5.png" alt="FE Server CPU"/><br/>
      <img src="https://noc.espix.net/graphs/graphs/graph_429_5.png" alt="FE Server Load Avg"/><br/>
      </p>
   </div><!-- d_content_box -->
  </div><!-- grid_19 -->
 </div><!-- d_content -->
