<?php

/**
 * Class Es_Migrations.
 */
class Es_Migrations {

	/**
	 * List of executed migrations IDs.
	 *
	 * @var array
	 */
	public static $executed_migrations_list = array();

	/**
	 * Initialzie migrations class.
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'init', array( 'Es_Migrations', 'execute' ) );
	}

	/**
	 * Execute migrations.
	 *
	 * @return void
	 */
	public static function execute() {

		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();

		if ( ! function_exists( 'dbDelta' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		}

		if ( ! static::is_executed( 'field-builder-setup-db' ) ) {

			$visible_for = serialize( array( 'all_users' ) );

			$sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}estatik_fb_sections (
					  id mediumint(9) NOT NULL AUTO_INCREMENT,
					  `label` VARCHAR(255) NOT NULL,
					  `options` TEXT,
					  machine_name VARCHAR(255) NOT NULL,
					  entity_name VARCHAR(255) NOT NULL,
					  is_visible INT(1) DEFAULT 1,
                	  `is_visible_for` VARCHAR(255) DEFAULT '$visible_for',
					  `order` INT(9) NOT NULL,
					  PRIMARY KEY (id)
					) $charset_collate;";

			$sql2 = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}estatik_fb_fields (
					  id mediumint(9) NOT NULL AUTO_INCREMENT,
					  `label` VARCHAR(255) NOT NULL,
					  frontend_form_name VARCHAR(255),
					  machine_name VARCHAR(255) NOT NULL,
					  `type` VARCHAR(255) NOT NULL,
					  `values` TEXT,
					  `options` TEXT,
					  section_machine_name VARCHAR(255) NOT NULL,
					  tab_machine_name VARCHAR(255) NOT NULL,
					  entity_name VARCHAR(255) NOT NULL DEFAULT 'property',
					  mandatory INT(1) DEFAULT 0,
					  address_component VARCHAR(255),
                	  search_support INT(1) DEFAULT 0,
                	  mls_import_support INT(1) DEFAULT 0,
                	  is_visible INT(1) DEFAULT 0,
                	  `is_visible_for` VARCHAR(255) DEFAULT '$visible_for',
					  `order` INT(9) NOT NULL,
					  PRIMARY KEY (id)
					) $charset_collate;";

			if ( $wpdb->query( $sql ) && $wpdb->query( $sql2 ) ) {
				static::set_executed( 'field-builder-setup-db' );
			}
		}

		if ( ! static::is_executed( 'default_taxonomy_terms' ) ) {
			$terms = array(

				// Categories.
				array( __( 'For sale', 'es' ), 'es_category', ),
				array( __( 'For rent', 'es' ), 'es_category', ),

				// Types.
				array( __( 'Houses', 'es' ), 'es_type', ),
				array( __( 'Apartments', 'es' ), 'es_type', ),
				array( __( 'Townhouses', 'es' ), 'es_type', ),
				array( __( 'Condos', 'es' ), 'es_type', ),
				array( __( 'Multifamily', 'es' ), 'es_type', ),
				array( __( 'Land', 'es' ), 'es_type', ),

				// Statuses.
				array( __( 'Active', 'es' ), 'es_status', ),
				array( __( 'Unpublished', 'es' ), 'es_status', ),
				array( __( 'Pending', 'es' ), 'es_status', ),
				array( __( 'Auto-draft', 'es' ), 'es_status', ),
				array( __( 'Deleted', 'es' ), 'es_status', ),
				array( __( 'Sold', 'es' ), 'es_status', ),

				// Rent reriods.
				array( __( 'per day', 'es' ), 'es_rent_period', ),
				array( __( 'per week', 'es' ), 'es_rent_period', ),
				array( __( 'per month', 'es' ), 'es_rent_period', ),
				array( __( 'per year', 'es' ), 'es_rent_period', ),

				// Parking
				array( __( 'Carport', 'es' ), 'es_parking', ),
				array( __( 'Garage - attached', 'es' ), 'es_parking', ),
				array( __( 'Garage - detached', 'es' ), 'es_parking', ),
				array( __( 'Off-street', 'es' ), 'es_parking', ),
				array( __( 'On-street', 'es' ), 'es_parking', ),

				// Roof.
				array( __( 'Asphalt', 'es' ), 'es_roof', ),
				array( __( 'Built-up', 'es' ), 'es_roof', ),
				array( __( 'Composition', 'es' ), 'es_roof', ),
				array( __( 'Metal', 'es' ), 'es_roof', ),
				array( __( 'Shake/Shingle', 'es' ), 'es_roof', ),
				array( __( 'Slate', 'es' ), 'es_roof', ),
				array( __( 'Tile', 'es' ), 'es_roof', ),

				// Exterior material.
				array( __( 'Brick', 'es' ), 'es_exterior_material', ),
				array( __( 'Cement/concrete', 'es' ), 'es_roof', ),
				array( __( 'Composition', 'es' ), 'es_roof', ),
				array( __( 'Metal', 'es' ), 'es_roof', ),
				array( __( 'Shingle', 'es' ), 'es_roof', ),
				array( __( 'Stone', 'es' ), 'es_roof', ),
				array( __( 'Stucco', 'es' ), 'es_roof', ),
				array( __( 'Vinyl', 'es' ), 'es_roof', ),
				array( __( 'Wood', 'es' ), 'es_roof', ),
				array( __( 'Wood products', 'es' ), 'es_roof', ),

				// Basement
				array( __( 'Finished', 'es' ), 'es_basement', ),
				array( __( 'Partially finished', 'es' ), 'es_basement', ),
				array( __( 'Unfinished', 'es' ), 'es_basement', ),

				// Floor covering.
				array( __( 'Carpet', 'es' ), 'es_floor_covering', ),
				array( __( 'Concrete', 'es' ), 'es_floor_covering', ),
				array( __( 'Hard wood', 'es' ), 'es_floor_covering', ),
				array( __( 'Laminate', 'es' ), 'es_floor_covering', ),
				array( __( 'Linoleum/vinyl', 'es' ), 'es_floor_covering', ),
				array( __( 'Slate', 'es' ), 'es_floor_covering', ),
				array( __( 'Softwood', 'es' ), 'es_floor_covering', ),
				array( __( 'Tile', 'es' ), 'es_floor_covering', ),
			);

			foreach ( $terms as $term_args ) {
				if ( ! term_exists( $term_args[0], $term_args[1] ) ) {
					$result = call_user_func_array( 'wp_insert_term', $term_args );

					if ( ! is_wp_error( $result ) && in_array( $term_args[1], array( 'es_category', 'es_status' ) ) ) {
						update_term_meta( $result['term_id'], 'es_default_term', 1 );
					}
				}
			}

			$labels = array(
				__( 'Featured', 'es' ) => '#FFB300',
				__( 'Foreclosure', 'es' ) => '#FFFFFF',
				__( 'Open house', 'es' ) => '#FFFFFF',
			);

			foreach ( $labels as $label => $color ) {
				if ( ! term_exists( $label, 'es_label' ) ) {
					$label_term = wp_insert_term( $label, 'es_label' );

					if ( ! is_wp_error( $label_term ) ) {
						update_term_meta( $label_term['term_id'], 'es_color', $color );

						if ( $label == __( 'Featured', 'es' ) ) {
							update_term_meta( $label_term['term_id'], 'es_default_term', 1 );
							ests_save_option( 'featured_term_id', array( 'default' => $label_term['term_id'], es_get_locale() => $label_term['term_id'] ) );
						}
					}
				}
			}

			$features = array(
				__( 'Air conditioning', 'es' ) => (object) array( 'type' => 'es-icon', 'icon' => '<span class="es-icon es-icon_air-cond"></span>' ),
				__( 'Heating', 'es' ) => (object) array( 'type' => 'es-icon', 'icon' => '<span class="es-icon es-icon_heating"></span>' ),
				__( 'Swimming pool', 'es' ) => (object) array( 'type' => 'es-icon', 'icon' => '<span class="es-icon es-icon_pool"></span>' ),
				__( 'Garden', 'es' ) => (object) array( 'type' => 'es-icon', 'icon' => '<span class="es-icon es-icon_garden"></span>' ),
				__( 'Balcony', 'es' ) => (object) array( 'type' => 'es-icon', 'icon' => '<span class="es-icon es-icon_balcony"></span>' ),
				__( 'Terrace', 'es' ) => (object) array( 'type' => 'es-icon', 'icon' => '<span class="es-icon es-icon_terrace"></span>' ),
				__( 'Fire alarm', 'es' ) => (object) array( 'type' => 'es-icon', 'icon' => '<span class="es-icon es-icon_fire-alarm"></span>' ),
				__( 'Smoke detector', 'es' ) => (object) array( 'type' => 'es-icon', 'icon' => '<span class="es-icon es-icon_smoke-detector"></span>' ),
				__( 'Carbon monoxide detector', 'es' ) => (object) array( 'type' => 'es-icon', 'icon' => '<span class="es-icon es-icon_carbon-monoxide-detector"></span>' ),
				__( 'Bellhop', 'es' ) => (object) array( 'type' => 'es-icon', 'icon' => '<span class="es-icon es-icon_bellhop"></span>' ),
				__( 'Fireplace', 'es' ) => (object) array( 'type' => 'es-icon', 'icon' => '<span class="es-icon es-icon_fireplace"></span>' ),
			);

			foreach ( $features as $label => $icon ) {
				if ( ! term_exists( $label, 'es_feature' ) ) {
					$label = wp_insert_term( $label, 'es_feature' );

					if ( ! is_wp_error( $label ) ) {
						update_term_meta( $label['term_id'], 'es_icon', $icon );
					}
				}
			}

			$amenities = array(
				__( 'Microwave', 'es' ) => (object) array( 'type' => 'es-icon', 'icon' => '<span class="es-icon es-icon_microwave"></span>' ),
				__( 'Dishwasher', 'es' ) => (object) array( 'type' => 'es-icon', 'icon' => '<span class="es-icon es-icon_dishwasher"></span>' ),
				__( 'Refrigerator', 'es' ) => (object) array( 'type' => 'es-icon', 'icon' => '<span class="es-icon es-icon_refrigerator"></span>' ),
				__( 'Cable TV', 'es' ) => (object) array( 'type' => 'es-icon', 'icon' => '<span class="es-icon es-icon_tv"></span>' ),
				__( 'Wifi', 'es' ) =>  (object) array( 'type' => 'es-icon', 'icon' => '<span class="es-icon es-icon_wifi"></span>' ),
				__( 'Iron', 'es' ) => (object) array( 'type' => 'es-icon', 'icon' => '<span class="es-icon es-icon_iron"></span>' ),
				__( 'Oven', 'es' ) => (object) array( 'type' => 'es-icon', 'icon' => '<span class="es-icon es-icon_oven"></span>' ),
				__( 'Jacuzzi', 'es' ) => (object) array( 'type' => 'es-icon', 'icon' => '<span class="es-icon es-icon_jacuzzi"></span>' ),
				__( 'Trash compactor', 'es' ) => (object) array( 'type' => 'es-icon', 'icon' => '<span class="es-icon es-icon_trash-compactor"></span>' ),
				__( 'Garbage disposal', 'es' ) => (object) array( 'type' => 'es-icon', 'icon' => '<span class="es-icon es-icon_garbage-disposal"></span>' ),
				__( 'Dryer', 'es' ) => (object) array( 'type' => 'es-icon', 'icon' => '<span class="es-icon es-icon_dryer"></span>' ),
			);

			foreach ( $amenities as $label => $icon ) {
				if ( ! term_exists( $label, 'es_amenity' ) ) {
					$label = wp_insert_term( $label, 'es_amenity' );

					if ( ! is_wp_error( $label ) ) {
						update_term_meta( $label['term_id'], 'es_icon', $icon );
					}
				}
			}

			static::set_executed( 'default_taxonomy_terms' );
		}

		if ( ! static::is_executed( 'simple_roles' ) ) {

			$capabilities = array(
				'read_es_property',
				'edit_es_property',
				'create_es_properties',
				'edit_es_properties',
				'edit_published_es_properties',
				'delete_published_es_properties',
				'delete_es_property',
				'publish_es_properties',
				'edit_others_es_properties',
				'read_private_es_properties',
				'delete_es_properties',
				'delete_private_es_properties',
				'delete_others_es_properties',
				'edit_private_es_properties',
			);

			$role = get_role( 'administrator' );

			foreach ( $capabilities as $cap ) {
				if ( $role->has_cap( $cap ) ) continue;

				$role->add_cap( $cap );
			}

			if ( $role->has_cap( 'edit_private_es_properties' ) ) {
				static::set_executed( 'simple_roles' );
			}
		}

		if ( ! static::is_executed( 'fb_compare_support' ) ) {
			$queried = $wpdb->query( "ALTER TABLE {$wpdb->prefix}estatik_fb_fields ADD compare_support INT(1)" );

			if ( $queried ) {
				static::set_executed( 'fb_compare_support' );
			}
		}
	}

	/**
	 * Set and return executed migrations list IDs.
	 *
	 * @return array
	 */
	public static function get_executed_list() {
		if (  empty( static::$executed_migrations_list ) ) {
			static::$executed_migrations_list = get_option( 'es_migrations', array() );
		}

		return static::$executed_migrations_list ? static::$executed_migrations_list : array();
	}

	/**
	 * Check is migration already executed.
	 *
	 * @param $migration_id
	 *
	 * @return bool
	 */
	public static function is_executed( $migration_id ) {
		$run_migration = filter_input( INPUT_GET, 'es-migration-execute-id' );
		$executed_list = static::get_executed_list();
		return in_array( $migration_id, $executed_list ) && $run_migration != $migration_id;
	}

	/**
	 * Set migration as executed.
	 *
	 * @param $migration_id
	 */
	public static function set_executed( $migration_id ) {
		static::$executed_migrations_list = static::$executed_migrations_list ? static::$executed_migrations_list : array();
		static::$executed_migrations_list[] = $migration_id;
		update_option( 'es_migrations', static::$executed_migrations_list );
	}
}

Es_Migrations::init();
