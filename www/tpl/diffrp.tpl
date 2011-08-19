<?php
  $h = HTTP::getInstance();
  if (!$h->css) $h->fetchCSS();
?>  
    <div id="d_content">
     <h2 class="grid_10 push_1 alpha omega">Diff README for <?php echo $patch->name(); ?></h2>
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
    <a name="top"></a>
    <h4>Diff for old readme version of <?php echo $patch->name(); ?></h4>

    <div class="ctable">
     <table class="ctable">
      <tr>
<?php $i=0; $prev = null; $n = count($diff) - 1; foreach($diff as $d => $di) { ?>
      <tr><td class="tdtitle"><?php if (!$i) { 
			echo "Before ".date($config['dateFormat'], $d); 
		    } else {
			echo "Between ".date($config['dateFormat'], $prev)." and ".date($config['dateFormat'], $d); 
		    }
		    $prev = $d;
	      ?> <a href="/readme/id/<?php echo $patch->name(); ?><?php if ($i != $n) echo "-$d"; ?>">View full README</a>
	  </td></tr>
      <tr>
       <td class="tdcode"><?php echo nl2br($di); ?></td>
      </tr>
<?php $i++; } ?>
     </table>
    </div>
 
    <p><br/><a href="#top"><img src="/img/arrow_up.png">back to top</a></p>
   </div><!-- d_content_box -->
  </div><!-- grid_19 -->
 </div><!-- d_content -->
