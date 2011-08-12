<?php
  $h = HTTP::getInstance();
  if (!$h->css) $h->fetchCSS();
?>  
    <div id="d_content">
     <h2 class="grid_10 push_1 alpha omega">Add comment</h2>
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
     <p>Add comment for <?php echo $type.' '.$id_on; ?></p>
<?php if (isset($error)) { ?>
    <span class="red"><p><?php echo $error; ?></p></span>
<?php } ?>
    <form method="POST" action="/add_comment/form/1">
     <input type="hidden" name="id_on" value="<?php echo $id_on; ?>"/>
     <input type="hidden" name="type" value="<?php echo $type; ?>"/>
    <table class="ctable">
      <tr><td>*Comment</td><td><textarea name="comment"><?php if (isset($comment)) echo $comment; ?></textarea></td></tr>
      <tr><td>Private comment?</td><td><input type="checkbox" <?php if (isset($is_private)) echo "checked"; ?>" name="is_private"></td></tr>
      <tr><td></td><td><input type="submit" value="Add" name="save"></td></tr>
    </table>
    </form>
   </div><!-- d_content_box -->
  </div><!-- grid_19 -->
 </div><!-- d_content -->
