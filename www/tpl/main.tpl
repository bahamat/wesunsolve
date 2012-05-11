<?php
  $h = HTTP::getInstance();
  if (!$h->css) $h->fetchCSS();
?>
    <div id="d_content"> 
     <h2 class="grid_10 push_1 alpha omega">Welcome to WeSunSolve !</h2> 
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
<p class="warning">We are currently moving some part of our infrastructure, please check <a href="http://wiki.wesunsolve.net/Moving">wiki</a> for details!</p>
       <p>This website is free of charge, without ads and not related nor associated with Oracle Corporation.</p>
       <p>Consider to <a href="/register">create an account</a> to <a href="http://wiki.wesunsolve.net/RegistrationProcess">take advantage of the website</a> for your daily sysadmin work!</p>
       <h3>Recent activity</h3>
       <div class="prefix_1 grid_<?php echo $h->css->s_box; ?> alpha"> 
        <div class="listbox firstbox"> 
         <h3>Most viewed patches</h3> 
         <ul> 
         <?php foreach($mvp as $p) { ?>
           <li><a href="/patch/id/<?php echo $p->name(); ?>"><?php echo $p->name(); ?></a> (<?php echo $p->views; ?> views)</li>
         <?php } ?>
	 </ul> 
        </div> 
       </div> 
       <div class="grid_<?php echo $h->css->s_box; ?>"> 
        <div class="listbox"> 
         <h3>Most viewed bugs</h3> 
         <ul> 
         <?php foreach($mvb as $b) { ?>
           <li><a href="/bugid/id/<?php echo $b->id; ?>"><?php echo $b->id; ?></a> (<?php echo $b->views; ?> views)</li>
         <?php } ?>
	 </ul> 
        </div> 
       </div> 
       <div class="grid_<?php echo $h->css->s_box + 2; ?> omega">
        <div class="listbox">
         <h3>Last 10 updated READMEs <a class="darklink" href="/lastreadme">[more]</a></h3>
         <ul>
<?php if (count($lap)) {
        foreach($lap as $p) { ?>
	  <li><a href="/diffr/type/patch/id/<?php echo $p->name(); ?>"><?php echo $p->name(); ?></a> has changed on <?php echo date(HTTP::getDateFormat(), $p->lmod); ?></li>
<?php   }
      } else { ?>
	  <li>No data...</li>
<?php } ?>
	 </ul>
	</div>
       </div>
       <div class="clear"></div>
       <div class="prefix_1 grid_<?php echo $h->css->s_box + 5; ?> alpha"> 
        <div class="listbox firstbox secondline"> 
         <h3>Last 10 comments</h3> 
     <?php if (count($com)) { ?>
         <ul> 
         <?php foreach($com as $c) { ?>
	   <li><b><?php $c->fetchLogin(); echo $c->o_login->username; ?></b> on <?php echo $c->type.' '.$c->link(); ?>: <?php echo $c->since(); ?> ago</li> 
         <?php } ?>
	 </ul> 
    <?php } ?>
        </div> 
       </div> 
       <div class="clear"></div> 
 
       <h3>Site News</h3> 
       <ul class="listtick">
<?php foreach($news as $n) { ?>
      <li><i><?php echo date(HTTP::getDateFormat(), $n->date); ?></i>: <?php echo $n->synopsis; ?> - <?php if (!empty($n->link)) echo "<a href=\"".$n->link."\">Link</a>"; ?></li>
<?php } ?>
       </ul>
 
       </div><!-- d_content_box --> 
      </div><!-- grid_19 --> 
     </div><!-- d_content --> 
