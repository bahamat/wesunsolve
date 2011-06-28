   <div class="content">
    <h4>Change your password</h4>
    <?php if (isset($error)) { ?><h2 class="red"><?php if (isset($error) && !empty($error)) echo $error; ?></h2><?php } ?>
     <form method="POST" action="/password">
      <table class="ctable">
        <tr><th>Login:</th><td><?php echo $login; ?></td></tr>
        <tr><th>New password:</th><td><input class="field" type="password" name="password"/></td></tr>
        <tr><th>Confirmation:</th><td><input class="field" type="password" name="password2"/></td></tr>
        <tr><td></td><td><input type="submit" class="submit" name="save" value="Save changes"/></td></tr>
      </table>
     </form>
    <hr/>
     <address></address>
   </div>
