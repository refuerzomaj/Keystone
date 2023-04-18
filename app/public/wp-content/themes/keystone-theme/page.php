<?php
get_header();//call the header
while(have_posts()){
    the_post();
}

//Display Homepage 
the_content(); 
if(is_page('Homepage')){
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
            <div class="title">
                <h2>Available Properties</h2>
                <div class="filters">
                    <div class="property-type" id="property-type">
                        <select>
                            <option>
                                <img src="http://keystone.local/wp-content/uploads/2023/03/Property-icon.png">
                                Property Type
                            </option>
                            <option></option>
                            <option></option>
                            <option></option>
                        </select>
                    
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
            <div class="box">
                <h2 class="title">Blog</h2>
            </div>
            <div class="blog-box">
                <?php 
                    $args2 = array(
                        'post_type' => 'post',
                        'post-status' => 'publish',
                        'posts_per_page' => -1
                    );
                        
                    $post_query = new WP_Query( $args2 );
                    $x = 1;
                    if ( $post_query->have_posts() ) {
                        while ( $post_query->have_posts() ) {
                            $post_query->the_post();
                            if($x == 1){
                                if( has_post_thumbnail() ){
                                    $featured_img_url = get_the_post_thumbnail_url(get_the_ID(),'medium');
                    ?>
                <div class="box-left">
                    <div class="first-blog-img" style="background-image: url('<?php echo $featured_img_url; ?>'); width: 666px; height: 396px; background-repeat: no-repeat; background-size: cover; border-radius:10px;" alt=" <?php the_title(); ?> ">
                        <?php  
                                }
                        ?>
                        <p class="blog-date" style="color:white; padding: 5px 23px 8px 31px; background-color:#EF9600;"><?php echo get_the_date(); ?></p>
                    </div>
                </div>
                <?php 
                            }
                            else if($x >=1 && $x <=1){ 
                ?>
                <div class="box-right"></div>
                <?php 
                            }
                            $x++;
                        }
                        wp_reset_postdata();
                    }
                ?>
                
            </div>
        </div>
        <!-- end -->
        <?php
$args = array(
    'post_type' => 'post',
    'post_status' => 'publish',
    'posts_per_page' => -1
);
$query = new WP_Query( $args );
 
if ( $query->have_posts() ) : while ( $query->have_posts() ) : $query->the_post(); ?>
 
    <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
    <?php if ( has_post_thumbnail() ) : ?>
        <a href="<?php the_permalink(); ?>"><?php the_post_thumbnail( 'medium' ); ?></a>
    <?php endif; ?>
    <p><?php the_excerpt(); ?></p>
 
<?php endwhile; endif; wp_reset_postdata(); ?>
    </div>
    <?php
}
//Display BMV Properties
if(is_page('BMV')){
    //echo '<script>alert("BMV Page")</script>';
}
get_footer();//call the footer
?>