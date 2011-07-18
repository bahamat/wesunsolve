	<div data-role="content">	
				
		<ul data-role="listview" data-inset="true"> 
<?php foreach($news as $n) { ?>
			<li> 
				<h3><?php echo $n->synopsis; ?></h3> 
  <?php if (isset($n->link) && !empty($n->link)) { ?>
				<p><a href="<?php echo $n->link; ?>">Related link</a></p> 
  <?php } ?>
			</li> 	
<?php } ?>
		</ul>
		
	</div>
