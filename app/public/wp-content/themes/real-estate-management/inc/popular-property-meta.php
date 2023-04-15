<?php

// Property Meta
function real_estate_management_bn_custom_meta_room() {
    add_meta_box( 'bn_meta', __( 'Property Meta Feilds', 'real-estate-management' ), 'real_estate_management_meta_callback_room', 'post', 'normal', 'high' );
}

if (is_admin()){
  add_action('admin_menu', 'real_estate_management_bn_custom_meta_room');
}

function real_estate_management_meta_callback_room( $post ) {
    wp_nonce_field( basename( __FILE__ ), 'real_estate_management_room_meta_nonce' );
    $bn_stored_meta = get_post_meta( $post->ID );
    $flat_location = get_post_meta( $post->ID, 'real_estate_management_flat_location', true );
    $flat_rent = get_post_meta( $post->ID, 'real_estate_management_flat_rent', true );
    $flat_bedroom = get_post_meta( $post->ID, 'real_estate_management_flat_bedroom', true );
    $flat_bathroom = get_post_meta( $post->ID, 'real_estate_management_flat_bathroom', true );
    $flat_sqrfit = get_post_meta( $post->ID, 'real_estate_management_flat_sqrfit', true );
    ?>
    <div id="testimonials_custom_stuff">
        <table id="list">
            <tbody id="the-list" data-wp-lists="list:meta">
                <tr id="meta-8">
                    <td class="left">
                        <?php esc_html_e( 'Flat Location', 'real-estate-management' )?>
                    </td>
                    <td class="left">
                        <input type="text" name="real_estate_management_flat_location" id="real_estate_management_flat_location" value="<?php echo esc_attr($flat_location); ?>" />
                    </td>
                </tr>
                <tr id="meta-8">
                    <td class="left">
                        <?php esc_html_e( 'Flat Rent', 'real-estate-management' )?>
                    </td>
                    <td class="left">
                        <input type="text" name="real_estate_management_flat_rent" id="real_estate_management_flat_rent" value="<?php echo esc_attr($flat_rent); ?>" />
                    </td>
                </tr>
                <tr id="meta-8">
                    <td class="left">
                        <?php esc_html_e( 'No of Bedrooms', 'real-estate-management' )?>
                    </td>
                    <td class="left">
                        <input type="text" name="real_estate_management_flat_bedroom" id="real_estate_management_flat_bedroom" value="<?php echo esc_attr($flat_bedroom); ?>" />
                    </td>
                </tr>
                <tr id="meta-8">
                    <td class="left">
                        <?php esc_html_e( 'No of Bathrooms', 'real-estate-management' )?>
                    </td>
                    <td class="left">
                        <input type="text" name="real_estate_management_flat_bathroom" id="real_estate_management_flat_bathroom" value="<?php echo esc_attr($flat_bathroom); ?>" />
                    </td>
                </tr>
                <tr id="meta-8">
                    <td class="left">
                        <?php esc_html_e( 'Flat Sqft Area', 'real-estate-management' )?>
                    </td>
                    <td class="left">
                        <input type="text" name="real_estate_management_flat_sqrfit" id="real_estate_management_flat_sqrfit" value="<?php echo esc_attr($flat_sqrfit); ?>" />
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <?php
}

/* Saves the custom meta input */
function real_estate_management_bn_metadesig_save( $post_id ) {
    if (!isset($_POST['real_estate_management_room_meta_nonce']) || !wp_verify_nonce( strip_tags( wp_unslash( $_POST['real_estate_management_room_meta_nonce']) ), basename(__FILE__))) {
        return;
    }

    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // Save
    if( isset( $_POST[ 'real_estate_management_flat_location' ] ) ) {
        update_post_meta( $post_id, 'real_estate_management_flat_location', strip_tags( wp_unslash( $_POST[ 'real_estate_management_flat_location' ]) ) );
    }

    if( isset( $_POST[ 'real_estate_management_flat_rent' ] ) ) {
        update_post_meta( $post_id, 'real_estate_management_flat_rent', strip_tags( wp_unslash( $_POST[ 'real_estate_management_flat_rent' ]) ) );
    }

    if( isset( $_POST[ 'real_estate_management_flat_bedroom' ] ) ) {
        update_post_meta( $post_id, 'real_estate_management_flat_bedroom', strip_tags( wp_unslash( $_POST[ 'real_estate_management_flat_bedroom' ]) ) );
    }

    if( isset( $_POST[ 'real_estate_management_flat_bathroom' ] ) ) {
        update_post_meta( $post_id, 'real_estate_management_flat_bathroom', strip_tags( wp_unslash( $_POST[ 'real_estate_management_flat_bathroom' ]) ) );
    }
    if( isset( $_POST[ 'real_estate_management_flat_sqrfit' ] ) ) {
        update_post_meta( $post_id, 'real_estate_management_flat_sqrfit', strip_tags( wp_unslash( $_POST[ 'real_estate_management_flat_sqrfit' ]) ) );
    }
}
add_action( 'save_post', 'real_estate_management_bn_metadesig_save' );