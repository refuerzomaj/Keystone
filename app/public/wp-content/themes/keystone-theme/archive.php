<?php 
get_header();//call the header
/*loops until have posts*/
while(have_posts()){
    the_post(); 
//Display Blog Page
if(is_page('Blog Page')){

    $arrayBlogImageUrl = array();
    $arrayBlogTitle = array();
    $arrayBlogDate = array();
    $arrayBlogDescription = array();
    $arrayBlogPermalink = array();
    $numOfBlogPost = 0;
    $args2 = array(
        'post_type' => 'post',
        'post-status' => 'publish',
        'posts_per_page' => -1
    );
                    
    $post_query = new WP_Query( $args2 );
    if ( $post_query->have_posts() ) {
        while ( $post_query->have_posts() ) {
            $post_query->the_post();

            if( has_post_thumbnail() ){
                $arrayBlogImageUrl[] = get_the_post_thumbnail_url(get_the_ID(),'medium');
                $arrayBlogTitle[] = get_the_title();
                $arrayBlogDate[] = get_the_date();
                $arrayBlogDescription[] = get_the_content();
                $arrayBlogPermalink[] = get_permalink();
            }
            $numOfBlogPost++;
        }
        wp_reset_postdata();
    }
?>
    <div class="blog-page">
        <!-- First Section Section -->
        <div class="first-section">
            <div class="box">
                <p><a href="http://keystone.local/homepage" style="padding:0;margin:0; text-decoration:none; color:white;">Home</a> > Blogs</p>
            </div>
        </div>
        <!-- Second Section -->
        <div class="second-section">
            <div class="box">
                <div class="title-box">
                    <h2>Latest Blog</h2>
                </div>
                <div class="first-blog-box">
                    <a href="<?php echo $arrayBlogPermalink[0];?>">
                    <div class="left-box">
                        <div class="img-post" style="background-image:url('<?php echo $arrayBlogImageUrl[0]; ?>');" alt="<?php echo $arrayBlogTitle[0];?>">
                            <p class="blog-date">
                                <?php echo $arrayBlogDate[0]; ?>
                            </p>
                        </div>
                    </div>
                    <div class="right-box">
                        <h3>
                            <?php echo $arrayBlogTitle[0]; ?>
                        </h3>
                        <p>
                            <?php echo $arrayBlogDescription[0]; ?>
                        </p>
                    </div>
                    </a>
                </div>
                <div class="second-blog-box">
                    <?php 
                        for($x = 1; $x < $numOfBlogPost; $x++){
                            if($x % 2 === 0){
                    ?>
                    <a href="<?php echo $arrayBlogPermalink[$x];?>">
                    <div class="blog-box" style="margin:0 62px;">
                        <div class="img-post" style="background-image:url('<?php echo $arrayBlogImageUrl[$x]; ?>');" alt="<?php echo $arrayBlogTitle[$x];?>">
                            <p class="blog-date">
                                <?php echo $arrayBlogDate[$x]; ?>
                            </p> 
                        </div>
                        <h3>
                            <?php echo $arrayBlogTitle[$x]; ?>
                        </h3>
                        <p>
                            <?php echo $arrayBlogDescription[$x]; ?>
                        </p>
                    </div>
                    </a>
                    <?php 
                            }
                            else{
                                ?>
                    <a href="<?php echo $arrayBlogPermalink[$x];?>">
                    <div class="blog-box">
                        <div class="img-post" style="background-image:url('<?php echo $arrayBlogImageUrl[$x]; ?>');" alt="<?php echo $arrayBlogTitle[$x];?>">
                            <p class="blog-date">
                                <?php echo $arrayBlogDate[$x]; ?>
                            </p> 
                        </div>
                        <h3>
                            <?php echo $arrayBlogTitle[$x]; ?>
                        </h3>
                        <p>
                            <?php echo $arrayBlogDescription[$x]; ?>
                        </p>
                    </div>
                    </a>
                    <?php 

                            }
                        }
                    ?>
                </div>
            </div>
        </div>
        <!-- Third Section -->
        <div class="third-section">
        </div>
    </div>
<?php
the_content(); 
}
}
get_footer();
?>