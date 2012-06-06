<?php
  $h = HTTP::getInstance();
  if (!$h->css) $h->fetchCSS();
?>
    <div id="d_content">
     <h2 class="grid_10 push_1 alpha omega">Job list</h2>
     <div class="clear"></div>
     <div class="grid_<?php echo ($h->css->s_total - $h->css->s_menu); ?> alpha omega">
      <div class="d_content_box">
      <div style="height: 30px" class="push_<?php echo $h->css->p_snet; ?> grid_<?php echo $h->css->s_snet; ?>">
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
    <br/>
    <span class="red"><p><?php echo $error; ?></p></span>
<?php } ?>

    <h4>Last jobs</h4>
    <div class="ctable">
     <table class="ctable">
     <tr>
       <th>Owner</th>
       <th>Class</th>
       <th>Function</th>
       <th>Argument</th>
       <th>PID</th>
       <th>Duration</th>
       <th>State</th>
       <th>Added on</th>
       <th>Log</th>
     </tr>
<?php $i=1; foreach ($jobs as $j) { $j->fetchLogin(); ?>
     <tr class="<?php if ($i % 2) { echo "tdp"; } else { echo "tdup"; } ?>">
       <td><?php echo $j->o_owner; ?></td>
       <td><?php echo $j->class; ?></td>
       <td><?php echo $j->fct; ?></td>
       <td><?php echo $j->arg; ?></td>
       <td><?php echo $j->pid; ?></td>
       <td><?php if ($j->d_start && $j->d_stop) { echo $j->d_stop - $j->d_start; } else { echo 'N/A'; } ?> s</td>
       <td><?php echo $j->stateStr(); ?></td>
       <td><?php echo date(HTTP::getDateTimeFormat(), $l->added); ?></td>
       <td><a href="/ajlog/i/<?php echo $j->id_log; ?>">View</a></td>
     </tr>
<?php $i++; } ?>
   </table>
   </div>
   </div><!-- d_content_box -->
  </div><!-- grid_19 -->
 </div><!-- d_content -->
