<?php
  $h = HTTP::getInstance();
  if (!$h->css) $h->fetchCSS();
?>
    <div id="d_content">
     <h2 class="grid_10 push_1 alpha omega">Package <?php echo $pkg->shortName(); ?></h2>
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
    <h4><?php echo $pkg->summary; ?></h4>
             <?php if ($pkg->o_latest) { ?>
                <p class="warning">There is a newer release of this package which may fixes some bugs, consider upgrading !</p>
             <?php } ?>
	     <h3>General informations</h3>
	      <ul class="listinfo">
<?php if ($pkg->o_latest) { ?>
		<li>Latest release: <?php echo $pkg->o_latest->link(); ?></li>
<?php } ?>
		 <li>Full name: <?php echo $pkg; ?></li>
		 <li>Summary: <?php echo $pkg->summary; ?></li>
		 <li>Description: <?php echo $pkg->desc; ?></li>
		 <li>IPS Path: <?php echo $pkg->path; ?></li>
		 <li>Product version: <?php echo $pkg->version; ?></li>
		 <li>Build version: <?php echo $pkg->buildver; ?></li>
		 <li>Branch version: <?php echo $pkg->branchver; ?></li>
		 <li>Full FMRI: <?php echo $pkg->fmri; ?></li>
		 <li>Release date: <?php if($pkg->pstamp) echo date(HTTP::getDateFormat(), $pkg->pstamp); ?></li>
	       </ul>
             <h3>Bugs affecting this package release</h3>
<?php if (count($pkg->a_affect)) { ?>
                <ul>
                <?php foreach($pkg->a_affect as $bug) { ?>
                 <li><a href="/bugid/id/<?php echo $bug->id; ?>"><?php echo $bug->id."</a>"; if (!empty($bug->synopsis)) { echo ": ".htmlentities($bug->synopsis); } ?></li>
                <?php } ?>
                </ul>
<?php } else { ?>
                <p>There is no current known bugs affecting this package release</p>
<?php } ?>
	     <h3>Bugs fixed in this package release</h3>
<?php if (count($pkg->a_bugids)) { ?>
		<ul>
		<?php foreach($pkg->a_bugids as $bug) { ?>
                 <li><a href="/bugid/id/<?php echo $bug->id; ?>"><?php echo $bug->id."</a>"; if (!empty($bug->synopsis)) { echo ": ".htmlentities($bug->synopsis); } ?></li>
		<?php } ?>
		</ul>
<?php } else { ?>
		<p>There is no related bug fixed in this package release</p>
<?php } ?>
             <h3>Bugs fixed in previous package release</h3>
<?php if (count($pkg->a_previous)) { ?>
                <?php foreach($pkg->a_previous as $pkg) { ?>
		 <h4>Bugid fixed by <?php echo $pkg->link(); ?></h4>
		 <?php if (count($pkg->a_bugids)) { ?>
	          <ul>
		  <?php foreach($pkg->a_bugids as $bug) { ?>
                   <li><a href="/bugid/id/<?php echo $bug->id; ?>"><?php echo $bug->id."</a>"; if (!empty($bug->synopsis)) { echo ": ".htmlentities($bug->synopsis); } ?></li>
                  <?php } ?>
                  </ul>
                <?php } else { ?>
                  <p>There is no bug fixed by this release for this package</p>
                <?php } ?>
           <?php } ?>
<?php } else { ?>
                <p>There is no previous release for this package</p>
<?php } ?>

 	        <h3><a id="comments"></a>Comments</h3>
		<?php if (!count($pkg->a_comments)) { ?>
		  <p>There is no comments for this pkg yet. <a href="/add_comment/id_on/<?php echo $pkg->id; ?>/type/pkg">add one</a>.</p>
		<?php } else { ?>
 		  <ul>
		<?php foreach ($pkg->a_comments as $c) { $c->fetchLogin(); ?>
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
