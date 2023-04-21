<?php
get_header();//call the header
get_template_part('preloader');//call the preloader function
while(have_posts()){
    the_post();
}
//background-image: url(<?php echo get_theme_file_uri('/file-directory/image.png') 
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
                    <form method="GET" id="filter-form">
                    <!-- Property Type -->
                    <div class="property-type" id="property-type">
                        <div class="dropbtn">
                            <img src="http://keystone.local/wp-content/uploads/2023/03/Property-icon.png">
                            <p>Property Type</p>
                            <i class="fas fa-chevron-down" style="display:inline-block; width:auto;float:right;"></i>
                        </div>
                        <div class="dropdown-content">
                            <a id="default">
                                Select Type
                            </a>
                            <a id="apartments">
                                Apartments
                            </a>
                            <a id="bungalows">
                                Bungalows
                            </a>
                            <a id="garages">
                                Garages
                            </a>
                            <a id="lands">
                                Lands
                            </a>
                            <a id="offices">
                                Offices
                            </a>
                            <a id="villas">
                                Villas
                            </a>
                            <input type="hidden" name="property-type-value" id="property-type-value"/>
                        </div>
                    </div>
                    <!-- Price Range -->
                    <div class="property-price-range" id="property-price-range">
                        <div class="dropbtn">
                            <img src="http://keystone.local/wp-content/uploads/2023/03/Pound.png">
                            <p>Price Range</p>
                            <i class="fas fa-chevron-down" style="display:inline-block; width:auto;"></i>
                        </div>
                        <div class="dropdown-content">
                            <a id="default">
                                Select Price Range
                            </a>
                            <a id="1">
                               £0 - £10,000
                            </a>
                            <a id="2">
                               £20,000 - £50,000
                            </a>
                            <a id="3">
                               £60,000 - £100,000
                            </a>
                            <a id="4">
                               £110,000 - £150,000
                            </a>
                            <a id="5">
                               £160,000 - £200,000
                            </a>
                            <a id="6">
                               £210,000 - £250,000
                            </a>
                            <a id="7">
                               £260,000 - £300,000
                            </a>
                            <a id="8">
                               £310,000 - £350,000
                            </a>
                            <a id="9">
                               £360,000 - £400,000
                            </a>
                            <a id="10">
                               £410,000 - £450,000
                            </a>
                            <a id="11">
                               £460,000 - £500,000
                            </a>
                            <a id="12">
                               £510,000 - £560,000
                            </a>
                            <input type="hidden" id="min-price" name="min-price">
                            <input type="hidden" id="max-price" name="max-price">
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
                            <a id="default">
                                Select Bedrooms
                            </a>
                            <a id="1">
                                1
                            </a>
                            <a id="2">
                                2
                            </a>
                            <a id="3">
                                3
                            </a>
                            <a id="4">
                                4
                            </a>
                            <a id="5">
                                5
                            </a>
                            <a id="6">
                                6
                            </a>
                            <a id="7">
                                7
                            </a>
                            <a id="8">
                                8
                            </a>
                            <a id="9">
                                9
                            </a>
                            <a id="10">
                                10
                            </a>
                            <input type="hidden" name="property-bedrooms-value" id="property-bedrooms-value"/>
                        </div>
                    </div>
                    <!-- Search button -->
                    <div  class="property-search-btn">
                        <button type="submit" name="search-btn" id="search-btn">Search <img src=""></button>
                    </div>
                    <!-- More View Button -->
                    <div class="property-more-btn">
                        <button>More Filters</button>
                    </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="third-section">
        </div>

        <div class="fourth-section">
            <div class="properties-section">

            <?php
                $property_type_value = ISSET( $_GET['property-type-value'] ) ? sanitize_text_field( $_GET['property-type-value'] ) : '';
                $property_bedrooms_value = ISSET( $_GET['property-bedrooms-value'] ) ? sanitize_text_field( $_GET['property-bedrooms-value'] ) : '';
                $min_price = isset($_GET['min-price']) ? $_GET['min-price'] : '';
                $max_price = isset($_GET['max-price']) ? $_GET['max-price'] : '';
                echo $property_type_value;
                if(ISSET($_GET['search-btn'])){//check filter form button if click
                $args = array( 
                    'post_type' => 'property', 
                    'post_per_page' => -1,
                    'meta_query' => array(
                        'relation' => 'OR',
                        array(
                            'key' => 'bedrooms',
                            'value' => $property_bedrooms_value,
                            'compare' => '=',
                        ),
                        array(
                            'key' => 'current_price',
                            'value' => array( $min_price, $max_price ),
                            'type' => 'numeric',
                            'compare' => 'BETWEEN',
                        ),
                    ),
                );
                }else{//if not click the filter form button
                    $args = array( 
                        'post_type' => 'property', 
                        'post_per_page' => -1
                    );
                }

                $properties_query = new WP_Query( $args );

                if( $properties_query->have_posts() ){
                    while( $properties_query->have_posts() ){

                        $properties_query->the_post();
                        $custom_fields = get_post_custom();//for custom fields
                        
                        $thumbnail_url = get_the_post_thumbnail_url( get_the_ID() );//get the image thumnail url
                        $property_link = get_permalink( get_the_ID() );//get the post link
                        $property_price = get_post_meta( get_the_ID(), 'current_price', true);//get post field
                        $property_price_for_you = get_post_meta( get_the_ID(), 'available_value', true);//get post field
                        $property_equity= get_post_meta( get_the_ID(), 'instant_equity', true);//get post field
                        $property_type = get_post_meta( get_the_ID(), 'property_type', true);
                        ?>
                        <!--Property Box-->
                        <div class="property-box">
                            <a href="<?php echo $property_link; ?>">

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
                        
                    }wp_reset_postdata();
                }
                else{
                    //echo esc_html__( 'No properties found', 'text-domain' );
                    ?>
                    <div style="margin-left:auto; margin-right:auto;">
                        <center>
                            <h3 class="filter-error-message">
                                No Properties Found
                            </h3>
                        </center>
                    </div>
                    <?php
                } 
                //Display the property using the Essential Real Estate shortcode
                //echo do_shortcode( '[es_property property_id="' . get_the_ID() . '"]' );

                //Display custom fields name
                /*echo '<ul>';
                foreach ( $custom_fields as $key => $value ) {
                    echo '<li>' . $key . '</li>';
                }
                echo '</ul>';*/
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
                    <a href="http://keystone.local/blog-2">View more articles >> </a>
                </div>
            </div>
        </div>
        <!-- end -->
     
    <?php
}

//Display Blog Page
if(is_page('Blog Page')){
    if(is_page_template('archive-page.php')){
        the_content();
        return;
    }
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
//Display BMV Properties
if(is_page('BMV')){
    the_content();
    ?>
    <div class="bmv-properties">
        <div class="second-section">
            <div class="filter-box">
                <div class="filters">
                    <!-- Property Search Field -->
                    <div class="property-searchfield-box">
                        <img src="http://keystone.local/wp-content/uploads/2023/04/Search-Normal.png">
                        <input type="text" id="property-searchfield" placeholder="Enter Keyword">
                    </div>
                    <!-- Property Type -->
                    <div class="property-type" id="property-type">
                        <div class="dropbtn">
                            <img src="http://keystone.local/wp-content/uploads/2023/03/Property-icon.png">
                            <p>Property Type</p>
                            <i class="fas fa-chevron-down" style="display:inline-block; width:auto;float:right;"></i>
                        </div>
                        <div class="dropdown-content">
                            <a href="" id="apartments">
                                Apartments
                            </a>
                            <a href="">
                                Bungalows
                            </a>
                            <a href="">
                                Garages
                            </a>
                            <a href="">
                                Lands
                            </a>
                            <a href="">
                                Offices
                            </a>
                            <a href="">
                                Villas
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
                               £0 - £10,000
                            </a>
                            <a href="">
                               £20,000 - £50,000
                            </a>
                            <a href="">
                               £60,000 - £100,000
                            </a>
                            <a href="">
                               £110,000 - £150,000
                            </a>
                            <a href="">
                               £160,000 - £200,000
                            </a>
                            <a href="">
                               £210,000 - £250,000
                            </a>
                            <a href="">
                               £260,000 - £300,000
                            </a>
                            <a href="">
                               £310,000 - £350,000
                            </a>
                            <a href="">
                               £360,000 - £400,000
                            </a>
                            <a href="">
                               £410,000 - £450,000
                            </a>
                            <a href="">
                               £460,000 - £500,000
                            </a>
                            <a href="">
                               £510,000 - £560,000
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
                                1
                            </a>
                            <a href="">
                                2
                            </a>
                            <a href="">
                                3
                            </a>
                            <a href="">
                                4
                            </a>
                            <a href="">
                                5
                            </a>
                            <a href="">
                                6
                            </a>
                            <a href="">
                                7
                            </a>
                            <a href="">
                                8
                            </a>
                            <a href="">
                                9
                            </a>
                            <a href="">
                                10
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
        <!-- First Section Section -->
        <div class="third-section">
            <div class="box">
                <p>
                    <a href="http://keystone.local/homepage" style="padding:0;margin:0; text-decoration:none; color:white;">Home</a> > BMV Properties
                </p>
            </div>
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

                $property_location = get_post_meta( get_the_ID(), 'property_address', true);//get post field
                $property_description = get_post_meta( get_the_ID(), 'property_description', true);//get post field
                $thumbnail_url = get_the_post_thumbnail_url( get_the_ID() );//get the image thumnail url
                $property_link = get_permalink( get_the_ID() );//get the post link
                $property_price = get_post_meta( get_the_ID(), 'current_price', true);//get post field
                $property_price_for_you = get_post_meta( get_the_ID(), 'available_value', true);//get post field
                $property_bedrooms = get_post_meta( get_the_ID(), 'bedrooms', true);
                $property_bathrooms = get_post_meta( get_the_ID(), 'bathrooms', true);
                $property_floor_area = get_post_meta( get_the_ID(), 'floor_area', true);
                $property_equity= get_post_meta( get_the_ID(), 'instant_equity', true);//get post field
                ?>
                <!--Property Box-->
                <div class="property-box">
                    <a href="<?php echo $property_link; ?>">

                        <!-- Property Image -->
                        <div class="img-box">
                            <img class="property-img" src="<?php echo $thumbnail_url; ?>">
                        </div>
                        
                        <div class="info-box">
                            <!-- Property Title -->
                            <h2 class="property-title"> 
                                <?php echo get_the_title();//function that get page or post title ?>
                            </h2>

                            <!-- Property Location -->
                            <p class="property-location"> 
                                <?php echo $property_location;//variable that get property location ?>
                            </p>

                            <!-- Property Description  -->
                            <p class="property-description"> 
                                <?php echo $property_description;//variable that get property price ?>
                            </p>

                            <!-- Property Current Value -->
                            <div class="property-current-value">
                                <p>
                                    Current Value: <br><span>£<?php echo $property_price;//variable that get current price ?></span>
                                </p>
                            </div>

                            <!-- Property available-for-you  -->
                            <div class="property-available-for-you">
                                <p>
                                Avalable to you for: <br><span>£<?php echo $property_price_for_you;//variable that get available price ?></span>
                                </p>
                            </div>

                            <!-- this is a break -->
                            <br>

                            <!-- Bedrooms -->
                            <div class="property-bedrooms">
                                <p>Bedrooms</p>
                                <div>
                                    <img src="http://keystone.local/wp-content/uploads/2023/03/1d1f0f0731a48358435c23c8cfa04fe1-1.png" alt="" style="width:20px;height:20px;"/>
                                    <p>
                                        <?php echo $property_bedrooms;//variable that get bedrooms ?>
                                    </p>
                                </div>
                            </div>

                            <!-- Bathrooms -->
                            <div class="property-bathrooms">
                                <p>Bathrooms</p>
                                <div>
                                    <img src="http://keystone.local/wp-content/uploads/2023/03/bathroom-1.png" alt="" style="width:20px;height:20px;"/>
                                    <p>
                                        <?php echo $property_bedrooms;//variable that get bedrooms ?>
                                    </p>
                                </div>
                            </div>

                            <!-- Property Equity -->
                            <div class="property-floor-area">
                                <p>Floor Area</p>
                                <div>
                                    <img src="http://keystone.local/wp-content/uploads/2023/03/area-1.png" alt="" style="width:20px;height:20px;"/>
                                    <p>
                                        <?php echo $property_floor_area;//variable that get floor area ?> m²
                                    </p>
                                </div>
                            </div>

                            <!-- this is a break -->
                            <br>

                            <div class="property-buttons">
                                <a href="#" id="view-more-button">View More</a>
                                <a href="http://keystone.local/contact-us/" id="call-us-button"><i class="fas fa-phone-alt"></i>&nbsp Call Us</a>
                            </div>
                        </div>
                    </a>
                    
                </div>
                <!-- end -->
                <?php
            
                }wp_reset_postdata();
            }
            else{
                echo esc_html__( 'No properties found', 'text-domain' );
            } 
        ?>
        </div>
    </div>
    <?php
}
if(is_page('Contact Us')){
    the_content();
}
if(is_page('My Profile')){
    the_content();
}
if(is_page_template('')){
}
get_footer();//call the footer
?>