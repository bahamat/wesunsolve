<?php
  $h = HTTP::getInstance();
  if (!$h->css) $h->fetchCSS();
  $cntpl = 0; foreach($as as $s) { $s->fetchPLevels(0); $cntpl += count($s->a_plevel); }
?>  
    <div id="d_content">
     <h2 class="grid_10 push_1 alpha omega">Add automatic reporting</h2>
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
<?php if (isset($error)) { ?>
    <span class="red"><p><?php echo $error; ?></p></span>
<?php } ?>
    <p>You have <?php echo count($as); ?> servers and <?php echo $cntpl; ?> patch level associated.</p>
    <form method="POST" action="/add_report/form/1">
    <table class="ctable">
     <tr>
      <th>Patch Level</th>
      <th>Patchdiag delay</th>
      <th>Frequency</th>
     </tr>
     <tr>
      <td><select name="pl">
	   <option value="" selected>Please choose a patch level</option>
<?php
  foreach($as as $s) {
    foreach($s->a_plevel as $p) {
      echo '<option value="'.$p->id.'">'.$s->name.' - '.$p->name.'</option>\n';
    }
  }
  foreach ($l->a_mgroups as $g) {
    foreach($g->a_srv as $s) {
      foreach($s->a_plevel as $p) {
        echo '<option value="'.$p->id.'">'.$s->name.' - '.$p->name.'</option>\n';
      }
    }
  }

?>
       </select>
       </td>
      <td><select name="pd">
	   <option value="0" selected>latest</option>
	   <option value="1" >1 day ago</option>
	   <option value="7" >1 week ago</option>
	   <option value="14" >2 week ago</option>
	   <option value="31" >1 month ago</option>
	   <option value="62" >2 month ago</option>
	   <option value="93" >3 month ago</option>
	   <option value="186" >6 month ago</option>
	  </select></td>
       <td><select name="f">
           <option value="1d" >every day</option>
           <option value="1w" selected>every week</option>
           <option value="1m" >every month</option>
          </select></td>
      <td><input type="submit" name="Add" value="Add"/></td>
     </tr>
    </table>
    </form>
    <br/><br/>
   </div><!-- d_content_box -->
  </div><!-- grid_19 -->
 </div><!-- d_content -->
