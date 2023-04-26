<?php
//This Page is for BMV Properties Page

get_header();//call the header
get_template_part('preloader');
/*loops until have posts*/

if(is_page('BMV')){
    the_content();
    ?>
    <div class="bmv-properties">
        <div class="second-section">
            <div class="filter-box">
                <div class="filters">
                <form method="GET" id="filter-form" autocomplete="off">
                    <!-- Property Search Field -->
                    <div class="property-searchfield-box">
                        <img src="http://keystone.local/wp-content/uploads/2023/04/Search-Normal.png">
                        <input type="text" name="property-searchfield" id="property-searchfield" placeholder="Enter Keyword" style="border-color:none;">
                    </div>
                    <!-- Property Type -->
                    <div class="property-type" id="property-type">
                        <div class="dropbtn">
                            <img src="http://keystone.local/wp-content/uploads/2023/03/Property-icon.png">
                            <p>Property Type</p>
                            <i class="fas fa-chevron-down" style="display:inline-block; width:auto;float:right;"></i>
                        </div>
                        <div class="dropdown-content" id="dropdown-content">
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
                        <div class="dropdown-content" id="dropdown-content">
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
                        <div class="dropdown-content" id="dropdown-content">
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
                    <div class="property-search-btn" id="property-search-btn">
                    <button type="submit" name="search-btn" id="search-btn">Search <img src=""></button>
                    </div>
                    <!-- More View Button -->
                    <div class="property-more-btn" id="property-more-btn">
                        <button>More Filters</button>
                    </div>
                </form>
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
            $property_searchfield_value = ISSET( $_GET['property-searchfield'] ) ? sanitize_text_field( $_GET['property-searchfield'] ) : '';
            $property_type_value = ISSET( $_GET['property-type-value'] ) ? sanitize_text_field( $_GET['property-type-value'] ) : '';
            $property_bedrooms_value = ISSET( $_GET['property-bedrooms-value'] ) ? sanitize_text_field( $_GET['property-bedrooms-value'] ) : '';
            $min_price = ISSET($_GET['min-price']) ? $_GET['min-price'] : '';
            $max_price = ISSET($_GET['max-price']) ? $_GET['max-price'] : '';
            //echo $property_type_value;
            if(ISSET($_GET['search-btn'])){//check filter form button if click
                $args = array( 
                    'post_type' => 'property', 
                    'post_per_page' => -1,
                    'meta_query' => array(
                        'relation' => 'AND',
                    ),
                );
                if ( ! empty( $property_searchfield_value ) ){
                    $args['meta_query'][] = array(
                        'key' => 'property_title',
                        'value' => $property_searchfield_value,
                        'compare' => 'LIKE',
                    );
                }
                if ( ! empty( $property_type_value) ) {
                    $args['meta_query'][] = array(
                        'key' => 'property_type',
                        'value' => $property_type_value,
                        'compare' => 'LIKE',
                    );
                }
                if ( ! empty( $property_bedrooms_value ) ) {
                    $args['meta_query'][] = array(
                        'key' => 'bedrooms',
                        'value' => $property_bedrooms_value,
                        'compare' => '=',
                    );
                }
                if ( ! empty( $min_price ) && ! empty( $max_price ) ) {
                    $args['meta_query'][] = array(
                        'key' => 'current_price',
                        'value' => array( $min_price, $max_price ),
                        'type' => 'numeric',
                        'compare' => 'BETWEEN',
                    );
                }
            }
            else{//if not click the filter form button
                $args = array( 
                    'post_type' => 'property', 
                    'post_per_page' => -1,
                );
            }
            $args['orderby']  = 'meta_value';
            $args['meta_key'] = 'property_status';
            $args['order']    = 'ASC';
            $properties_query = new WP_Query( $args );
            
            if( $properties_query->have_posts() ){
                while( $properties_query->have_posts() ){

                $properties_query->the_post();
                $custom_fields = get_post_custom();//for custom fields

                $property_type = get_post_meta( get_the_ID(), 'property_type', true);
                if( !empty($property_type) ):
                    $property_type = implode(', ', (array) $property_type);
                endif;
                $property_status = get_post_meta( get_the_ID(), 'property_status', true);
                $property_status = implode(', ', (array) $property_status);
                $property_location = get_post_meta( get_the_ID(), 'property_address', true);//get post field
                $property_description = get_post_meta( get_the_ID(), 'property_description', true);//get post field
                $thumbnail_url = get_field( 'property_image_1' );//get the image thumnail url
                $property_link = get_permalink( get_the_ID() );//get the post link
                $property_price = get_post_meta( get_the_ID(), 'current_price', true);//get post field
                $property_price_for_you = get_post_meta( get_the_ID(), 'available_value', true);//get post field
                $property_bedrooms = get_post_meta( get_the_ID(), 'bedrooms', true);
                $property_bathrooms = get_post_meta( get_the_ID(), 'bathrooms', true);
                $property_floor_area = get_post_meta( get_the_ID(), 'floor_area', true);
                $property_rental_yield = get_post_meta( get_the_ID(), 'rental_yield', true);// get rental yield value
                $property_equity= get_post_meta( get_the_ID(), 'instant_equity', true);//get instant equity value
                $property_percentage_sold = get_post_meta( get_the_ID(), 'percentage_sold', true);
                ?>
                <!--Property Box-->
                <div class="property-box">
                    <a href="<?php echo $property_link; ?>">
                        <!-- Property Image -->
                        <div class="img-box">
                            <?php
                                if($property_status == "sold"){
                            ?>
                            <img class="property-img-sold" src="<?php echo $thumbnail_url['url']; ?>" alt="<?php echo $thumbnail_url['alt']; ?>"/>
                            <?php
                                }else{
                            ?>
                            <img class="property-img" src="<?php echo $thumbnail_url['url']; ?>" alt="<?php echo $thumbnail_url['alt']; ?>"/>
                            <?php
                                }
                            ?>
                        </div>
                        <div class="info-box">
                            <!-- Property Status For Sold Property Only -->
                            <?php 
                                if($property_status == "sold"){ 
                            ?>
                            <h2 class="property-status"> 
                                <?php echo strtoupper($property_status);//function that get page or post title ?>
                            </h2>
                            <?php 
                                }
                            ?>
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
                                    Market Value: <br><span>£<?php echo $property_price;//variable that get current price ?></span>
                                </p>
                            </div>

                            <!-- Property available-for-you  -->
                            <div class="property-available-for-you">
                                <p>
                                Avalable to you for: <br><span>£<?php echo $property_price_for_you;//variable that get available price ?></span>
                                </p>
                            </div>

                            <!-- Property Instant Equity  -->
                            <div class="property-instant-equity">
                                <p>
                                Instant Equity: <br><span>£<?php echo $property_equity;//variable that get available price ?></span>
                                </p>
                            </div>

                            <!-- Property Rental  -->
                            <div class="property-rental-yield">
                                <p>
                                Rental Yield: <br><span><?php echo $property_rental_yield;//variable that get available price ?></span>
                                </p>
                            </div>
                            <!-- this is a break -->
                            <!--<br>-->

                            <!-- Bedrooms -->
                            <!--<div class="property-bedrooms">-->
                                <!--<p>Bedrooms:</p>-->
                                <!--<div>-->
                                    <!--<img src="http://keystone.local/wp-content/uploads/2023/03/1d1f0f0731a48358435c23c8cfa04fe1-1.png" alt="" style="width:20px;height:20px;"/>-->
                                    <!--<p>-->
                                        <!--<?php  //$property_bedrooms;//variable that get bedrooms ?>-->
                                    <!--</p>-->
                                <!--</div>-->
                            <!--</div>-->

                            <!-- Bathrooms -->
                            <!--<div class="property-type">-->
                                <!--<p>Property Type:</p>-->
                                <!--<div>-->
                                    <!--<img src="http://keystone.local/wp-content/uploads/2023/03/bathroom-1.png" alt="" style="width:20px;height:20px;"/>-->
                                    <!--<p>-->
                                        <!--<?php //$property_type;//variable that get bedrooms ?>-->
                                    <!--</p>-->
                                <!--</div>-->
                            <!--</div>-->

                            <!-- Property Percentage Sold -->
                            <!--<div class="property-percentage">-->
                                <!--<div>-->
                                    <!--<img src="http://keystone.local/wp-content/uploads/2023/03/area-1.png" alt="" style="width:20px;height:20px;"/>-->
                                    <!--<p>-->
                                        <!--<?php  //$property_percentage_sold;//variable that get floor area ?> BMV-->
                                    <!--</p>-->
                                <!--</div>-->
                            <!--</div>-->

                            <!-- this is a break -->
                            <br>

                            <div class="property-buttons">
                                <a href="<?php echo $property_link; ?>" id="view-more-button">View More</a>
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
                //echo esc_html__( 'No properties found', 'text-domain' );
                ?>
                <div style="margin-left:auto; margin-right:auto; width:100%; height:165px; background-color:white;">
                    <center>
                        <h3 class="filter-error-message" style="color:black; padding-top:50px;">
                            No Properties Found
                        </h3>
                    </center>
                </div>
                <?php
            } 
        ?>
        </div>
    </div>
    <?php
}
get_footer();//call the footer
?>