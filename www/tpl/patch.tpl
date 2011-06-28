	   <div class="content">
	    <h2>Patch <?php echo $patch->name(); ?></h2>
             <?php if ($patch->pca_bad) { ?>
		<p class="warning">You should consider NOT installing this patch, as it is WITHDRAWN !</p>
             <?php } ?>
             <?php if (!strcmp($patch->status, 'OBSOLETE')) { ?>
		<p class="warning">This patch is obsolete, consider upgrading to the latest release of this patch !</p>
             <?php } ?>
	     <h3>General informations</h3>
		<ul>
<?php if ($patch->pca_rec) { ?>
		 <li class="green">This is a recommended patch</li>
<?php } ?>
<?php if ($patch->pca_sec) { ?>
		 <li class="orange">This is a security patch</li>
<?php } ?>
		 <li>Release date: <?php if($patch->releasedate) echo date('d/m/Y', $patch->releasedate); ?></li>
		 <li>Detected status: <?php echo $patch->status; ?></li>
		 <li>Synopsis: <?php echo $patch->synopsis; ?></li>
		 <li>Archive size: <?php echo $patch->filesize; ?> bytes (<?php echo round($patch->filesize / 1024 / 1024, 2); ?> MBytes)</li>
		 <li>Keywords: <?php foreach ($patch->a_keywords as $k) { echo $k->keyword.", "; } ?></li>
		 <li>Architecture: <?php echo $patch->data("arch"); ?></li>
		 <li>Solaris Release: <?php echo $patch->data("solaris_release"); ?></li>
		 <li>SunOS Release: <?php echo $patch->data("sunos_release"); ?></li>
		 <li>Unbundled Product: <?php echo $patch->data("unbundled_product"); ?></li>
		 <li>Unbundled Release: <?php echo $patch->data("unbundled_release"); ?></li>
		 <li>Xref: <?php echo $patch->data("xref"); ?></li>
		 <li>Files: <a href="/files/id/<?php echo $patch->name(); ?>">click here</a></li>
                 <li><a href="/readme/id/<?php echo $patch->name(); ?>">View README</a></li>
  		 <?php if ($patch->o_latest) { ?>
                 <li>Latest release of this patch: <a href="/patch/id/<?php echo $patch->o_latest->name(); ?>"><?php echo $patch->o_latest->name(); ?></a></li>
                 <?php } ?>
                 <li><a href="https://getupdates.oracle.com/all_unsigned/<?php echo $patch->name().".zip"; ?>">Download</a> at Oracle MOS</li>
<?php if ($is_dl && $archive) { ?>
		<li><a href="/pdl/p/<?php echo $patch->name(); ?>">Download</a> locally</a>
<?php } ?>
		</ul>
		<h3>Patch Requirements</h3>
		<?php if (!count($patch->a_depend)) { echo "<p>There is no patch dependency for ".$patch->name()."</p>"; } else { ?>
		<ul>
		<?php foreach ($patch->a_depend as $p) { ?>
		 <li><a href="/patch/id/<?php echo $p->name(); ?>"><?php echo $p->name(); ?></a> : <?php echo $p->synopsis; ?></li>
		<?php } ?>
		</ul>
		<?php } ?>
		<h3>Patches obsoleted by this patch</h3>
                <?php if (!count($patch->a_obso)) { echo "<p>There is no patch obsoleted by ".$patch->name()."</p>"; } else { ?>
                <ul>
                <?php foreach ($patch->a_obso as $p) { ?>
                 <li><a href="/patch/id/<?php echo $p->name(); ?>"><?php echo $p->name(); ?></a> : <?php echo $p->synopsis; ?></li>
                <?php } ?>
                </ul>
                <?php } ?>
		<h3>Patches that conflitcs with this patch</h3>
                <?php if (!count($patch->a_conflicts)) { echo "<p>There is no patch that conflicts with ".$patch->name()."</p>"; } else { ?>
                <ul>
                <?php foreach ($patch->a_conflicts as $p) { ?>
                 <li><a href="/patch/id/<?php echo $p->name(); ?>"><?php echo $p->name(); ?></a> : <?php echo $p->synopsis; ?></li>
                <?php } ?>
                </ul>
                <?php } ?>
		<h3>Bug IDs fixed with this patch:</h3>
		<?php if (!count($patch->a_bugids)) { echo "<p>There is no detected bugid fixed with this patch</p>"; } else { ?>
		<ul>
		<?php foreach($patch->a_bugids as $bug) { ?>
		 <li><a href="/bugid/id/<?php echo $bug->id; ?>"><?php echo $bug->id."</a>"; if (!empty($bug->synopsis)) { echo ": ".$bug->synopsis; } ?></li>
		<?php } ?>
		</ul>
		<?php } ?>

	   </div>
