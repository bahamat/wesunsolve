	<div data-role="content">	
				
		<ul data-role="listview" data-inset="true"> 
<?php foreach($bundles as $b) { ?>
			<li> 
				<h3><?php echo $b->synopsis; ?> (<?php echo $b->filename; ?>)</h3> 
				<p>Updated on: <?php echo date('d/m/Y', $b->lastmod); ?>
				| <?php echo round($b->size / 1024 / 1024, 2); ?> MBytes
				| <a href="/bundle/id/<?php echo $b->id; ?>">Details</a></p> 
			</li> 	
<?php } ?>
		</ul>
		
	</div>
