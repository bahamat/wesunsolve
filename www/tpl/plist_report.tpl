<div class="content">
  <h2>Patch List report</h2>
  <p>Warning: The patch synopsis is limited to 100 char on this array, please check patch detail to see full messages</p>
  <div class="ctable">
  <?php if (isset($psetid)) { ?>
  <p>Check the list of files affected by theses patches by clicking <a href="/plevel_files/p/<?php echo $psetid; ?>/s/<?php echo $s->id; ?>">here</a>
  <?php } ?>
  <table class="ctable"><tr><td class="greentd">RECOMMENDED</td><td class="orangetd">SECURITY</td><td class="redtd">WITHDRAWN</td></tr></table>
  <table class="ctable">
    <tr>
      <th>Patch</th>
      <th>Released</th>
      <th>Latest?</th>
      <th>Status</th>
      <th>Synopsis</th>
<?php if ($curr) { ?>
      <th>Currently installed</th>
<?php } ?>
<?php $lm = loginCM::getInstance();
     if ($lm->o_login && $lm->o_login->is_admin) { ?>
      <th>Tobedl</th>
<?php } ?>
      <th>Readme</th>
    </tr>
<?php foreach($plist as $p) { ?>
    <tr>
      <td <?php echo $p->color(); ?>><a href="/patch/id/<?php echo $p->name(); ?>"><?php echo $p->name(); ?></a></td>
      <td><?php if ($p->releasedate) echo date('d/m/Y', $p->releasedate); ?></td>
      <td><?php if ($p->o_latest === false) { echo "yes"; } else if ($p->o_latest) { echo "<a href=\"/patch/id/".$p->o_latest->name()."\">".$p->o_latest->name()."</a>"; } else { echo "not found"; } ?></td>
      <td><?php echo $p->status; ?></td>
      <td style="text-align: left;"><?php echo substr($p->synopsis,0,100); ?></td>
<?php if ($curr && $p->o_current) { ?>
      <td><a href="/patch/id/<?php echo $p->o_current->name(); ?>"><?php echo $p->o_current->name(); ?></a></td>
<?php } else if ($curr) { ?>
      <td>None</td>
<?php } ?>
<?php if($lm->o_login && $lm->o_login->is_admin) { ?>
      <td><a target="_blank" href="/.toadd/p/<?php echo $p->patch; ?>/i/<?php echo $p->revision; ?>">todo</a></td>
<?php } ?>
      <td><a href="/readme/id/<?php echo $p->name(); ?>">Readme</a></td>
    </tr>
<?php } ?>
  </table>
  </div>
</div>

