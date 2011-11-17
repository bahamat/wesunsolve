<?php
  $h = HTTP::getInstance();
?>
    <div id="d_content">
     <h2 class="grid_10 push_1 alpha omega">OS Releases referenced</h2>
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
<?php if (isset($rls)) {
        foreach($rls as $r) { $cfiles = $r->countFiles(); if (!$cfiles) continue; ?>
	     <h3>Solaris <?php echo $r->major.' (Update '.$r->update.') '.$r->dstring.' ('.$r->arch.')'; ?></h3>
		<ul class="listinfo">
		 <li>Number of files referenced: <?php echo $cfiles; ?></li>
		 <li>Number of packages referenced: <?php echo $r->countPackages(); ?></li>
		 <li>Total size of referenced files: <?php echo $r->countSize(); ?> MBytes</li>
		</ul>
<?php   }
      }
?>
   </div><!-- d_content_box -->
  </div><!-- grid_19 -->
 </div><!-- d_content -->
