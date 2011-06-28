   <div class="content">
    <h2>Add server</h2>
<?php if (isset($error)) { ?>
    <span class="red"><p><?php echo $error; ?></p></span>
<?php } ?>
    <form method="POST" action="/register_srv.php/form/1">
    <table class="ctable">
      <tr><td>*Server Name</td><td><input type="text" value="<?php if (isset($sname)) echo $sname; ?>" name="sname"></td></tr>
      <tr><td>Comment</td><td><input type="text" value="<?php if (isset($comment)) echo $comment; ?>" name="comment"></td></tr>
      <tr><td></td><td><input type="submit" value="Register" name="save"></td></tr>
    </table>
    </form>
    <hr/>
   </div>
