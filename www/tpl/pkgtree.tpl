<?php
  $h = HTTP::getInstance();
  if (!$h->css) $h->fetchCSS();
?>
    <div id="d_content">
     <h2 class="grid_10 push_1 alpha omega">Package Browser</h2>
     <p id="titlecomment" class="push_4 grid_5"><?php echo count($pkg->a_comments); ?> Comments | <a href="#comments">view</a> / <a href="/add_comment/id_on/<?php echo $pkg->id; ?>/type/pkg">add</a></p>
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
     <ul>
       <li class="treeL1">/ (<?php echo Pkg::countPkgPath('/'); ?><img alt="Packages" src="./img/page_world.png"/> <?php echo count($tree['/']); ?><img alt="Sub Folders" src="./img/folder.png"/>)</li>
<?php  foreach($tree['/'] as $dir => $content) { ?>
       <li class="treeL2"><a href="/pkgtree/c/<?php echo $dir; ?>"><?php echo $dir; ?></a> (<?php echo Pkg::countPkgPath('/'.$dir.'/'); ?><img alt="Packages" src="./img/page_world.png"/> <?php echo count($content); ?><img alt="Sub Folders" src="./img/folder.png"/>)</li>
<?php } ?>
     </ul>
    <p><br/><a href="#top"><img alt="back to top" src="/img/arrow_up.png">back to top</a></p>
   </div><!-- d_content_box -->
  </div><!-- grid_19 -->
 </div><!-- d_content -->
