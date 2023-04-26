<?php

get_header();//call the header
/*loops until have posts*/
while(have_posts()){
    the_post(); 
}
get_footer();//call the footer
?>