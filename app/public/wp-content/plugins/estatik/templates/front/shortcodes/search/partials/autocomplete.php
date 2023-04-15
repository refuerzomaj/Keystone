<?php

/**
 * @var $addresses
 */

?>
<div class="es-autocomplete js-es-autocomplete content-font">
    <div class="es-address-list es-address-list--results">
        <?php if ( $addresses ) : ?>
            <ul>
                <?php foreach ( $addresses as $term_id => $address ) : ?>
                    <li class="es-address-list__item es-address-list__item--<?php echo $term_id; ?>">
                        <a href="" class="js-autocomplete-item" data-query="<?php esc_attr_e( $address ); ?>"><span class="es-icon es-icon_marker"></span><?php echo $address; ?></a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <b><?php _e( 'Location not found', 'es' ); ?></b>
        <?php endif; ?>
    </div>

    <?php if ( ! empty( $recent ) ) : ?>
        <div class="es-address-list es-address-list--recent">
            <div class="es-address-list__head"><?php _e( 'Recent searches', 'es' ); ?></div>
            <?php if ( $recent ) : ?>
                <ul>
                    <?php foreach ( $recent as $term_id => $address ) : ?>
                        <li class="es-address-list__item es-address-list__item--<?php echo $term_id; ?>">
                            <a href=""><span class="es-icon es-icon_marker"></span><?php echo $address; ?></a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <b><?php _e( 'Location not found', 'es' ); ?></b>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>
