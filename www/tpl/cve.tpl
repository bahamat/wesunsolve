<?php
  $h = HTTP::getInstance();
  if (!$h->css) $h->fetchCSS();
?>
    <div id="d_content">
     <h2 class="grid_10 push_1 alpha omega">CVE Security alert: <?php echo $cve->name; ?></h2>
     <p id="titlecomment" class="push_4 grid_5"><?php echo count($cve->a_comments); ?> Comments | <a href="#comments">view</a> / <a href="/add_comment/id_on/<?php echo $cve->id; ?>/type/cve">add</a></p>
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
    <h4><?php echo $cve->name; ?>: affect <?php echo $cve->affect; ?></h4>
	     <h3>Description</h3>
<p><?php echo $cve->desc; ?></p>
	     <h3>General informations</h3>
	      <ul class="listinfo">
		 <li>Name: <?php echo $cve; ?></li>
		 <li>First release: <?php echo date(HTTP::getDateFormat(), $cve->released); ?></li>
		 <li>Last revision: <?php echo date(HTTP::getDateFormat(), $cve->revised); ?></li>
		 <li>Affect: <?php echo $cve->affect; ?></li>
		 <li>Score: <?php echo $cve->score; ?></li>
		 <li>Severity: <?php echo $cve->severity; ?></li>
		 <li>Added on: <?php echo date(HTTP::getDateFormat(), $cve->added); ?></li>
		 <li><a href="http://web.nvd.nist.gov/view/vuln/detail?vulnId=<?php echo $cve; ?>">External informations</a></li>
	       </ul>
             <h3>Patches fixing this alert:</h3>
<?php if (count($cve->a_patches)) { ?>
                <ul>
                <?php foreach($cve->a_patches as $p) { ?>
                 <li><?php echo $p->link(); ?> : <?php echo $p->synopsis; ?></li>
                <?php } ?>
                </ul>
<?php } else { ?>
                <p>There is no current patches fixing this alert</p>
<?php } ?>

 	        <h3><a id="comments"></a>Comments</h3>
		<?php if (!count($cve->a_comments)) { ?>
		  <p>There is no comments for this cve yet. <a href="/add_comment/id_on/<?php echo $cve->id; ?>/type/cve">add one</a>.</p>
		<?php } else { ?>
 		  <ul>
		<?php foreach ($cve->a_comments as $c) { $c->fetchLogin(); ?>
			<li>on <i><?php echo date(HTTP::getDateFormat(), $c->added);  ?></i>, <b><?php echo $c->o_login->username; ?></b> said :  <?php echo $c->show(); ?>
			<?php if (isset($l) && ($l->id == $c->id_login)) { ?>
			  (<a href="/del_comment/id/<?php echo $c->id; ?>">delete</a>)
			<?php } ?>
			<hr/></li>
		<?php } ?>
 		  </ul>
		<?php } ?>
		<p><br/><a href="#top"><img alt="back to top" src="/img/arrow_up.png">back to top</a></p>
   </div><!-- d_content_box -->
  </div><!-- grid_19 -->
 </div><!-- d_content -->
