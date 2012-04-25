<?php
  $h = HTTP::getInstance();
  if (!$h->css) $h->fetchCSS();
?>  
    <div id="d_content">
     <h2 class="grid_10 push_1 alpha omega">Patch level</h2>
     <div class="clear"></div>
     <div class="grid_<?php echo ($h->css->s_total - $h->css->s_menu); ?> alpha omega">
      <div class="d_content_box">
       <div style="height: 30px" class="push_<?php echo $h->css->p_snet; ?> grid_<?php echo $h->css->s_snet; ?> alpha omega">
        <div class="addthis_toolbox addthis_default_style" id="snet">
         <a class="addthis_button_facebook"></a>
         <a class="addthis_button_twitter"></a>
         <a class="addthis_button_email"></a>
         <a class="addthis_button_print"></a>
         <a class="addthis_button_google_plusone"></a>
        </div>
       </div>
       <div class="clear clearfix"></div>
  <h3>Patch level of <?php echo $s->name; ?></h3>
    <p>You have <?php echo count($plevels); ?> different patch level</p><br/>
    <table class="ctable">
     <tr>
      <th>Name</th>
      <th>Comment</th>
      <th></th>
      <th></th>
      <th></th>
     </tr>
<?php foreach($plevels as $plevel) { ?>
     <tr>
      <td><?php echo $plevel->name; ?></td>
      <td><?php echo $plevel->comment; ?></td>
      <td style="text-align: center;"><?php echo count($plevel->a_patches); ?> Patches</td>
      <td style="text-align: center;"><?php echo count($plevel->a_srv4pkgs); ?> Packages</td>
      <td style="text-align: center;"><a href="/plevel/s/<?php echo $s->id; ?>/p/<?php echo $plevel->id; ?>">View</a></td>
      <td style="text-align: center;"><a href="/del_plevel/s/<?php echo $s->id; ?>/p/<?php echo $plevel->id; ?>">Del</a></td>
      <td style="text-align: center;">
	<form action="/plevel_pca" method="GET">
	  <input type="hidden" name="s" value="<?php echo $s->id; ?>"/>
	  <input type="hidden" name="p" value="<?php echo $plevel->id; ?>"/>
	  <select name="patchdiag">
<?php if (isset($pdiags)) {
        $latest = Patchdiag::fetchLatest();
        foreach($pdiags as $pdiag) { ?>
            <option value="<?php echo $pdiag->id; ?>" <?php if ($pdiag->id == $latest->id) echo "selected"; ?>><?php echo $pdiag->format(); ?></option>
<?php   }
      } ?>

	  </select>
	  <input type="submit" name="form" value="Report"/>
	</form>
      </td>
     </tr>
<?php } ?>
    </table>
    <br/>
    <p><a href="/add_plevel/s/<?php echo $s->id; ?>">Add patch level</a></p>
   </div><!-- d_content_box -->
  </div><!-- grid_19 -->
 </div><!-- d_content -->
