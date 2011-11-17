<?php
  $h = HTTP::getInstance();
  if (!$h->css) $h->fetchCSS();
?>

    <div id="d_content">
     <h2 class="grid_10 push_1 alpha omega">File search result: <?php echo $mfile->name; ?></h2>
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
    <a id="top"></a>
	     <h3>File Information</h3>
		<ul class="listinfo">
		 <li>File size: <?php echo $mfile->size; ?> bytes (<?php echo round($mfile->size / 1024 / 1024, 2); ?> MBytes)</li>
		 <li>MD5 Sum: <?php echo $mfile->md5; ?></li>
		 <li>SHA1 Sum: <?php echo $mfile->sha1; ?></li>
		</ul>
		<h3><a id="dep"></a>Solaris patches that match this file</h3>
                <?php if (!count($patches)) { echo "<p>This file is not listed in any patch</p>"; } else { ?>
                <ul class="listinfo">
                <?php foreach ($patches as $p) { ?>
                 <li><a href="/patch/id/<?php echo $p->name(); ?>"><?php echo $p->name(); ?></a>: <?php echo $p->synopsis; ?></li>
                <?php } ?>
                </ul>
                <?php } ?>
               </div><!-- d_content_box -->
  </div><!-- grid_19 -->
 </div><!-- d_content -->
