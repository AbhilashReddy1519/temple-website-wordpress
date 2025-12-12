<?php
/**
 * Gutenverse Theme class
 *
 * @author Jegstudio
 * @package gutenverse-companion
 */

namespace Gutenverse_Companion\Gutenverse_Theme;

use Gutenverse_Companion\Dashboard;
use WP_Error;
use WP_Query;

/**
 * Class Gutenverse_Theme
 *
 * @package gutenverse-companion
 */
class Gutenverse_Theme {
	/**
	 * Endpoint Path
	 *
	 * @var string
	 */
	const ENDPOINT = 'gtb-themes-backend/v1';

	/**
	 * Theme Slug
	 *
	 * @var string
	 */
	private $theme_slug = '';

	/**
	 * Blocks constructor.
	 */
	public function __construct() {
		$this->theme_slug = get_option( 'stylesheet' );
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
		add_action( 'admin_menu', array( $this, 'theme_wizard' ) );
		add_action( 'admin_init', array( $this, 'theme_redirect' ), 99 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ), 99 );
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );

		/**Remove if Designer has make all theme to 100% */
		add_filter( 'wp_theme_json_data_theme', array( $this, 'layout_max_width_theme_json' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'frontend_script' ), 999 );
	}

	/**
	 * Overwrite layout max width css
	 *
	 * @return void
	 */
	public function frontend_script() {
		$global_settings = wp_get_global_settings();

		// Get layout sizes (may or may not exist) .
		$layout_content_size = isset( $global_settings['layout']['contentSize'] ) ? $global_settings['layout']['contentSize'] : null;
		$layout_wide_size    = isset( $global_settings['layout']['wideSize'] ) ? $global_settings['layout']['wideSize'] : null;

		/** Normalize and check values */
		$is_full_width = (
			( ! empty( $layout_content_size ) && '100%' === $layout_content_size ) ||
			( ! empty( $layout_wide_size ) && '100%' === $layout_wide_size )
		);

		// If not full width, enqueue a custom CSS to make it full width.
		if ( ! $is_full_width ) {
			wp_enqueue_style(
				'gutenverse-overwrite-layout',
				GUTENVERSE_COMPANION_URL . '/assets/css/companion-overwrite-layout.css',
				array(),
				GUTENVERSE_COMPANION_VERSION
			);
		}
	}

	/**
	 * Overwrite layout max width theme json (Remove after designer make it all theme to 100%)
	 *
	 * @param object $theme_json_data .
	 * @return object
	 */
	public function layout_max_width_theme_json( $theme_json_data ) {

		$theme_json = $theme_json_data->get_data();
		if ( is_object( $theme_json ) && method_exists( $theme_json, 'get_raw_data' ) ) {
			$data = $theme_json->get_raw_data();
		} else {
			$data = $theme_json;
		}

		if ( ! isset( $data['settings']['layout'] ) ) {
			$data['settings']['layout'] = array();
		}

		$data['settings']['layout']['contentSize'] = '100%';
		$data['settings']['layout']['wideSize']    = '100%';

		if ( ! isset( $data['styles'] ) ) {
			$data['styles'] = array();
		}
		$data['styles']['layout'] = array(
			'contentSize' => '100%',
			'wideSize'    => '100%',
		);
		$theme_json_data->update_with( $data );
		return $theme_json_data;
	}

	/**
	 * Enqueue Scripts.
	 */
	public function enqueue_scripts() {
		if ( is_admin() ) {
			$include = array_values(
				array_unique(
					array_merge(
						( include GUTENVERSE_COMPANION_DIR . '/lib/dependencies/gutenverse-theme-wizard.asset.php' )['dependencies'],
						array( 'wp-api-fetch' )
					)
				)
			);
			wp_enqueue_script( 'wp-api-fetch' );

			wp_enqueue_style(
				'gutenverse-companion-gutenverse-theme-wizard',
				GUTENVERSE_COMPANION_URL . '/assets/css/gutenverse-theme-wizard.css',
				array(),
				GUTENVERSE_COMPANION_VERSION
			);

			wp_enqueue_script(
				'gutenverse-companion-gutenverse-theme-wizard',
				GUTENVERSE_COMPANION_URL . '/assets/js/gutenverse-theme-wizard.js',
				$include,
				GUTENVERSE_COMPANION_VERSION,
				true
			);
			if ( ! $this->gutenverse_check_if_script_localized( 'GutenverseCompanionConfig' ) ) {
				$companion_dashboard = new Dashboard();
				$config              = $companion_dashboard->companion_config();
				wp_localize_script( 'gutenverse-companion-gutenverse-theme-wizard', 'GutenverseCompanionConfig', $config );
			}
		}
		wp_enqueue_style(
			'gutenverse-companion-dashboard-inter-font',
			GUTENVERSE_COMPANION_URL . '/assets/dashboard-fonts/inter/inter.css',
			array(),
			GUTENVERSE_COMPANION_VERSION
		);

		wp_enqueue_style(
			'gutenverse-companion-dashboard-jakarta-sans-font',
			GUTENVERSE_COMPANION_URL . '/assets/dashboard-fonts/plus-jakarta-sans/plus-jakarta-sans.css',
			array(),
			GUTENVERSE_COMPANION_VERSION
		);
	}

	/**
	 * Check if script localized
	 */
	public function gutenverse_check_if_script_localized( $handle ) {
		global $wp_scripts;

		if ( ! is_a( $wp_scripts, 'WP_Scripts' ) ) {
			return false;
		}

		if ( isset( $wp_scripts->registered[ $handle ] ) ) {
			$script = $wp_scripts->registered[ $handle ];
			if ( ! empty( $script->extra['data'] ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Wizard Menu.
	 */
	public function theme_wizard() {
		add_theme_page(
			get_option( $this->theme_slug . '_wizard_setup_done' ) !== 'yes' ? 'Wizard Setup' : '',
			'Wizard Setup',
			'manage_options',
			'theme-wizard',
			array( $this, 'theme_wizard_page' ),
			99
		);
	}

	/**
	 * Wizard Page.
	 */
	public function theme_wizard_page() {
		?>
		<div id="gutenverse-theme-wizard"></div>
		<?php
	}

	/**
	 * Add Menu
	 */
	public function admin_menu() {
		$theme = wp_get_theme();
		$title = $theme->get( 'Name' );
		$slug  = $theme->get_template();
		add_theme_page(
			$title . ' Dashboard',
			$title . ' Dashboard',
			'manage_options',
			$slug . '-dashboard',
			array( $this, 'load_dashboard' ),
			1
		);
	}

	/**
	 * Template page
	 */
	public function load_dashboard() {
		?>
			<div id="gutenverse-theme-dashboard">
			</div>
		<?php
	}

	/**
	 * Check parameter.
	 */
	private function is_wizard_done() {
		return isset( $_GET['page'] ) && isset( $_GET['wizard_setup_done'] ) && $_GET['page'] === $this->theme_slug . '-dashboard' && $_GET['wizard_setup_done'] === 'yes';
	}

	/**
	 * Theme Redirect.
	 */
	public function theme_redirect() {
		if ( ! apply_filters( 'gutenverse_companion_base_theme', false ) ) {
			if ( $this->is_wizard_done() ) {
				update_option( $this->theme_slug . '_wizard_setup_done', 'yes' );
				wp_safe_redirect( admin_url( 'themes.php?page=' . $this->theme_slug . '-dashboard' ) );
			}

			if ( get_option( $this->theme_slug . '_wizard_init_done' ) !== 'yes' ) {

				update_option( $this->theme_slug . '_wizard_init_done', 'yes' );
				wp_safe_redirect( admin_url( 'admin.php?page=theme-wizard' ) );
				exit;
			}
		}
	}

	/**
	 * Register APIs
	 */
	public function register_routes() {
		if ( ! is_admin() && ! current_user_can( 'manage_options' ) ) {
			return;
		}

		/**
		 * Backend routes.
		 */

		// Themes.
		register_rest_route(
			self::ENDPOINT,
			'pages/assign',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'handle_pages' ),
				'permission_callback' => function () {
					if ( ! current_user_can( 'manage_options' ) ) {
						return new WP_Error(
							'forbidden_permission',
							esc_html__( 'Forbidden Access', 'gutenverse-companion' ),
							array( 'status' => 403 )
						);
					}

					return true;
				},
			)
		);

		register_rest_route(
			self::ENDPOINT,
			'import/menus',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'handle_menus' ),
				'permission_callback' => function () {
					if ( ! current_user_can( 'manage_options' ) ) {
						return new WP_Error(
							'forbidden_permission',
							esc_html__( 'Forbidden Access', 'gutenverse-companion' ),
							array( 'status' => 403 )
						);
					}

					return true;
				},
			)
		);

		register_rest_route(
			self::ENDPOINT,
			'install/plugins',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'install_plugin' ),
				'permission_callback' => function () {
					if ( ! current_user_can( 'manage_options' ) ) {
						return new WP_Error(
							'forbidden_permission',
							esc_html__( 'Forbidden Access', 'gutenverse-companion' ),
							array( 'status' => 403 )
						);
					}

					return true;
				},
			)
		);
	}

	/**
	 * Create pages and assign templates.
	 *
	 * @param object $request .
	 *
	 * @return int|string
	 */
	public function handle_pages( $request ) {
		global $wp_filesystem;
		require_once ABSPATH . 'wp-admin/includes/file.php';
		WP_Filesystem();
		$title           = $request->get_param( 'title' );
		$page_slug_title = str_replace( ' ', '_', strtolower( $title ) );
		$theme_dir       = get_template_directory();
		$json_file_data  = $wp_filesystem->get_contents( $theme_dir . '/gutenverse-pages/' . $page_slug_title . '.json' );
		$page            = json_decode( $json_file_data, true );
		$theme_url       = get_template_directory_uri();
		if ( $page ) {
			$content = str_replace( '{{home_url}}', $theme_url, $page['content'] );
			$content = str_replace( "\'", "'", $content );
			$page_id = null;

			if ( ! empty( $page['core-patterns'] ) ) {
				$this->import_synced_patterns( $page['core-patterns'] );
			}

			if ( ! empty( $page['pro-patterns'] ) ) {
				$this->import_synced_patterns( $page['pro-patterns'] );
			}

			if ( ! empty( $page['gutenverse-patterns'] ) ) {
				$this->import_synced_patterns( $page['gutenverse-patterns'] );
			}

			/**Download Image */
			if ( $page['image_arr'] ) {
				$images  = json_decode( str_replace( "'", '"', $page['image_arr'] ) );
				$content = wp_slash( $content );

				foreach ( $images as $key => $image ) {
					$url  = $image->image_url;
					$data = Helper::check_image_exist( $url );
					if ( ! $data ) {
						$data = Helper::handle_file( $url );
					}
					$content  = str_replace( $url, $data['url'], $content );
					$image_id = $image->image_id;
					if ( $image_id && 'null' !== $image_id ) {
						$content = str_replace( '"imageId\":' . $image_id, '"imageId\":' . $data['id'], $content );
					}
				}
			}

			$query = new \WP_Query(
				array(
					'post_type'      => 'page',
					'post_status'    => 'publish',
					'title'          => $page['pagetitle'],
					'posts_per_page' => 1,
				)
			);

			if ( $query->have_posts() ) {
				$existing_page = $query->posts[0];
				$page_id       = $existing_page->ID;
				wp_update_post(
					array(
						'ID'            => $existing_page->ID,
						'page_template' => $page['template'],
					)
				);
			} else {
				$new_page = array(
					'post_title'    => $page['pagetitle'],
					'post_content'  => $content,
					'post_status'   => 'publish',
					'post_type'     => 'page',
					'page_template' => $page['template'],
				);
				$page_id  = wp_insert_post( $new_page );
			}

			if ( $page['is_homepage'] && $page_id ) {
				update_option( 'show_on_front', 'page' );
				update_option( 'page_on_front', $page_id );
			}
		}

		return true;
	}

	/**
	 * Create menus.
	 *
	 * @return int|string
	 */
	public function handle_menus() {
		global $wp_filesystem;
		require_once ABSPATH . 'wp-admin/includes/file.php';
		WP_Filesystem();
		$theme_dir = get_template_directory();
		$menus     = (object) json_decode( $wp_filesystem->get_contents( $theme_dir . '/assets/misc/menu.json' ) );
		foreach ( $menus as $key => $menu ) {
			$menu_name   = get_option( 'stylesheet' ) . ' ' . intval( $key ) + 1;
			$menu_exists = wp_get_nav_menu_object( $menu_name );
			if ( ! $menu_exists ) {
				$menu_id = wp_create_nav_menu( $menu_name );
			} else {
				$menu_id = $menu_exists->term_id;
			}

			$parent_id = array();
			foreach ( $menu->menu_data as $idx => $data ) {
				$menu_parent = 0;
				$url         = $data->url;
				if ( null !== $data->parent ) {
					foreach ( $parent_id as $pr_id ) {
						if ( strval( $pr_id['idx'] ) === strval( $data->parent ) ) {
							$menu_parent = $pr_id['menu_id'];
						}
					}
				}
				if ( $data->object_slug && ( 'page' === $data->type ) ) {
					$args = array(
						'name'        => $data->object_slug,
						'post_type'   => 'page',
						'post_status' => 'publish',
						'numberposts' => 1,
					);

					$query = new WP_Query( $args );

					if ( $query->have_posts() ) {
						$page = $query->posts[0]; // Get the first result.
					} else {
						$page = null; // No page found.
					}

					wp_reset_postdata();
					if ( $page ) {
						$url = get_permalink( $page->ID );
					}
				}

				$menu_items   = wp_get_nav_menu_items( $menu_id );
				$menu_item_id = 0;
				if ( $menu_items ) {
					foreach ( $menu_items as $menu_item ) {
						if ( $menu_item->title === $data->title ) {
							$menu_item_id = $menu_item->ID;
						}
					}
				}
				$menu_item_id = wp_update_nav_menu_item(
					$menu_id,
					$menu_item_id,
					array(
						'menu-item-title'     => $data->title,
						'menu-item-url'       => $url,
						'menu-item-status'    => 'publish',
						'menu-item-parent-id' => $menu_parent,
					)
				);
				if ( $data->have_child ) {
					$parent_id[] = array(
						'idx'     => $idx,
						'menu_id' => $menu_item_id,
					);
				}
			}
		}
		return true;
	}

	/**
	 * Download plugin file
	 *
	 * @param string $url .
	 */
	public function download_plugin_file( $url ) {
		$url = esc_url_raw( $url );
		if ( ! filter_var( $url, FILTER_VALIDATE_URL ) ) {
			return false;
		}
		$temp_file = download_url( $url );
		if ( is_wp_error( $temp_file ) ) {
			return false;
		}
		return $temp_file;
	}

	/**
	 * Create Synced Pattern
	 *
	 * @param WP_REST_Request $request Request Object.
	 */
	public function install_plugin( $request ) {
		$download_url = $request->get_param( 'download_url' );

		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
		require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

		WP_Filesystem();
		global $wp_filesystem;

		$temp_file = $this->download_plugin_file( $download_url );
		if ( ! $temp_file ) {
			return array(
				'status'  => 'failed',
				'message' => 'Failed to download the plugin',
			);
		}

		$plugin_dir = WP_PLUGIN_DIR;
		$unzip_file = unzip_file( $temp_file, $plugin_dir );

		if ( is_wp_error( $unzip_file ) ) {
			return array(
				'status'  => 'failed',
				'message' => 'Failed to unzip the plugin',
			);
		}

		unlink( $temp_file );
		return array(
			'status'  => 'success',
			'message' => 'Plugin installed successfully!',
		);
	}

	/**
	 * Create Synced Pattern
	 *
	 * @param array $patterns .
	 */
	public function import_synced_patterns( $patterns ) {
		$pattern_list = get_option( $this->theme_slug . '_synced_pattern_imported', false );
		if ( ! $pattern_list ) {
			$pattern_list = array();
		}

		foreach ( $patterns as $block_pattern ) {
			$pattern_file = get_theme_file_path( '/inc/patterns/' . $block_pattern . '.php' );
			$pattern_data = require $pattern_file;

			if ( (bool) $pattern_data['is_sync'] ) {
				$post = get_page_by_path( $block_pattern . '-synced', OBJECT, 'wp_block' );

				/**Download Image */
				$content = wp_slash( $pattern_data['content'] );
				if ( $pattern_data['images'] ) {
					$images = json_decode( $pattern_data['images'] );
					foreach ( $images as $key => $image ) {
						$url  = $image->image_url;
						$data = Helper::check_image_exist( $url );
						if ( ! $data ) {
							$data = Helper::handle_file( $url );
						}
						$content  = str_replace( $url, $data['url'], $content );
						$image_id = $image->image_id;
						if ( $image_id && 'null' !== $image_id ) {
							$content = str_replace( '"imageId\":' . $image_id, '"imageId\":' . $data['id'], $content );
						}
					}
				}
				if ( empty( $post ) ) {
					$post_id = wp_insert_post(
						array(
							'post_name'    => $block_pattern . '-synced',
							'post_title'   => $pattern_data['title'],
							'post_content' => $content,
							'post_status'  => 'publish',
							'post_author'  => 1,
							'post_type'    => 'wp_block',
						)
					);
					if ( ! is_wp_error( $post_id ) ) {
						$pattern_category = $pattern_data['categories'];
						foreach ( $pattern_category as $category ) {
							wp_set_object_terms( $post_id, $category, 'wp_pattern_category' );
						}
					}

					$pattern_data['content']  = '<!-- wp:block {"ref":' . $post_id . '} /-->';
					$pattern_data['inserter'] = false;
					$pattern_data['slug']     = $block_pattern;

					$pattern_list[] = $pattern_data;
				}
			}
		}

		update_option( $this->theme_slug . '_synced_pattern_imported', $pattern_list );
	}
}