	<div data-role="content">	
        
	<div data-role="collapsible">
	<h3>Bundle details</h3>
 	<ul>
	  <li><b>Bundle</b>: <?php echo $bundle->synopsis; ?></li>
	  <li><b>Filename</b>: <?php echo $bundle->filename; ?></li>
	  <li><b>Last modified date</b>: <?php if($bundle->lastmod) echo date('d/m/Y', $bundle->lastmod); ?></li>
	  <li><b>Size</b>: <?php echo $bundle->size; ?> bytes (<?php echo round($bundle->size / 1024 / 1024, 2); ?> MBytes)</li>
	</ul>
	</div>	

        <div data-role="collapsible" data-collapsed="true">
        <h3>Patches included</h3>
	<?php if (!count($bundle->a_patches)) { echo "<p>There is no patch detected inside ".$bundle->filename."</p>"; } else { ?>
	<ul>
	  <?php foreach ($bundle->a_patches as $p) { ?>
	  <li><a href="/patch/id/<?php echo $p->name(); ?>"><?php echo $p->name(); ?></a>: <?php echo $p->synopsis; ?></li>
	  <?php } ?>
	</ul>
	<?php } ?>
        </div>

	</div>
