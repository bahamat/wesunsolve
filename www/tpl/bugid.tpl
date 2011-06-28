	   <div class="content">
	    <h2>Bug <?php echo $bug->id; ?></h2>
	     <h3>General informations</h3>
		<ul>
		 <li>Synopsis: <?php echo $bug->synopsis; ?></li>
		</ul>
             <h3>Patches that fixes the bug</h3>
	     <?php if (!count($bug->a_patches)) { echo "<p>There is no patch for this bug</p>"; } else { ?>
	      <ul>
		<?php foreach($bug->a_patches as $p) { ?>
		<li><a href="/patch/id/<?php echo $p->name(); ?>"><?php echo $p->name(); ?></a> : <?php echo $p->synopsis; ?></li>
                <?php } ?>
	      </ul>
             <?php } ?>
	     <h3>Bug ID Details:</h3>
	     <div id="bug_details">
<h4><?php echo $bug->ft("synopsis"); ?></h4>
<p>
<?php if (!empty($bug->type)) { ?><b>Type</b>: <?php echo $bug->type; ?><br/><?php } ?>
<?php if (!empty($bug->category)) { ?><b>Category</b>: <?php echo $bug->category; ?><br/><?php } ?>
<?php if (!empty($bug->subcat)) { ?><b>Sub Category</b>: <?php echo $bug->subcat; ?><br/><?php } ?>
<?php if (!empty($bug->product)) { ?><b>Product</b>: <?php echo $bug->product; ?><br/><?php } ?>
<?php if (!empty($bug->state)) { ?><b>State</b>: <?php echo $bug->state; ?><br/><?php } ?>
<?php if (!empty($bug->substate)) { ?><b>Sub State</b>: <?php echo $bug->substate; ?><br/><?php } ?>
<?php if (!empty($bug->submitter)) { ?><b>Submitter</b>: <?php echo $bug->submitter; ?><br/><?php } ?>
<?php if (!empty($bug->sponsor)) { ?><b>Sponsor</b>: <?php echo $bug->sponsor; ?><br/><?php } ?>
<?php if ($bug->d_submit) { ?><b>Date Submitted</b>: <?php echo date('d/m/Y', $bug->d_submit); ?><br/><?php } ?>
<?php if ($bug->d_created) { ?><b>Date Created</b>: <?php echo date('d/m/Y', $bug->d_created); ?><br/><?php } ?>
<?php if ($bug->d_updated) { ?><b>Date Updated</b>: <?php echo date('d/m/Y', $bug->d_updated); ?><br/><?php } ?>
<?php if (!empty($bug->commit_tf)) { ?><b>Commit to fix</b>: <?php echo $bug->commit_tf; ?><br/><?php } ?>
<?php if (!empty($bug->duplicate_of)) { ?><b>Duplicate of</b>: <?php echo $bug->duplicate_of; ?><br/><?php } ?>
<?php if (!empty($bug->first_reported_bug_id)) { ?><b>First reported bug ID</b>: <?php echo $bug->first_reported_bug_id; ?><br/><?php } ?>
<?php if (!empty($bug->fixed_in)) { ?><b>Fixed in</b>: <?php echo $bug->fixed_in; ?><br/><?php } ?>
<?php if (!empty($bug->introduced_in)) { ?><b>Introduced in</b>: <?php echo $bug->introduced_in; ?><br/><?php } ?>
<?php if (!empty($bug->related_bugs)) { ?><b>Related bugs</b>: <?php echo $bug->related_bugs; ?><br/><?php } ?>
<?php if (!empty($bug->reported_against)) { ?><b>Reported against</b>: <?php echo $bug->reported_against; ?><br/><?php } ?>
<b>Keywords</b>: <?php echo $bug->ft("keywords"); ?><br/>
<b>Responsible engineer</b>: <?php echo $bug->ft("responsible_engineer"); ?><br/>
</p>
<?php
  $raw = $bug->ft("raw");
  $desc = $bug->ft("description");
  $co = $bug->ft("comments");
  $wa = $bug->ft("workaround");
  if (empty($raw) || !empty($desc)) {
?>
<h4>Description</h4>
<pre>
<?php echo $bug->ft("description"); ?>
</pre>
<h4>Workaround</h4>
<pre>
<?php echo $bug->ft("workaround"); ?>
</pre>
<h4>Comments</h4>
<pre>
<?php echo $bug->ft("comments"); ?>
</pre>
<?php } else { 
  echo $bug->ft("raw");
} ?>
	     </div>
	   </div>

