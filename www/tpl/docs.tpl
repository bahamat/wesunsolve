<?php
  $h = HTTP::getInstance();
  if (!$h->css) $h->fetchCSS();
?>
    <div id="d_content"> 
     <h2 class="grid_10 push_1 alpha omega">We Sun Solve Documentation</h2> 
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
<h3><a name="index"></a>Index</h3>
<ul class="listtoc">
 <li><a href="#register">Registration process</a></li>
 <li><a href="#features">Features list</a></li>
 <li><a href="#usettings">User settings</a></li>
 <li><a href="#psearch">Patches Searching</a></li>
 <li><a href="#bsearch">Bugs Searching</a></li>
 <li><a href="#clists">Custom User lists</a></li>
 <li><a href="#plevel">Server patch level</a></li>
 <li><a href="#roadmap">Roadmap</a></li>
</ul>
<!-- [ Separator ] -->
<h3><a name="register"></a>Registration process</h3>
<p>You can register on <i>We Sun Solve!</i> by filling the <a href="http://wesunsolve.net/register">registration form</a>. <br/>
   The only information we need is an email address, a login and a password. Please note that you will receive a confirmation
   code to verify your email address. After you've followed the link present in this e-mail, you will be able to <a href="/login">login</a></p>
<p>There are several feature that are only available to registered users, like:</p>
<ul class="listtick">
  <li>Adapt UI settings of the website;</li>
  <li>Encode your servers and keep track of their patching level;</li>
  <li>Setup custom patches list and keep track of their updates;</li>
  <li>History of your browsing (patches/bug/searchs);</li>
  <li>Make your list of patches public and let people rate them;</li>
  <li>Post public or private comments on bugs/patches;</li>
  <li>Adapt the resolution of the whole website;</li>
  <li><b>*SOON*</b> Saved search with notifications;</li>
  <li><b>*SOON*</b> Vote for other users' custom lists;</li>
  <li><b>*SOON*</b> Download all the readme's of a patch list at once</li>
</ul>
<br/>
<a href="#index">back to top</a>
<!-- [ Separator ] -->
<h3><a name="features"></a>Features list</h3>
<p>We will try to keep track of every feature of this website hereunder:</p>
<ul class="listtick">
 <li>View the <a href="/lpatches">Latest patches</a> released;</li>
 <li>View the <a href="/lpatches/sec/1">Latest security patches</a> released;</li>
 <li>View the <a href="/lpatches/bad/1">Latest obsoleted/bad patches</a> released;</li>
 <li>Search for <a href="/patch_search">patches</a>;</li>
 <li>Search for <a href="/bsearch">bugs</a>;</li>
 <li>Use a "Last modified" date limit while searching for <a href="/bsearch">bugs</a>;</li>
 <li>Quickly access to a patch by using the site's header form (<i>111111-02</i>, <i>137137-%</i>);</li>
 <li>Quickly access to a bug by using the site's header form (<i>6932350</i>);</li>
 <li>View public user-comments for patches/bugs/bundles;</li>
 <li>*Post <b>public</b> comments on patches/bugs/bundles;</li>
 <li>*Post <b>private</b> comments on patches/bugs/bundle (i.e. to remember on which server you've tried a certain patch..);</li>
 <li>View the most seen patches on <a href="/">main page</a>;</li>
 <li>View the most seen bugs on <a href="/">main page</a>;</li>
 <li>View the latest public user-comments on <a href="/">main page</a>;</li>
 <li>Check the website <a href="/dbstats">database statistics</a>;</li>
 <li>Check the website <a href="/arch">architecture details</a>;</li>
 <li>Check the website <a href="/changelog">change log</a>;</li>
 <li>RSS feed for <a href="/rss/bundles">Last modified patch clusters</a>;</li>
 <li>RSS feed for <a href="/rss/patches">Latest patch released</a>;</li>
 <li>RSS feed for <a href="/rss/news">We Sun Solve news</a>;</li>
 <li>*Add some <a href="/add_clist">custom patches list</a> either private or public;</li>
 <li>*Add some <a href="/register_srv">server</a> to your user panel;</li>
 <li>*Change your <a href="/password">password</a>;</li>
 <li>*Change your <a href="/settings">settings</a> (bugs shown per page, patches shown per page, disable history log, change resolution of the website);</li>
 <li>View your latest patches and bugs view on your <a href="/panel">user panel</a>;</li>
 <li>Manage different patching level for your servers;</li>
 <li>Compare two patching level to see differences;</li>
 <li>Make a <a href="/plist_report">report</a> for a list of given patches;</li>
 <li>See differences between README's updates of a patch (i.e: <a href="/diffr/type/patch/id/146091-04">146091-04</a>)</li>
</ul>
<br/>
<a href="#index">back to top</a>
<!-- [ Separator ] -->
<h3><a name="usettings"></a>User settings</h3>
<p></p>
<a href="#index">back to top</a>
<!-- [ Separator ] -->
<h3><a name="psearch"></a>Patches Searching</h3>
<p></p>
<a href="#index">back to top</a>
<!-- [ Separator ] -->
<h3><a name="bsearch"></a>Bugs Searching</h3>
<p></p>
<a href="#index">back to top</a>
<!-- [ Separator ] -->
<h3><a name="clists"></a>Custom User lists</h3>
<p></p>
<a href="#index">back to top</a>
<!-- [ Separator ] -->
<h3><a name="plevel"></a>Server patch level</h3>
<p></p>
<a href="#index">back to top</a>
<!-- [ Separator ] -->
<h3><a name="roadmap"></a>Roadmap</h3>
<p></p>
<a href="#index">back to top</a>
<!-- [ Separator ] -->

 
       </div><!-- d_content_box --> 
      </div><!-- grid_19 --> 
     </div><!-- d_content --> 
