	<div data-role="navbar">
                <ul>   
                        <li><a href="/readme/id/<?php echo $patch->name(); ?>">Readme</a></li>
                        <li><a href="/files/id/<?php echo $patch->name(); ?>">Files</a></li>
                </ul>
        </div>

	<div data-role="content">	
        
	<div data-role="collapsible">
	<h3>Patch details</h3>
 	<ul>
	  <li><b>Patch</b>: <?php echo $patch->name(); ?></li>
	  <li><b>Release date</b>: <?php if($patch->releasedate) echo date('d/m/Y', $patch->releasedate); ?></li>
	  <li><b>Detected status</b>: <?php echo $patch->status; ?></li>
	  <li><b>Synopsis</b>: <?php echo $patch->synopsis; ?></li>
	  <li><b>Size</b>: <?php echo $patch->filesize; ?> bytes (<?php echo round($patch->filesize / 1024 / 1024, 2); ?> MBytes)</li>
<?php if ($patch->o_latest) { ?>
	  <li><b>Latest release</b>: <a href="/patch/id/<?php echo $patch->o_latest->name(); ?>"><?php echo $patch->o_latest->name(); ?></a></li>
<?php } ?>
	</ul>
	</div>	

        <div data-role="collapsible" data-collapsed="true">
        <h3>Patch requirements</h3>
	<?php if (!count($patch->a_depend)) { echo "<p>There is no patch dependency for ".$patch->name()."</p>"; } else { ?>
	<ul>
	  <?php foreach ($patch->a_depend as $p) { ?>
	  <li><a href="/patch/id/<?php echo $p->name(); ?>"><?php echo $p->name(); ?></a>: <?php echo $p->synopsis; ?></li>
	  <?php } ?>
	</ul>
	<?php } ?>
        </div>

        <div data-role="collapsible" data-collapsed="true">
        <h3>Patches obsolated by this patch</h3>
        <?php if (!count($patch->a_obso)) { echo "<p>There is no patch obsolated by ".$patch->name()."</p>"; } else { ?>
        <ul>
          <?php foreach ($patch->a_obso as $p) { ?>
          <li><a href="/patch/id/<?php echo $p->name(); ?>"><?php echo $p->name(); ?></a>: <?php echo $p->synopsis; ?></li>
          <?php } ?>
        </ul>
        <?php } ?>
        </div>

        <div data-role="collapsible" data-collapsed="true">
        <h3>Patches that conflitcs with this patch</h3>
        <?php if (!count($patch->a_conflicts)) { echo "<p>There is no patch in conflict with".$patch->name()."</p>"; } else { ?>
        <ul>
          <?php foreach ($patch->a_conflicts as $p) { ?>
          <li><a href="/patch/id/<?php echo $p->name(); ?>"><?php echo $p->name(); ?></a>: <?php echo $p->synopsis; ?></li>
          <?php } ?>
        </ul>
        <?php } ?>
        </div>

        <div data-role="collapsible" data-collapsed="true">
        <h3>Bug IDs fixed with this patch</h3>
	<?php if (!count($patch->a_bugids)) { echo "<p>There is no detected bugid fixed with this patch</p>"; } else { ?>
        <ul>
        <?php foreach($patch->a_bugids as $bug) { ?>
          <li><a href="/bugid/id/<?php echo $bug->id; ?>"><?php echo $bug->id."</a>"; if (!empty($bug->synopsis)) { echo ": ".htmlentities($bug->synopsis); } ?></li>
        <?php } ?>
 	</ul>
	<?php } ?>
        </div>

        <div data-role="collapsible" data-collapsed="true">
        <h3>Bug IDs fixed with included patches</h3>
        <?php foreach($patch->a_previous as $p) { ?>
         <h4>from <?php echo $p->name(); ?></h4>
         <ul>
         <?php   foreach($p->a_bugids as $bug) { ?>
          <li><a href="/bugid/id/<?php echo $bug->id; ?>"><?php echo $bug->id."</a>"; if (!empty($bug->synopsis)) { echo ": ".htmlentities($bug->synopsis); } ?></li>
         <?php   } ?>
         </ul>
        <?php } ?>

        </div>


	</div>
