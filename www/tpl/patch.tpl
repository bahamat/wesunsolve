<?php
  $h = HTTP::getInstance();
  if (!$h->css) $h->fetchCSS();
?>
    <div id="d_content">
     <h2 class="grid_10 push_1 alpha omega">Patch <?php echo $patch->name(); ?></h2>
     <p id="titlecomment" class="push_4 grid_5"><?php echo count($patch->a_comments); ?> Comments | <a href="#comments">view</a> / <a href="/add_comment/id_on/<?php echo $patch->name(); ?>/type/patch">add</a></p>
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
    <h4><?php echo $patch->synopsis; ?></h4>
    <?php if (isset($l)) { ?>
	    <div id="add_uclist_form">
		  <select id="selectAddUCList" name="i">
		    <option value="-1" selected>Add to Custom List</option>
		    <?php foreach($l->a_uclists as $li) { ?>
		      <option value="<?php echo $li->id; ?>"><?php echo $li->name; ?></option>
		    <?php } ?>
		  </select>
		  <input type="button" name="Add" value="Add" onclick="addUCList('<?php echo $patch->name(); ?>', showMessage)"/>
		<div id="msg_uclist"></div>
	    </div>
     <?php } ?>
             <?php if ($patch->pca_bad) { ?>
		<p class="warning">You should consider NOT installing this patch, as it is WITHDRAWN !</p>
             <?php } ?>
             <?php if (!strcmp($patch->status, 'OBSOLETE')) { ?>
		<p class="warning">This patch is obsolete, consider upgrading to the latest release of this patch !</p>
             <?php } ?>
	     <h3>General informations</h3>
		<ul class="listinfo">
<?php if ($patch->pca_rec) { ?>
		 <li class="green">This is a recommended patch</li>
<?php } ?>
<?php if ($patch->pca_sec) { ?>
		 <li class="orange">This is a security patch</li>
<?php } ?>
<?php if (isset($patch->o_obsby) && $patch->o_obsby) { ?>
                 <li>Obsoleted by: <a href="/patch/id/<?php echo $patch->o_obsby->name(); ?>"><?php echo $patch->o_obsby->name(); ?></a></li>
<?php } ?>
<?php if ($patch->o_latest) { ?>
                 <li>Latest release of this patch: <a href="/patch/id/<?php echo $patch->o_latest->name(); ?>"><?php echo $patch->o_latest->name(); ?></a></li>
<?php } ?>
		 <li>Release date: <?php if($patch->releasedate) echo date(HTTP::getDateFormat(), $patch->releasedate); ?> [<a href="/ptimeline/pid/<?php echo $patch->name(); ?>">View Timeline</a>] [<a href="/ptimeline/pid/<?php echo $patch->patch; ?>">View All Revision Timeline</a>]</li>
		 <li>Detected status: <?php echo $patch->status; ?></li>
		 <li>Synopsis: <?php echo Patch::linkize($patch->synopsis); ?></li>
		 <li>Archive size: <?php echo $patch->filesize; ?> bytes (<?php echo round($patch->filesize / 1024 / 1024, 2); ?> MBytes)</li>
		 <li>Packages impacted: <?php foreach($patch->a_srv4pkg as $pkg) { echo $pkg.", "; } ?></li>
		 <li>Keywords: <?php foreach ($patch->a_keywords as $k) { echo $k->keyword.", "; } ?></li>
		 <li>Architecture: <?php echo $patch->data("arch"); ?></li>
		 <li>Solaris Release: <?php echo $patch->data("solaris_release"); ?></li>
		 <li>SunOS Release: <?php echo $patch->data("sunos_release"); ?></li>
		 <li>Unbundled Product: <?php echo $patch->data("unbundled_product"); ?></li>
		 <li>Unbundled Release: <?php echo $patch->data("unbundled_release"); ?></li>
<?php if (count($patch->a_cve)) { ?>
		 <li>Fixing CVE: <?php $i=0; foreach($patch->a_cve as $cve) { if ($i) echo ', '; echo $cve->link(); $i++; } ?></li>
<?php } ?>
		 <li>Xref: <?php echo Patch::linkize($patch->data("xref")); ?></li>
		 <li>Files: <a href="/files/id/<?php echo $patch->name(); ?>">click here</a></li>
                 <li><a href="/readme/id/<?php echo $patch->name(); ?>">View README</a></li>
 		 <?php if ($mreadme) { ?>
	         <li><a href="/diffr/type/patch/id/<?php echo $patch->name(); ?>">View differences between README versions</a></li>
		 <?php } ?>
                 <li><a href="https://getupdates.oracle.com/all_unsigned/<?php echo $patch->name().'.'.$patch->findExt(); ?>">Download</a> at Oracle MOS</li>
<?php if ($is_dl && $archive) { ?>
	 	 <li><a href="/pdl/p/<?php echo $patch->name(); ?>">Download</a> locally</a>
<?php } ?>
		</ul>
		<h3><a id="dep"></a>Bundles / Patch clusters</h3>
                <?php if (!count($patch->a_bundles)) { echo "<p>This patch is not integrated in any bundle</p>"; } else { ?>
                <ul>
                <?php foreach ($patch->a_bundles as $p) { ?>
                 <li><a href="/bundle/id/<?php echo $p->id; ?>"><?php echo $p->filename; ?></a> : <?php echo $p->synopsis; ?></li>
                <?php } ?>
                </ul>
                <?php } ?>
                <p><br/><a href="#top"><img alt="back to top" src="/img/arrow_up.png">back to top</a></p>
		<h3><a id="dep"></a>Patch Requirements</h3>
		<?php if (!count($patch->a_depend)) { echo "<p>There is no patch dependency for ".$patch->name()."</p>"; } else { ?>
		<ul>
		<?php foreach ($patch->a_depend as $p) { ?>
		 <li><a href="/patch/id/<?php echo $p->name(); ?>"><?php echo $p->name(); ?></a> : <?php echo $p->synopsis; ?></li>
		<?php } ?>
		</ul>
		<p><br/><a href="#top"><img alt="back to top" src="/img/arrow_up.png">back to top</a></p>
		<?php } ?>
		<h3><a id="obsoleted"></a>Patches obsoleted by this patch</h3>
                <?php if (!count($patch->a_obso)) { echo "<p>There is no patch obsoleted by ".$patch->name()."</p>"; } else { ?>
                <ul>
                <?php foreach ($patch->a_obso as $p) { ?>
                 <li><a href="/patch/id/<?php echo $p->name(); ?>"><?php echo $p->name(); ?></a> : <?php echo $p->synopsis; ?></li>
                <?php } ?>
                </ul>
		<p><br/><a href="#top"><img alt="back to top" src="/img/arrow_up.png">back to top</a></p>
                <?php } ?>
		<h3><a id="conflicts"></a>Patches that conflitcs with this patch</h3>
                <?php if (!count($patch->a_conflicts)) { echo "<p>There is no patch that conflicts with ".$patch->name()."</p>"; } else { ?>
                <ul>
                <?php foreach ($patch->a_conflicts as $p) { ?>
                 <li><a href="/patch/id/<?php echo $p->name(); ?>"><?php echo $p->name(); ?></a> : <?php echo $p->synopsis; ?></li>
                <?php } ?>
                </ul>
		<p><br/><a href="#top"><img alt="back to top" src="/img/arrow_up.png">back to top</a></p>
                <?php } ?>
		<h3><a id="bugids"></a>Bug IDs fixed with this patch:</h3>
		<?php if (!count($patch->a_bugids)) { echo "<p>There is no detected bugid fixed with this patch</p>"; } else { ?>
		<ul>
		<?php foreach($patch->a_bugids as $bug) { ?>
		 <li><a href="/bugid/id/<?php echo $bug->id; ?>"><?php echo $bug->id."</a>"; if (!empty($bug->synopsis)) { echo ": ".Patch::linkize(htmlentities($bug->synopsis)); } ?></li>
		<?php } ?>
		</ul>
		<p><br/><a href="#top"><img alt="back to top" src="/img/arrow_up.png">back to top</a></p>
		<?php } ?>
		<h3><a id="bugids_incl"></a>Bug IDs fixed with included patches:</h3>
		<?php foreach($patch->a_previous as $p) { ?>
		  <h4>from <?php echo $p->link(); ?></h4>
		  <ul>
  		<?php   foreach($p->a_bugids as $bug) { ?>
		   <li><a href="/bugid/id/<?php echo $bug->id; ?>"><?php echo $bug->id."</a>"; if (!empty($bug->synopsis)) { echo ": ".Patch::linkize(htmlentities($bug->synopsis)); } ?></li>
		<?php   } ?>
		  </ul>
		<?php } ?>
 	        <h3><a id="comments"></a>Comments</h3>
		<?php if (!count($patch->a_comments)) { ?>
		  <p>There is no comments for this patch yet. <a href="/add_comment/id_on/<?php echo $patch->name(); ?>/type/patch">add one</a>.</p>
		<?php } else { ?>
 		  <ul>
		<?php foreach ($patch->a_comments as $c) { $c->fetchLogin(); ?>
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
