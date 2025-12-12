<?php
/**
 * Init Configuration
 *
 * @author Jegstudio
 * @package unibiz
 */

namespace Unibiz;

use WP_Query;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Init Class
 *
 * @package unibiz
 */
class Init {

	/**
	 * Instance variable
	 *
	 * @var $instance
	 */
	private static $instance;

	/**
	 * Class instance.
	 *
	 * @return Init
	 */
	public static function instance() {
		if ( null === static::$instance ) {
			static::$instance = new static();
		}

		return static::$instance;
	}

	/**
	 * Class constructor.
	 */
	private function __construct() {
		$this->init_instance();
		$this->load_hooks();
	}

	/**
	 * Load initial hooks.
	 */
	private function load_hooks() {
		add_action( 'init', array( $this, 'register_block_patterns' ), 9 );
		add_action( 'admin_enqueue_scripts', array( $this, 'dashboard_scripts' ) );

		add_action( 'wp_ajax_unibiz_set_admin_notice_viewed', array( $this, 'notice_closed' ) );

		add_action( 'after_switch_theme', array( $this, 'update_global_styles_after_theme_switch' ) );
		add_filter( 'gutenverse_template_path', array( $this, 'template_path' ), null, 3 );
		add_filter( 'gutenverse_themes_template', array( $this, 'add_template' ), 10, 2 );
		add_filter( 'gutenverse_block_config', array( $this, 'default_font' ), 10 );
		add_filter( 'gutenverse_font_header', array( $this, 'default_header_font' ) );
		add_filter( 'gutenverse_global_css', array( $this, 'global_header_style' ) );

		add_filter( 'gutenverse_stylesheet_directory', array( $this, 'change_stylesheet_directory' ) );
		add_filter( 'gutenverse_themes_override_mechanism', '__return_true' );

		add_filter( 'gutenverse_themes_support_section_global_style', '__return_true' );
		add_filter( 'gutenverse_companion_base_theme', '__return_true' );
		add_filter(
						'gutenverse_companion_menu_icon',
						function () {
							return trailingslashit( get_template_directory_uri() ) . 'assets/img/logo-icon-unibiz.svg';
						}
					);
	}

	/**
	 * Update Global Styles After Theme Switch
	 */
	public function update_global_styles_after_theme_switch() {
		// Get the path to the current theme's theme.json file
		$theme_json_path = get_template_directory() . '/theme.json';
		$theme_slug      = get_option( 'stylesheet' ); // Get the current theme's slug
		$args            = array(
			'post_type'      => 'wp_global_styles',
			'post_status'    => 'publish',
			'name'           => 'wp-global-styles-' . $theme_slug,
			'posts_per_page' => 1,
		);

		$global_styles_query = new WP_Query( $args );
		// Check if the theme.json file exists
		if ( file_exists( $theme_json_path ) && $global_styles_query->have_posts() ) {
			$global_styles_query->the_post();
			$global_styles_post_id = get_the_ID();
			// Step 2: Get the existing global styles (color palette)
			$global_styles_content = json_decode( get_post_field( 'post_content', $global_styles_post_id ), true );
			if ( isset( $global_styles_content['settings']['color']['palette']['theme'] ) ) {
				$existing_colors = $global_styles_content['settings']['color']['palette']['theme'];
			} else {
				$existing_colors = array();
			}

			// Step 3: Extract slugs from the existing colors
			$existing_slugs = array_column( $existing_colors, 'slug' );
			// Step 4:Read the contents of the theme.json file

			$theme_json_content = file_get_contents( $theme_json_path );
			$theme_json_data    = json_decode( $theme_json_content, true );

			// Access the color palette from the theme.json file
			if ( isset( $theme_json_data['settings']['color']['palette'] ) ) {

				$theme_colors = $theme_json_data['settings']['color']['palette'];

				// Step 5: Loop through theme.json colors and add them if they don't exist
				foreach ( $theme_colors as $theme_color ) {
					if ( ! in_array( $theme_color['slug'], $existing_slugs ) ) {
						$existing_colors[] = $theme_color; // Add new color to the existing palette
					}
				}
				foreach ( $theme_colors as $theme_color ) {
					$theme_slug = $theme_color['slug'];

					// Step 6: Use in_array to check if the slug already exists in the global palette
					if ( ! in_array( $theme_slug, $existing_slugs ) ) {
						// If the slug does not exist, add the theme color to the global palette
						$global_colors[] = $theme_color;
					}
				}
				// Step 6: Update the global styles content with the new colors
				$global_styles_content['settings']['color']['palette']['theme'] = $existing_colors;

				// Step 7: Save the updated global styles back to the post
				wp_update_post(
					array(
						'ID'           => $global_styles_post_id,
						'post_content' => wp_json_encode( $global_styles_content ),
					)
				);

			}
			wp_reset_postdata(); // Reset the query
		}
	}

	/**
	 * Change Stylesheet Directory.
	 *
	 * @return string
	 */
	public function change_stylesheet_directory() {
		return UNIBIZ_DIR . 'gutenverse-files/';
	}

	/**
	 * Initialize Instance.
	 */
	public function init_instance() {
		new Asset_Enqueue();
		new Plugin_Notice();
	}

	/**
	 * Notice Closed
	 */
	public function notice_closed() {
		if ( isset( $_POST['nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'unibiz_admin_notice' ) ) {
			update_user_meta( get_current_user_id(), 'gutenverse_install_notice', 'true' );
		}
		die;
	}

	/**
	 * Generate Global Font
	 *
	 * @param string $value  Value of the option.
	 *
	 * @return string
	 */
	public function global_header_style( $value ) {
		$theme_name      = get_stylesheet();
		$global_variable = get_option( 'gutenverse-global-variable-font-' . $theme_name );

		if ( empty( $global_variable ) && function_exists( 'gutenverse_global_font_style_generator' ) ) {
			$font_variable = $this->default_font_variable();
			$value        .= \gutenverse_global_font_style_generator( $font_variable );
		}

		return $value;
	}

	/**
	 * Header Font.
	 *
	 * @param mixed $value  Value of the option.
	 *
	 * @return mixed Value of the option.
	 */
	public function default_header_font( $value ) {
		if ( ! $value ) {
			$value = array(
				array(
					'value'  => 'Alfa Slab One',
					'type'   => 'google',
					'weight' => 'bold',
				),
			);
		}

		return $value;
	}

	/**
	 * Alter Default Font.
	 *
	 * @param array $config Array of Config.
	 *
	 * @return array
	 */
	public function default_font( $config ) {
		if ( empty( $config['globalVariable']['fonts'] ) ) {
			$config['globalVariable']['fonts'] = $this->default_font_variable();

			return $config;
		}

		if ( ! empty( $config['globalVariable']['fonts'] ) ) {
			// Handle existing fonts.
			$theme_name   = get_stylesheet();
			$initial_font = get_option( 'gutenverse-font-init-' . $theme_name );

			if ( ! $initial_font ) {
				$result = array();
				$array1 = $config['globalVariable']['fonts'];
				$array2 = $this->default_font_variable();
				foreach ( $array2 as $item ) { // default font
					$result[ $item['id'] ] = $item;
				}
				foreach ( $array1 as $item ) { // overwrite fonts
					$result[ $item['id'] ] = $item;
				}
				$fonts = array();
				foreach ( $result as $key => $font ) {
					$fonts[] = $font;
				}
				$config['globalVariable']['fonts'] = $fonts;

				update_option( 'gutenverse-font-init-' . $theme_name, true );
			}
		}

		return $config;
	}

	/**
	 * Default Font Variable.
	 *
	 * @return array
	 */
	public function default_font_variable() {
		return array(
            array (
  'id' => 'gv-font-primary',
  'name' => 'Primary',
  'font' => 
  array (
    'font' => 
    array (
      'label' => 'Host Grotesk',
      'value' => 'Host Grotesk',
      'type' => 'google',
    ),
    'size' => 
    array (
      'Desktop' => 
      array (
        'unit' => 'px',
        'point' => '64',
      ),
      'Mobile' => 
      array (
        'unit' => 'px',
        'point' => '42',
      ),
      'Tablet' => 
      array (
        'unit' => 'px',
      ),
    ),
    'lineHeight' => 
    array (
      'Desktop' => 
      array (
        'unit' => 'em',
        'point' => '1.125',
      ),
      'Mobile' => 
      array (
        'unit' => 'px',
      ),
      'Tablet' => 
      array (
        'unit' => 'px',
      ),
    ),
    'weight' => '700',
    'spacing' => 
    array (
      'Desktop' => '-0.02',
    ),
  ),
),array (
  'id' => 'gv-font-primary-alt',
  'name' => 'Primary Alt',
  'font' => 
  array (
    'font' => 
    array (
      'label' => 'Host Grotesk',
      'value' => 'Host Grotesk',
      'type' => 'google',
    ),
    'size' => 
    array (
      'Desktop' => 
      array (
        'unit' => 'px',
        'point' => '52',
      ),
      'Mobile' => 
      array (
        'unit' => 'px',
        'point' => '32',
      ),
    ),
    'lineHeight' => 
    array (
      'Desktop' => 
      array (
        'unit' => 'em',
        'point' => '1.15',
      ),
      'Mobile' => 
      array (
        'unit' => 'px',
      ),
    ),
    'weight' => '600',
    'spacing' => 
    array (
      'Desktop' => '-0.02',
    ),
  ),
),array (
  'id' => 'gv-font-secondary',
  'name' => 'Secondary',
  'font' => 
  array (
    'font' => 
    array (
      'label' => 'Host Grotesk',
      'value' => 'Host Grotesk',
      'type' => 'google',
    ),
    'size' => 
    array (
      'Desktop' => 
      array (
        'unit' => 'px',
        'point' => '48',
      ),
      'Mobile' => 
      array (
        'unit' => 'px',
        'point' => '32',
      ),
    ),
    'lineHeight' => 
    array (
      'Desktop' => 
      array (
        'unit' => 'em',
        'point' => '1.25',
      ),
      'Mobile' => 
      array (
        'unit' => 'px',
      ),
    ),
    'weight' => '700',
    'spacing' => 
    array (
      'Desktop' => '-0.02',
    ),
  ),
),array (
  'id' => 'gv-font-secondary-alt',
  'name' => 'Secondary Alt',
  'font' => 
  array (
    'font' => 
    array (
      'label' => 'Host Grotesk',
      'value' => 'Host Grotesk',
      'type' => 'google',
    ),
    'size' => 
    array (
      'Desktop' => 
      array (
        'unit' => 'px',
        'point' => '38',
      ),
      'Mobile' => 
      array (
        'unit' => 'px',
        'point' => '20',
      ),
    ),
    'lineHeight' => 
    array (
      'Desktop' => 
      array (
        'unit' => 'em',
        'point' => '1.3',
      ),
      'Mobile' => 
      array (
        'unit' => 'px',
      ),
    ),
    'weight' => '700',
  ),
),array (
  'id' => 'gv-font-feature',
  'name' => 'Feature',
  'font' => 
  array (
    'font' => 
    array (
      'label' => 'Host Grotesk',
      'value' => 'Host Grotesk',
      'type' => 'google',
    ),
    'size' => 
    array (
      'Desktop' => 
      array (
        'unit' => 'px',
        'point' => '26',
      ),
      'Mobile' => 
      array (
        'unit' => 'px',
        'point' => '22',
      ),
    ),
    'lineHeight' => 
    array (
      'Desktop' => 
      array (
        'unit' => 'em',
        'point' => '1.15',
      ),
      'Mobile' => 
      array (
        'unit' => 'px',
      ),
    ),
    'weight' => '700',
  ),
),array (
  'id' => 'gv-font-feature-alt',
  'name' => 'Feature Alt',
  'font' => 
  array (
    'font' => 
    array (
      'label' => 'Host Grotesk',
      'value' => 'Host Grotesk',
      'type' => 'google',
    ),
    'size' => 
    array (
      'Desktop' => 
      array (
        'unit' => 'px',
        'point' => '40',
      ),
      'Mobile' => 
      array (
        'unit' => 'px',
        'point' => '32',
      ),
    ),
    'lineHeight' => 
    array (
      'Desktop' => 
      array (
        'unit' => 'em',
        'point' => '1.2',
      ),
      'Mobile' => 
      array (
        'unit' => 'px',
      ),
      'Tablet' => 
      array (
        'unit' => 'px',
      ),
    ),
    'weight' => '700',
  ),
),array (
  'id' => 'gv-font-feature-secondary',
  'name' => 'Feature Secondary',
  'font' => 
  array (
    'font' => 
    array (
      'label' => 'Host Grotesk',
      'value' => 'Host Grotesk',
      'type' => 'google',
    ),
    'size' => 
    array (
      'Desktop' => 
      array (
        'unit' => 'px',
        'point' => '22',
      ),
      'Mobile' => 
      array (
        'unit' => 'px',
        'point' => '18',
      ),
    ),
    'lineHeight' => 
    array (
      'Desktop' => 
      array (
        'unit' => 'em',
        'point' => '1.1',
      ),
      'Mobile' => 
      array (
        'unit' => 'px',
      ),
    ),
    'weight' => '700',
  ),
),array (
  'id' => 'gv-font-feature-secondary-alt',
  'name' => 'Feature Secondary Alt',
  'font' => 
  array (
    'font' => 
    array (
      'label' => 'Host Grotesk',
      'value' => 'Host Grotesk',
      'type' => 'google',
    ),
    'size' => 
    array (
      'Desktop' => 
      array (
        'unit' => 'px',
        'point' => '32',
      ),
    ),
    'lineHeight' => 
    array (
      'Desktop' => 
      array (
        'unit' => 'em',
        'point' => '1.2',
      ),
      'Mobile' => 
      array (
        'unit' => 'px',
      ),
    ),
    'weight' => '500',
  ),
),array (
  'id' => 'gv-font-meta',
  'name' => 'Meta',
  'font' => 
  array (
    'font' => 
    array (
      'label' => 'Host Grotesk',
      'value' => 'Host Grotesk',
      'type' => 'google',
    ),
    'size' => 
    array (
      'Desktop' => 
      array (
        'unit' => 'px',
        'point' => '14',
      ),
      'Mobile' => 
      array (
        'unit' => 'px',
        'point' => '12',
      ),
    ),
    'lineHeight' => 
    array (
      'Desktop' => 
      array (
        'unit' => 'em',
        'point' => '1.2',
      ),
      'Mobile' => 
      array (
        'unit' => 'px',
      ),
    ),
    'weight' => '600',
  ),
),array (
  'id' => 'gv-font-meta-alt',
  'name' => 'Meta Alt',
  'font' => 
  array (
    'size' => 
    array (
      'Desktop' => 
      array (
        'unit' => 'px',
        'point' => '24',
      ),
    ),
    'font' => 
    array (
      'label' => 'Host Grotesk',
      'value' => 'Host Grotesk',
      'type' => 'google',
    ),
    'lineHeight' => 
    array (
      'Desktop' => 
      array (
        'unit' => 'em',
        'point' => '1.2',
      ),
      'Mobile' => 
      array (
        'unit' => 'px',
      ),
    ),
    'weight' => '500',
  ),
),array (
  'id' => 'gv-font-subheading',
  'name' => 'Subheading',
  'font' => 
  array (
    'font' => 
    array (
      'label' => 'Host Grotesk',
      'value' => 'Host Grotesk',
      'type' => 'google',
    ),
    'size' => 
    array (
      'Desktop' => 
      array (
        'unit' => 'px',
        'point' => '12',
      ),
    ),
    'lineHeight' => 
    array (
      'Desktop' => 
      array (
        'unit' => 'em',
        'point' => '1.3',
      ),
      'Mobile' => 
      array (
        'unit' => 'px',
      ),
    ),
    'weight' => '600',
    'spacing' => 
    array (
      'Desktop' => '0.05',
    ),
    'transform' => 'uppercase',
  ),
),array (
  'id' => 'gv-font-text-hero',
  'name' => 'Text Hero',
  'font' => 
  array (
    'font' => 
    array (
      'label' => 'Host Grotesk',
      'value' => 'Host Grotesk',
      'type' => 'google',
    ),
    'size' => 
    array (
      'Desktop' => 
      array (
        'unit' => 'px',
        'point' => '18',
      ),
      'Mobile' => 
      array (
        'unit' => 'px',
        'point' => '16',
      ),
    ),
    'lineHeight' => 
    array (
      'Desktop' => 
      array (
        'unit' => 'em',
        'point' => '1.6',
      ),
      'Mobile' => 
      array (
        'unit' => 'px',
      ),
    ),
    'weight' => '400',
  ),
),array (
  'id' => 'gv-font-text-hero-alt',
  'name' => 'Text Hero Alt',
  'font' => 
  array (
    'font' => 
    array (
      'label' => 'Host Grotesk',
      'value' => 'Host Grotesk',
      'type' => 'google',
    ),
    'size' => 
    array (
      'Desktop' => 
      array (
        'unit' => 'px',
        'point' => '18',
      ),
    ),
    'lineHeight' => 
    array (
      'Desktop' => 
      array (
        'unit' => 'em',
        'point' => '1',
      ),
      'Mobile' => 
      array (
        'unit' => 'px',
      ),
    ),
    'weight' => '500',
  ),
),array (
  'id' => 'gv-font-text',
  'name' => 'Text',
  'font' => 
  array (
    'font' => 
    array (
      'label' => 'Host Grotesk',
      'value' => 'Host Grotesk',
      'type' => 'google',
    ),
    'size' => 
    array (
      'Desktop' => 
      array (
        'unit' => 'px',
        'point' => '16',
      ),
      'Mobile' => 
      array (
        'unit' => 'px',
        'point' => '14',
      ),
    ),
    'lineHeight' => 
    array (
      'Desktop' => 
      array (
        'unit' => 'em',
        'point' => '1.5',
      ),
      'Mobile' => 
      array (
        'unit' => 'px',
      ),
    ),
    'weight' => '400',
  ),
),array (
  'id' => 'gv-font-text-small',
  'name' => 'Text Small',
  'font' => 
  array (
    'lineHeight' => 
    array (
      'Desktop' => 
      array (
        'unit' => 'em',
        'point' => '1.4',
      ),
      'Mobile' => 
      array (
        'unit' => 'px',
      ),
    ),
    'font' => 
    array (
      'label' => 'Host Grotesk',
      'value' => 'Host Grotesk',
      'type' => 'google',
    ),
    'size' => 
    array (
      'Desktop' => 
      array (
        'unit' => 'px',
        'point' => '14',
      ),
      'Mobile' => 
      array (
        'unit' => 'px',
        'point' => '12',
      ),
    ),
    'weight' => '400',
  ),
),array (
  'id' => 'gv-font-text-small-alt',
  'name' => 'Text Small Alt',
  'font' => 
  array (
    'font' => 
    array (
      'label' => 'Host Grotesk',
      'value' => 'Host Grotesk',
      'type' => 'google',
    ),
    'size' => 
    array (
      'Desktop' => 
      array (
        'unit' => 'px',
        'point' => '12',
      ),
    ),
    'lineHeight' => 
    array (
      'Desktop' => 
      array (
        'unit' => 'em',
        'point' => '1',
      ),
      'Mobile' => 
      array (
        'unit' => 'px',
      ),
    ),
    'weight' => '400',
  ),
),array (
  'id' => 'gv-font-button-primary',
  'name' => 'Button Primary',
  'font' => 
  array (
    'size' => 
    array (
      'Desktop' => 
      array (
        'unit' => 'px',
        'point' => '16',
      ),
      'Mobile' => 
      array (
        'unit' => 'px',
        'point' => '14',
      ),
    ),
    'lineHeight' => 
    array (
      'Desktop' => 
      array (
        'unit' => 'em',
        'point' => '1.5',
      ),
      'Mobile' => 
      array (
        'unit' => 'px',
      ),
    ),
    'weight' => '600',
    'font' => 
    array (
      'label' => 'Host Grotesk',
      'value' => 'Host Grotesk',
      'type' => 'google',
    ),
  ),
),array (
  'id' => 'gv-font-button-secondary',
  'name' => 'Button Secondary',
  'font' => 
  array (
    'size' => 
    array (
      'Desktop' => 
      array (
        'unit' => 'px',
        'point' => '15',
      ),
      'Mobile' => 
      array (
        'unit' => 'px',
        'point' => '13',
      ),
    ),
    'lineHeight' => 
    array (
      'Desktop' => 
      array (
        'unit' => 'em',
        'point' => '1',
      ),
      'Mobile' => 
      array (
        'unit' => 'px',
      ),
    ),
    'font' => 
    array (
      'label' => 'Host Grotesk',
      'value' => 'Host Grotesk',
      'type' => 'google',
    ),
    'weight' => '500',
  ),
),array (
  'id' => 'gv-font-form-label',
  'name' => 'Form Label',
  'font' => 
  array (
    'font' => 
    array (
      'label' => 'Host Grotesk',
      'value' => 'Host Grotesk',
      'type' => 'google',
    ),
    'size' => 
    array (
      'Desktop' => 
      array (
        'unit' => 'px',
        'point' => '15',
      ),
      'Mobile' => 
      array (
        'unit' => 'px',
        'point' => '13',
      ),
    ),
    'lineHeight' => 
    array (
      'Desktop' => 
      array (
        'unit' => 'em',
        'point' => '1',
      ),
      'Mobile' => 
      array (
        'unit' => 'px',
      ),
    ),
    'weight' => '400',
  ),
),array (
  'id' => 'gv-font-heading-404',
  'name' => 'Heading 404',
  'font' => 
  array (
    'size' => 
    array (
      'Desktop' => 
      array (
        'unit' => 'px',
        'point' => '200',
      ),
      'Mobile' => 
      array (
        'unit' => 'px',
        'point' => '120',
      ),
    ),
    'font' => 
    array (
      'label' => 'Host Grotesk',
      'value' => 'Host Grotesk',
      'type' => 'google',
    ),
    'lineHeight' => 
    array (
      'Desktop' => 
      array (
        'unit' => 'em',
        'point' => '1',
      ),
      'Mobile' => 
      array (
        'unit' => 'px',
      ),
    ),
    'weight' => '500',
  ),
),
		);
	}



	/**
	 * Add Template to Editor.
	 *
	 * @param array $template_files Path to Template File.
	 * @param array $template_type Template Type.
	 *
	 * @return array
	 */
	public function add_template( $template_files, $template_type ) {
		if ( 'wp_template' === $template_type ) {
			$new_templates = array(
				'index',
				'archive',
				'search',
				'page',
				'single',
				'404',
				'page-no-sidebar',
				'home',
				'blank-canvas',
				'page-with-sidebar'
			);

			foreach ( $new_templates as $template ) {
				$template_files[] = array(
					'slug'  => $template,
					'path'  => $this->change_stylesheet_directory() . "/templates/{$template}.html",
					'theme' => get_template(),
					'type'  => 'wp_template',
					'title' => ucfirst( str_replace( '-', ' ', $template ) ),
				);
			}
		}

		return $template_files;
	}

	/**
	 * Use gutenverse template file instead.
	 *
	 * @param string $template_file Path to Template File.
	 * @param string $theme_slug Theme Slug.
	 * @param string $template_slug Template Slug.
	 *
	 * @return string
	 */
	public function template_path( $template_file, $theme_slug, $template_slug ) {
		switch ( $template_slug ) {
            case 'index':
					return $this->change_stylesheet_directory() . '/templates/index.html';
			case 'archive':
					return $this->change_stylesheet_directory() . '/templates/archive.html';
			case 'search':
					return $this->change_stylesheet_directory() . '/templates/search.html';
			case 'page':
					return $this->change_stylesheet_directory() . '/templates/page.html';
			case 'single':
					return $this->change_stylesheet_directory() . '/templates/single.html';
			case '404':
					return $this->change_stylesheet_directory() . '/templates/404.html';
			case 'header':
					return $this->change_stylesheet_directory() . '/parts/header.html';
			case 'footer':
					return $this->change_stylesheet_directory() . '/parts/footer.html';
			case 'header-alternate':
					return $this->change_stylesheet_directory() . '/parts/header-alternate.html';
			case 'footer-alternate':
					return $this->change_stylesheet_directory() . '/parts/footer-alternate.html';
			case 'page-no-sidebar':
					return $this->change_stylesheet_directory() . '/templates/page-no-sidebar.html';
			case 'home':
					return $this->change_stylesheet_directory() . '/templates/home.html';
			case 'blank-canvas':
					return $this->change_stylesheet_directory() . '/templates/blank-canvas.html';
			case 'page-with-sidebar':
					return $this->change_stylesheet_directory() . '/templates/page-with-sidebar.html';
		}

		return $template_file;
	}

	/**
	 * Register Block Pattern.
	 */
	public function register_block_patterns() {
		new Block_Patterns();
	}

	/**
	 * Enqueue scripts and styles.
	 */
	public function dashboard_scripts() {
		if ( is_admin() ) {
			// enqueue css.
			

			wp_enqueue_script('wp-api-fetch');

			wp_localize_script( 'wp-api-fetch', 'GutenThemeConfig', $this->theme_config() );
		}
	}

	/**
	 * Check if plugin is installed.
	 *
	 * @param string $plugin_slug plugin slug.
	 * 
	 * @return boolean
	 */
	public function is_installed( $plugin_slug ) {
		$all_plugins = get_plugins();
		foreach ( $all_plugins as $plugin_file => $plugin_data ) {
			$plugin_dir = dirname($plugin_file);

			if ($plugin_dir === $plugin_slug) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Register static data to be used in theme's js file
	 */
	public function theme_config() {
		$active_plugins = get_option( 'active_plugins' );
		$plugins = array();
		foreach( $active_plugins as $active ) {
			$plugins[] = explode( '/', $active)[0];
		}

		$config = array(
			'home_url'     => home_url(),
			'version'      => UNIBIZ_VERSION,
			'images'       => get_template_directory_uri() . '/assets/img/',
			'title'        => esc_html__( 'Unibiz', 'unibiz' ),
			'description'  => esc_html__( 'Unibiz is a versatile modern WordPress theme designed for any niche with seamless Full Site Editing support and advanced customization, the first multipurpose theme fully compatible with FSE.', 'unibiz' ),
			'pluginTitle'  => esc_html__( 'Plugin Requirement', 'unibiz' ),
			'pluginDesc'   => esc_html__( 'This theme require some plugins. Please make sure all the plugin below are installed and activated.', 'unibiz' ),
			'note'         => esc_html__( '', 'unibiz' ),
			'note2'        => esc_html__( '', 'unibiz' ),
			'demo'         => esc_html__( '', 'unibiz' ),
			'demoUrl'      => esc_url( 'https://gutenverse.com/demo?name=unibiz' ),
			'install'      => '',
			'installText'  => esc_html__( 'Install Gutenverse Plugin', 'unibiz' ),
			'activateText' => esc_html__( 'Activate Gutenverse Plugin', 'unibiz' ),
			'doneText'     => esc_html__( 'Gutenverse Plugin Installed', 'unibiz' ),
			'dashboardPage'=> admin_url( 'themes.php?page=unibiz-dashboard' ),
			'logo'         => trailingslashit( get_template_directory_uri() ) . 'assets/img/logo-icon-unibiz.svg',
			'slug'         => 'unibiz',
			'upgradePro'   => 'https://gutenverse.com/pro',
			'supportLink'  => 'https://support.jegtheme.com/forums/forum/fse-themes/',
			'libraryApi'   => 'https://gutenverse.com//wp-json/gutenverse-server/v1',
			'docsLink'     => 'https://support.jegtheme.com/theme/fse-themes/',
			'pages'        => array(
				
			),
			'plugins'      => array(
				array(
					'slug'       		=> 'gutenverse',
					'title'      		=> 'Gutenverse',
					'short_desc' 		=> 'GUTENVERSE – GUTENBERG BLOCKS AND WEBSITE BUILDER FOR SITE EDITOR, TEMPLATE LIBRARY, POPUP BUILDER, ADVANCED ANIMATION EFFECTS, COMPLETE FEATURE ECOSYSTEM, 45+ FREE USER-FRIENDLY BLOCKS',
					'active'    		=> in_array( 'gutenverse', $plugins, true ),
					'installed'  		=> $this->is_installed( 'gutenverse' ),
					'icons'      		=> array (
  '1x' => 'https://ps.w.org/gutenverse/assets/icon-128x128.gif?rev=3132408',
  '2x' => 'https://ps.w.org/gutenverse/assets/icon-256x256.gif?rev=3132408',
),
					'download_url'      => '',
				),
				array(
					'slug'       		=> 'gutenverse-companion',
					'title'      		=> 'Gutenverse Companion',
					'short_desc' 		=> 'A companion plugin designed specifically to enhance and extend the functionality of Gutenverse base themes. This plugin integrates seamlessly with the base themes, providing additional features, customization options, and advanced tools to optimize the overall user experience and streamline the development process.',
					'active'    		=> in_array( 'gutenverse-companion', $plugins, true ),
					'installed'  		=> $this->is_installed( 'gutenverse-companion' ),
					'icons'      		=> array (
  '1x' => 'https://ps.w.org/gutenverse-companion/assets/icon-128x128.png?rev=3162415',
),
					'download_url'      => '',
				)
			),
			'assign'       => array(
				
			),
			'dashboardData'=> array(
				
			),
			
		);

		if ( isset( $config['assign'] ) && $config['assign'] ) {
			$assign = $config['assign'];
			foreach ( $assign as $key => $value ) {
				$query = new \WP_Query(
					array(
						'post_type'      => 'page',
						'post_status'    => 'publish',
						'title'          => '' !== $value['page'] ? $value['page'] : $value['title'],
						'posts_per_page' => 1,
					)
				);

				if ( $query->have_posts() ) {
					$post                     = $query->posts[0];
					$page_template            = get_page_template_slug( $post->ID );
					$assign[ $key ]['status'] = array(
						'exists'         => true,
						'using_template' => $page_template === $value['slug'],
					);

				} else {
					$assign[ $key ]['status'] = array(
						'exists'         => false,
						'using_template' => false,
					);
				}

				wp_reset_postdata();
			}
			$config['assign'] = $assign;
		}

		return $config;
	}
	
}
