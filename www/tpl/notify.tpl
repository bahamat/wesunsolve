   <div class="content">
    <?php if (isset($what)) { ?>
    <span class="red">Error, the <?php echo $what; ?> that you have requested has not been found.</span>
    <p>If the error persist, please fill the form below to notify the error!</p>
    <?php } else { ?>
    <p>You can contact us by:</p>
    <ul>
     <li><a href="irc://#sunsolve@irc.freenode.net">IRC</a> on <b>#sunsolve</b> @ <b>irc.freenode.org</b></li>
     <li>Twitter: <a href="http://twitter.com/wesunsolve">@WeSunSolve</a></li>
     <li><a href="mailto:info@wesunsolve.net">E-Mail</a></li>
     <li>With the form below...</li>
    </ul>
    <?php } ?>
    <form method="POST" action="notify.php?form=1">
    <table class="ctable">
      <tr><th>Your Name</th><td style="text-align: left;"><input type="text" value="" name="nom"></td></tr>
      <tr><th>Your E-mail</th><td style="text-align: left;"><input type="text" value="" name="email"></td></tr>
      <tr><th>Free Text</th><td style="text-align: left;"><textarea name="details"></textarea></td></tr>
      <tr><td></td><td style="text-align: left;"><input type="submit" value="Submit" name="submit"></td></tr>
    </table>
    </form>
   </div>
