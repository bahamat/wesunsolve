<?php
  $h = HTTP::getInstance();
  if (!$h->css) $h->fetchCSS();
?>  
    <div id="d_content">
     <h2 class="grid_10 push_1 alpha omega">Server Upgrade</h2>
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
  <p>Here you can generate a <i>patchdiag.xref</i> file to upgrade a server patch level (source) to match another one (destination).</p>
    <p>You have <?php echo count($as); ?> server registered</p>
    <form method="GET" action="/srvPdiag">
    <table class="ctable">
     <tr>
      <th>Patch Level source</th>
      <th>Patch Level destination</th>
      <th></th>
     </tr>
     <tr>
      <td><select name="is">
<?php
  $choices = "";
  $choices .= '<option value="-1">Choose a patch level</option>\n';
  foreach($as as $s) {
    $s->fetchPLevels();
    foreach($s->a_plevel as $p) {
      $choices .= '<option value="'.$p->id.'">'.$s->name.' - '.$p->name.'</option>\n';
    }
  }
  echo $choices;
?>
          </select></td>
      <td><select name="id">
<?php echo $choices; ?>
	  </select></td>
      <td><input type="submit" name="Generate" value="Generate"/></td>
     </tr>
    </table>
    </form>
    <br/><br/>
   </div><!-- d_content_box -->
  </div><!-- grid_19 -->
 </div><!-- d_content -->
