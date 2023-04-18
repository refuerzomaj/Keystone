<?php

get_header();//call the header
/*loops until have posts*/
while(have_posts()){
    the_post(); ?>
    <!--<h2><a href="<?php //the_permalink(); ?>"><?php //the_title(); ?></a></h2>
    <p><?php //the_content(); ?></p>
    <hr>-->
    <?php
}
get_footer();//call the footer
?>