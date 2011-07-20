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
<div class="content">
  <h2><?php echo $title; ?> (<?php echo $rpp; ?> patches of <?php echo $nb; ?> starting from <?php echo $start; ?>)</h2>
  <div class="ctable">
   <table class="ctable"><tr>
				<td class="greentd">RECOMMENDED</td>
				<td class="orangetd">SECURITY</td>
				<td class="redtd">WITHDRAWN</td>
				<td class="browntd">OBSOLETE</td>
			</tr></table>
 <?php if (isset($l)) { ?>
  <div id="add_uclist_form">
   <select id="selectAddUCList" name="i">
    <option value="-1" selected>Add to Custom List</option>
    <?php foreach($l->a_uclists as $l) { ?>
    <option value="<?php echo $l->id; ?>"><?php echo $l->name; ?></option>
    <?php } ?>
   </select>
   <input type="button" name="Add" value="Add" onclick="addManyUCList(showMessage)"/>
   <div id="msg_uclist"></div>
  </div>
 <?php } ?>
  <table id="tbl_patches" class="ctable">
 <tr><td colspan="3"><a href="<?php echo $str; ?>/start/0">&lt;&lt;</a>&nbsp;&nbsp;&nbsp;&nbsp;
<?php if (isset($idprev)) { ?>
      <a href="<?php echo $str; ?>/start/<?php echo $idprev; ?>">[prev]</a>
<?php } else { ?>
      [prev]
<?php } ?>&nbsp;&nbsp;&nbsp;&nbsp;
<?php if (isset($idnext)) { ?>
      <a href="<?php echo $str; ?>/start/<?php echo $idnext; ?>">[next]</a>
<?php } else { ?>
      [next]
<?php } ?>&nbsp;&nbsp;&nbsp;&nbsp;
  <a href="<?php echo $str; ?>/start/<?php echo $nb-$rpp; ?>">&gt;&gt;</a></td></tr>
   <tr>
    <?php if (isset($l)) { ?><th></th><?php } ?>
    <th>Patch ID</th>
    <th>Release date</th>
    <th>Synopsis</th>
   </tr>
<?php $i=0; foreach($patches as $p) { ?>
   <tr>
    <?php if (isset($l)) { ?><td><input type="checkbox" name="p[<?php echo $i; ?>]" value="<?php echo $p->name(); ?>"/></td><?php } ?>
    <td <?php echo $p->color(); ?>><a href="/patch/id/<?php echo $p->name(); ?>"><?php echo $p->name(); ?></a></td>
    <td><?php echo date('d/m/Y', $p->releasedate); ?></td>
    <td style="text-align: left"><?php echo substr($p->synopsis,0,100); ?></td>
   </tr>
<?php $i++; } ?>
 <tr><td colspan="3"><a href="<?php echo $str; ?>/start/0">&lt;&lt;</a>&nbsp;&nbsp;&nbsp;&nbsp;
<?php if (isset($idprev)) { ?>
      <a href="<?php echo $str; ?>/start/<?php echo $idprev; ?>">[prev]</a>
<?php } else { ?>
      [prev]
<?php } ?>&nbsp;&nbsp;&nbsp;&nbsp;
<?php if (isset($idnext)) { ?>
      <a href="<?php echo $str; ?>/start/<?php echo $idnext; ?>">[next]</a>
<?php } else { ?>
      [next]
<?php } ?>&nbsp;&nbsp;&nbsp;&nbsp;
  <a href="<?php echo $str; ?>/start/<?php echo $nb-$rpp; ?>">&gt;&gt;</a></td></tr>
   </table>
  </div>
 </div>


