	   <div class="content">
	    <h2>Bundle <?php echo $bundle->synopsis; ?></h2>
	     <h3>General informations</h3>
		<ul>
		 <li>Synopsis: <?php echo $bundle->synopsis; ?></li>
		 <li>Release date: <?php if($bundle->lastmod) echo date('d/m/Y', $bundle->lastmod); ?></li>
		 <li>Archive size: <?php echo $bundle->size; ?> bytes (<?php echo round($bundle->size / 1024 / 1024, 2); ?> MBytes)</li>
                 <li><a href="/readme/bn/<?php echo $bundle->id; ?>">View README</a></li>
                 <li><a href="https://getupdates.oracle.com/patch_cluster/<?php echo $bundle->filename; ?>">Download</a> at Oracle MOS</li>
<?php if ($is_dl && $archive) { ?>
		<li><a href="/pdl/b/<?php echo $bundle->id; ?>">Download</a> locally</a>
<?php } ?>
		</ul>
                <h3>Patch enclosed inside the cluster</h3>
                <?php if (!count($bundle->a_patches)) { echo "<p>There is no patch included in ".$bundle->synopsis."</p>"; } else { ?>
                <ul>
                <?php foreach ($bundle->a_patches as $p) { ?>
                 <li><a href="/patch/id/<?php echo $p->name(); ?>"><?php echo $p->name(); ?></a> : <?php echo $p->synopsis; ?></li>
                <?php } ?>
                </ul>
                <?php } ?>

	   </div>
