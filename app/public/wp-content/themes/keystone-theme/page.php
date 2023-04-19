<?php
get_header();//call the header
while(have_posts()){
    the_post();
}

//Display Homepage 
if(is_page('Homepage')){
    the_content(); 
    ?>
    <div class="home-page">
        <!-- Seventh Section -->
        <div class="seventh-section">
            <div class="box">
                <h2>Ready to buy your next home</h2>
                <p>Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem. Ut enim ad minima veniam, quis nostrum exercitationem ullam</p>
                <a href="http://keystone.local/bmv-properties">Check available BMV Properties here</a>
            </div>
        </div>
        
        <!--<div class="first-section">
        </div>-->

        <div class="second-section">
            <div class="filter-box">
                <div class="title">
                    <h2>Available Properties</h2>
                </div>
                <div class="filters">
                    <!-- Property Type -->
                    <div class="property-type" id="property-type">
                        <div class="dropbtn">
                            <img src="http://keystone.local/wp-content/uploads/2023/03/Property-icon.png">
                            <p>Property Type</p>
                            <i class="fas fa-chevron-down" style="display:inline-block; width:auto;"></i>
                        </div>
                        <div class="dropdown-content">
                            <a href="">
                                Type 1
                            </a>
                            <a href="">
                                Type 2
                            </a>
                        </div>
                    </div>
                    <!-- Price Range -->
                    <div class="property-price-range" id="propert-price-range">
                        <div class="dropbtn">
                            <img src="http://keystone.local/wp-content/uploads/2023/03/Pound.png">
                            <p>Price Range</p>
                            <i class="fas fa-chevron-down" style="display:inline-block; width:auto;"></i>
                        </div>
                        <div class="dropdown-content">
                            <a href="">
                                Type 1
                            </a>
                            <a href="">
                                Type 2
                            </a>
                        </div>
                    </div>
                    <!-- Bed Rooms -->
                    <div class="property-bed-room" id="property-bed-room">
                        <div class="dropbtn">
                            <img src="http://keystone.local/wp-content/uploads/2023/03/Bed-icon.png">
                            <p>Bedrooms</p>
                            <i class="fas fa-chevron-down" style="display:inline-block; width:auto;"></i>
                        </div>
                        <div class="dropdown-content">
                            <a href="">
                                Type 1
                            </a>
                            <a href="">
                                Type 2
                            </a>
                        </div>
                    </div>
                    <!-- Search button -->
                    <div class="property-search-btn" id="property-search-btn">
                        <button>Search <img src=""></button>
                    </div>
                    <!-- More View Button -->
                    <div class="property-more-btn" id="property-more-btn">
                        <button>More Filters</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="third-section">
        </div>

        <div class="fourth-section">
            <div class="properties-section">

            <?php 
                $args = array( 
                    'post_type' => 'property', 
                    'post_per_pae' => -1,
                );

                $properties_query = new WP_Query( $args );

                if( $properties_query->have_posts() ){
                    while( $properties_query->have_posts() ){

                        $properties_query->the_post();
                        $custom_fields = get_post_custom();//for custom fields
                        
                        $thumbnail_url = get_the_post_thumbnail_url( get_the_ID() );//get the image thumnail url
                        $property_link = get_permalink( get_the_ID() );//get the post link
                        $property_price = get_post_field( 'bmv_property_current_price' );//get post field
                        $property_price_for_you = get_post_field( 'bmv_property_price_for_you' );//get post field
                        $property_equity= get_post_field(' bmv_property_equity ');//get post field
                        ?>
                        <!--Property Box-->
                        <div class="property-box">
                            <a href="<?php echo $property_link; ?>">
                                <?php
                                
                                ?>
                                <!-- Property Image -->
                                <img src="<?php echo $thumbnail_url; ?>">

                                <!-- Property Title -->
                                <h2 class="property-title"> 
                                    <?php echo get_the_title();//function that get page or post title ?>
                                </h2>

                                <!-- Property Current Value -->
                                <p class="property-current-value"> 
                                    Current Value: £<?php echo $property_price;//variable that get property price ?>
                                </p>

                                <!-- Property Available For You  -->
                                <p class="property-available-for-you"> 
                                    Avalable to you for: £<?php echo $property_price_for_you;//variable that get property price ?>
                                </p>

                                <!-- Property Equity -->
                                <p class="property-equity"> 
                                    Equity upon completion: £<?php echo $property_equity;//variable that get property price ?>
                                </p>


                            </a>
                            <a class="link-2" href="http://keystone.local/bmv-properties/">More Properties</a>
                        </div>
                        <!-- end -->
                        <?php
                    }
                }
                else{
                    echo esc_html__( 'No properties found', 'text-domain' );
                } 

            ?>
            </div>
        </div>
        <!-- Fifth Section -->
        <div class="fifth-section">
            <div class="box-section">
                <div class="box-left">
                    <h3 class="title">Below Market Value BMV Property</h3>
                    <p class="content1">At Keystone Invest, we offer property investors a complete BMV property investment solution. Simply select from any of the <span style="text-decoration:underline;">BMV properties</span> we currently have available, and we help you with the property completion. Investing in BMV property with us is a straight forward way to build your property investments.</p>
                    <p class="content2">We have built a reputation for sourcing some of the best BMV deals around. Our completion rate is high because we do our research correctly.</p>
                    <a href="http://keystone.local/bmv-properties">More BMV Properties here</a>
                </div>
                <div class="box-right">
                     <img src="http://keystone.local/wp-content/uploads/2023/03/e54693bc4dfb295bfc27f12c04088390.png" style="width:645px;height:416px;" alt="bmv_property_image">
                </div>
            </div>
        </div>
        <!-- end -->

        <!-- Sixth Section -->
        <div class="sixth-section">
            <div class="box">
                <div class="title">
                    <h2>Walk through your new property journey with us</h2>
                </div>
                <div class="steps-container">
                    <div class="box-1">
                        <p>01</p>
                        <p>Search through our list of properties</p>
                        <p>Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, </p>
                    </div>
                    <div class="box-2">
                        <p>02</p>
                        <p>Send us an inquiry of your chosen property</p>
                        <p>Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, </p>
                    </div>
                    <div class="box-3">
                        <p>03</p>
                        <p>Confirm the availability of the property</p>
                        <p>Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, </p>
                    </div>
                    <div class="box-4">
                        <p>04</p>
                        <p>Reservation of property memorandum of sale</p>
                        <p>Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, </p>
                    </div>
                </div>
            </div>
        </div>
        <!-- end -->
        <!-- Eight Section -->
        <div class="eight-section">
            <div class="blog-box">
                <h2 class="title">Blog</h2>
                <?php 

                    $arrayBlogImageUrl = array();
                    $arrayBlogTitle = array();
                    $arrayBlogDate = array();
                    $arrayBlogDescription = array();
                    $arrayBlogPermalink = array();
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
                        }
                        wp_reset_postdata();
                    }
                    ?>
                <div class="box-left">
                    <div class="blog">
                        <a href="<?php echo $arrayBlogPermalink[3]; ?>">
                            <div class="first-blog-img" style="background-image: url('<?php echo $arrayBlogImageUrl[3]; ?>'); " alt=" ">
                                <p class="blog-date"><?php echo $arrayBlogDate[3]; ?></p>
                            </div>
                            <div class="first-blog-title">
                                <h2>
                                    <?php echo $arrayBlogTitle[3]; ?>
                                </h2>
                                <p>
                                    Nullam rutrum maximus lacus. Nunc at aliquet massa, id molestie urna. Vestibulum leo ex, porttitor at eros non, vehicula hendrerit justo. Nulla volutpat ex id quam cursus, eget tincidunt diam laoreet. Quisque sagittis pharetra lacus ac scelerisque.
                                </p>
                            </div>
                        </a>
                    </div>    
                </div>
                <div class="box-right">
                    <a href="<?php echo $arrayBlogPermalink[2]; ?>">
                        <div class="blog">
                            <div class="blog-image">
                                <img src="<?php echo $arrayBlogImageUrl[2]; ?>" alt="<?php echo $arrayBlogTitle[2]; ?>">
                            </div>
                            <div class="blog-content">
                                <h2>
                                    <?php echo $arrayBlogTitle[2]; ?>
                                </h2>
                                <p>
                                    Aliquam quis fringilla quam. Praesent vitae ex sit amet velit interdum malesuada vitae a dui. Nullam rutrum maximus lacus. 
                                </p>
                            </div>
                        </div>
                    </a>
                    <a href="<?php echo $arrayBlogPermalink[1]; ?>">
                        <div class="blog">
                            <div class="blog-image">
                                <img src="<?php echo $arrayBlogImageUrl[1]; ?>" alt="<?php echo $arrayBlogTitle[1]; ?>">
                            </div>
                            <div class="blog-content">
                                <h2>
                                    <?php echo $arrayBlogTitle[1]; ?>
                                </h2>
                                <p>
                                    Cras in dui turpis. Quisque neque justo, eleifend et nunc nec, interdum laoreet massa. Aenean euismod est velit, sed congue turpis euismod at. 
                                </p>
                            </div>
                        </div>
                    </a>
                    <a href="<?php echo $arrayBlogPermalink[0]; ?>">
                        <div class="blog">
                            <div class="blog-image">
                                <img src="<?php echo $arrayBlogImageUrl[0]; ?>" alt="<?php echo $arrayBlogTitle[0]; ?>">
                            </div>
                            <div class="blog-content">
                            <h2>
                                    <?php echo $arrayBlogTitle[0]; ?>
                                </h2>
                                <p>
                                Vivamus vitae metus scelerisque, tristique est sit amet, faucibus quam. Integer et facilisis nibh. Aenean vel arcu felis. Ut quis erat eget sapien pharetra fringilla. 
                                </p>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="blog-view-more">
                    <a href="http://keystone.local/blog">View more articles >> </a>
                </div>
            </div>
        </div>
        <!-- end -->
     
    <?php
}

//Display BMV Properties
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
                        <div class="left-box">
                            <div class="img-post" style="background-image:url('<?php echo $arrayBlogImageUrl[0]; ?>');" alt="<?php echo $arrayBlogTitle[0];?>"></div>
                        </div>
                        <div class="right-box">
                            <h3><?php echo $arrayBlogTitle[0]; ?></h3>
                            <p><?php echo $arrayBlogDescription[0]; ?></p>
                        </div>
                    </div>
                    <div class="second-blog-box">
                        <?php 
                            for($x = 1; $x < $numOfBlogPost; $x++){
                                if($x % 2 === 0){
                        ?>
                        <div class="blog-box" style="margin:0 62px;">
                            <div class="img-post" style="background-image:url('<?php echo $arrayBlogImageUrl[$x]; ?>');" alt="<?php echo $arrayBlogTitle[$x];?>"></div>
                            <h3><?php echo $arrayBlogTitle[$x]; ?></h3>
                            <p><?php echo $arrayBlogDescription[$x]; ?></p>
                        </div>
                        <?php 
                                }
                                else{
                                    ?>
                        <div class="blog-box">
                            <div class="img-post" style="background-image:url('<?php echo $arrayBlogImageUrl[$x]; ?>');" alt="<?php echo $arrayBlogTitle[$x];?>"></div>
                            <h3><?php echo $arrayBlogTitle[$x]; ?></h3>
                            <p><?php echo $arrayBlogDescription[$x]; ?></p>
                        </div>
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
get_footer();//call the footer
?>