<?php
  $h = HTTP::getInstance();
  if (!$h->css) $h->fetchCSS();
?>  
    <div id="d_content">
     <h2 class="grid_10 push_1 alpha omega">Disclaimer</h2>
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
    <h3>Why such a website ?</h3>
    <p>When the merge of Sun and Oracle has been made, we lost a lot of tools that were present on the famous SunSolve. Like the ability
       to search efficiently for patches, bugids, sun alerts and so on. This website has the aim to provide part of this information by
       indexing all the patches and bugids that we could gather publicly. We want to allow easy search to ease the life of solaris sysadmins.
    </p>

    <h3>Brands and authors</h3>
      <p>The content aggregated on this website is the property of its respective owner.</p>

    <h3>Informations</h3>
      <p>The accuracy of the information shown on <i>We Sun Solve!</i> is provided as-is and we could not be held responsible for a lack of accuracy of this content. Also, the usage of information shown on this website is under your strict responsability. You are using theses information at your own risk!</p>

    <h3>User content</h3>
      <p>Every content managed by users on <i>We Sun Solve!</i> is owned by its respective author. We could not be held responsible for content submitted by users. This includes <i>Custom patch lists</i>, <i>Comments</i> and every other form of content managed by users.</p>

    <h3>Tracking and history</h3>
      <p>For the convenience of our users, bug and patch viewing are logged into the site's database. This logging is only done to allow user to see it's browsing history on <i>We Sun Solve!</i>. The user has also the possibility to disable such tracking inside the <i>Change settings</i> page on their panel.</p>

   </div><!-- d_content_box -->
  </div><!-- grid_19 -->
 </div><!-- d_content -->
