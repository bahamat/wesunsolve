   <div class="content">
    <h2>Please authenticate yourself!</h2>
<?php if (isset($error)) { ?>
    <span class="red"><p><?php echo $error; ?></p></span>
<?php } ?>
    <form method="POST" action="/login">
    <table class="ctable">
      <tr><td>Login</td><td><input type="text" value="" name="username"></td></tr>
      <tr><td>Password</td><td><input type="password" value="" name="password"></td></tr>
      <tr><td></td><td><input type="checkbox" value="1" name="keep"> Keep me loggedin</td></tr>
      <tr><td></td><td><input type="submit" value="Login" name="save"></td></tr>
    </table>
    </form>
   </div>
