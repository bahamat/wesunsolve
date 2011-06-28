   <div class="content">
    <?php if (isset($what)) { ?>
    <span class="red">Error, the <?php echo $what; ?> that you have requested has not been found.</span>
    <p>If the error persist, please fill the form below to notify the error!</p>
    <?php } else { ?>
    <p>If you wish to contact the author, please use the form below...</p>
    <p>You could also discuss with us on IRC at #sunsolve @ irc.freenode.net</p>
    <?php } ?>
    <form method="POST" action="notify.php?form=1">
    <table class="ctable">
      <tr><th>Your Name</th><td style="text-align: left;"><input type="text" value="" name="nom"></td></tr>
      <tr><th>Your E-mail</th><td style="text-align: left;"><input type="text" value="" name="email"></td></tr>
      <tr><th>Free Text</th><td style="text-align: left;"><textarea name="details"></textarea></td></tr>
      <tr><td></td><td style="text-align: left;"><input type="submit" value="Submit" name="submit"></td></tr>
    </table>
    </form>
    <hr/>
   </div>
