<?php
/**
 * Assets class
 *
 * @author Jegstudio
 * @since 1.0.0
 * @package gutenverse-companion
 */

namespace Gutenverse_Companion\Essential;

use DateTime;

/**
 * Class Assets
 *
 * @package gutenverse-companion
 */
class Assets {

	/**
	 * Init constructor.
	 */
	public function __construct() {
		if ( ! class_exists( '\Gutenverse\Pro\License' ) ) {
			add_action( 'gutenverse_include_frontend', array( $this, 'frontend_scripts' ), 11 );
		}
		add_action( 'gutenverse_include_block', array( $this, 'editor_scripts' ), 1 );
		add_filter( 'gutenverse_block_config', array( $this, 'block_config' ) );
		add_filter( 'gutenverse_dashboard_config', array( $this, 'block_config' ) );
	}

	/**
	 * Enqueue script frontend.
	 */
	public function frontend_scripts() {
		$directory = apply_filters( 'gutenverse_companion_essential_assets_directory', false );
		$url       = apply_filters( 'gutenverse_companion_essential_assets_url', false );

		/**
		 * 'jeg_theme_essential_assets_directory' deprecated since version 1.0.1 Use 'gutenverse_companion_essential_assets_directory' instead.
		 */
		if ( ! $directory ) {
			$directory = apply_filters( 'jeg_theme_essential_assets_directory', false );
		}

		/**
		 * 'jeg_theme_essential_assets_url' deprecated since version 1.0.1 Use 'gutenverse_companion_essential_assets_url' instead.
		 */
		if ( ! $url ) {
			$url = apply_filters( 'jeg_theme_essential_assets_url', false );
		}

		if ( $directory && $url ) {
			wp_enqueue_script(
				'gutenverse-companion-frontend-event',
				$url . '/js/essential/profrontend.js',
				array(),
				GUTENVERSE_COMPANION_VERSION,
				true
			);

			$frontend_include = ( include_once $directory . '/dependencies/essential/frontend.asset.php' )['dependencies'];

			wp_enqueue_script(
				'gutenverse-companion-frontend',
				$url . '/js/essential/frontend.js',
				$frontend_include,
				GUTENVERSE_COMPANION_VERSION,
				true
			);

			// Register & Enqueue Style.
			wp_enqueue_style(
				'gutenverse-companion-frontend-block',
				$url . '/css/essential/frontend-essential.css',
				array( 'gutenverse-frontend-style' ),
				GUTENVERSE_COMPANION_VERSION
			);
		}
	}

	/**
	 * Enqueue Editor Scripts.
	 */
	public function editor_scripts() {
		$directory = apply_filters( 'gutenverse_companion_essential_assets_directory', false );
		$url       = apply_filters( 'gutenverse_companion_essential_assets_url', false );

		/**
		 * 'jeg_theme_essential_assets_directory' deprecated since version 1.0.1 Use 'gutenverse_companion_essential_assets_directory' instead.
		 */
		if ( ! $directory ) {
			$directory = apply_filters( 'jeg_theme_essential_assets_directory', false );
		}
		/**
		 * 'jeg_theme_essential_assets_url' deprecated since version 1.0.1 Use 'gutenverse_companion_essential_assets_url' instead.
		 */
		if ( ! $url ) {
			$url = apply_filters( 'jeg_theme_essential_assets_url', false );
		}

		if ( $directory && $url ) {
			$include = array_values(
				array_unique(
					array_merge(
						( include_once $directory . '/dependencies/essential/editor.sticky.asset.php' )['dependencies'],
						( include_once $directory . '/dependencies/essential/filter.asset.php' )['dependencies']
					)
				)
			);

			wp_enqueue_script(
				'gutenverse-companion-filter',
				$url . '/js/essential/filter-client.js',
				$include,
				GUTENVERSE_COMPANION_VERSION,
				true
			);

			wp_enqueue_style(
				'gutenverse-companion-editor-block',
				$url . '/css/essential/editor-essential.css',
				array( 'gutenverse-editor-style' ),
				GUTENVERSE_COMPANION_VERSION
			);

			wp_enqueue_style(
				'gutenverse-companion-frontend-block',
				$url . '/css/essential/frontend-essential.css',
				array( 'gutenverse-frontend-style' ),
				GUTENVERSE_COMPANION_VERSION
			);

			wp_enqueue_script(
				'gutenverse-companion-blocks',
				$url . '/js/essential/blocks.js',
				$include,
				GUTENVERSE_COMPANION_VERSION,
				true
			);

			// Register & Enqueue Style.
			wp_enqueue_style( 'gutenverse-companion-block' );

			if ( ! gutenverse_check_if_script_localized( 'GutenverseProJSURL' ) ) {
				wp_localize_script( 'gutenverse-companion-filter', 'GutenverseProJSURL', $this->js_pro_config() );

				wp_localize_script(
					'gutenverse-companion-block',
					'GutenverseProData',
					array(
						'roles'      => $this->get_all_roles(),
						'posttype'   => $this->get_all_post_type(),
						'taxonomies' => $this->get_all_taxonomies(),
					)
				);
			}
		}
	}

	/**
	 * Get All Taxonomies
	 *
	 * @return array
	 */
	public function get_all_taxonomies() {
		$result     = array();
		$taxonomies = get_taxonomies(
			array(
				'public' => true,
			),
			'object'
		);

		foreach ( $taxonomies as $key => $taxonomy ) {
			$result[] = array(
				'value' => $key,
				'label' => $taxonomy->label,
			);
		}

		return $result;
	}

	/**
	 * Get All Post Type
	 *
	 * @return array
	 */
	public function get_all_post_type() {
		$types = get_post_types(
			array(
				'public' => true,
			),
			'objects'
		);

		unset( $types['gutenverse-form'] );
		unset( $types['gutenverse-entries'] );
		unset( $types['attachment'] );

		$the_types = array();

		foreach ( $types as $key => $type ) {
			$the_types[] = array(
				'value' => $key,
				'label' => $type->label,
			);
		}

		return $the_types;
	}

	/**
	 * Get All Roles.
	 *
	 * @return array
	 */
	public function get_all_roles() {
		$roles = array();

		foreach ( get_editable_roles() as $key => $role ) {
			$roles[] = array(
				'value' => $key,
				'label' => $role['name'],
			);
		}

		return $roles;
	}

	/**
	 * JS Pro Config.
	 */
	public function js_pro_config() {
		$url = apply_filters( 'gutenverse_companion_essential_assets_url', false );

		/**
		 * 'jeg_theme_essential_assets_url' deprecated since version 1.0.1 Use 'gutenverse_companion_essential_assets_url' instead.
		 */
		if ( ! $url ) {
			$url = apply_filters( 'jeg_theme_essential_assets_url', false );
		}

		$arr_config = array(
			'wpJsonConfig' => array(
				'wpjson_url'      => get_rest_url(),
				'wpjson_nonce'    => wp_create_nonce( 'wp_rest' ),
				'wpjson_endpoint' => admin_url( 'admin-ajax.php?action=rest-nonce' ),
			),
		);
		if ( $url ) {
			$arr_config['editorSticky'] = $url . '/js/essential/editor.sticky.js';
			$arr_config['imgDir']       = $url . '/img';
		}
		return $arr_config;
	}
	/**
	 * JS Config.
	 *
	 * @param array $config .
	 */
	public function block_config( $config ) {
		$config['license']   = $this->get_license();
		$config['domainURL'] = get_site_url(); // Todo: check if we are using WordPress address.
		$config['current']   = ( new DateTime() )->getTimestamp();

		return $config;
	}

	/**
	 * Get dashboard data.
	 *
	 * @return array|boolean
	 */
	private function get_license() {
		return get_option( 'gutenverse-license', '' );
	}
}
