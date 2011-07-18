<?php
      if ($start != 0 && $start >= $rpp) {
        $idprev = $start - $rpp;
      }
      if (($start + 50) >= $nb) {
        $idnext = $nb - 1;
      } else {
        $idnext = $start + $rpp;
      }
?>
	<div data-role="navbar">
		<ul>
			<li><a href="<?php echo $str; ?>/start/0">&lt;&lt;</a></li>
 		        <?php if (isset($idprev)) { ?>
		        <li><a href="<?php echo $str; ?>/start/<?php echo $idprev; ?>">[prev]</a></li>
			<?php } else { ?>
			<li><a href="#">[prev]</a></li>
			<?php } ?>
			<?php if (isset($idnext)) { ?>
		        <li><a href="<?php echo $str; ?>/start/<?php echo $idnext; ?>">[next]</a></li>
			<?php } else { ?>
			<li><a href="#">[next]</a></li>
			<?php } ?>
			<li><a href="<?php echo $str; ?>/start/<?php echo $nb-$rpp; ?>">&gt;&gt;</a></li>
		</ul>
	</div>
	<div data-role="content">	
				
		<ul data-role="listview" data-inset="true"> 
<?php foreach($patches as $p) { ?>
			<li> 
				<h3><?php echo $p->name(); ?> - <?php echo $p->synopsis; ?></h3> 
				<p>Released on: <?php echo date('d/m/Y', $p->releasedate); ?>
				<?php if ($p->pca_bad) { ?>| [BAD]<?php } ?>
				<?php if ($p->pca_sec) { ?>| [SEC]<?php } ?>
				<?php if ($p->pca_rec) { ?>| [REC]<?php } ?>
				<?php if (!strcmp($p->status, 'OBSOLETE')) { ?>| [OBS]<?php } ?>
				  | <a href="/patch/id/<?php echo $p->name(); ?>">Details</a></p> 
			</li> 	
<?php } ?>
		</ul>
		
	</div>
