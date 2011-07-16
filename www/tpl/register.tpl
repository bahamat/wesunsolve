   <div class="content">
    <h2>Registration form</h2>
<?php if (isset($error)) { ?>
    <span class="red"><p><?php echo $error; ?></p></span>
<?php } ?>
    <form method="POST" action="register.php">
    <table class="ctable">
      <tr><td>Full Name</td><td><input type="text" value="<?php if (isset($fullname)) echo $fullname; ?>" name="fullname"></td></tr>
      <tr><td>Email</td><td><input type="text" value="<?php if (isset($email)) echo $email; ?>" name="email"></td></tr>
      <tr><td>Login</td><td><input type="text" value="<?php if (isset($username)) echo $username; ?>" name="username"></td></tr>
      <tr><td>Password</td><td><input type="password" value="<?php if (isset($password)) echo $password; ?>" name="password"></td></tr>
      <tr><td>Confirmation</td><td><input type="password" value="<?php if (isset($password2)) echo $password2; ?>" name="password2"></td></tr>
      <tr><td></td><td><input type="submit" value="Register" name="save"></td></tr>
    </table>
    </form>
   </div>
