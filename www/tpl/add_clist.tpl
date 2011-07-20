   <div class="content">
    <h2>Add custom list of patches</h2>
<?php if (isset($error)) { ?>
    <span class="red"><p><?php echo $error; ?></p></span>
<?php } ?>
    <form method="POST" action="/add_clist.php/form/1">
    <table class="ctable">
      <tr><td>*List Name</td><td><input type="text" value="<?php if (isset($name)) echo $name; ?>" name="name"></td></tr>
      <tr><td></td><td><input type="submit" value="Add" name="save"></td></tr>
    </table>
    </form>
   </div>
