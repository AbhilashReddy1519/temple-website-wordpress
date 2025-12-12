<?php
/**
 * Dashboard class
 *
 * @author Jegstudio
 * @since 1.0.0
 * @package gutenverse-companion
 */

namespace Gutenverse_Companion;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Dashboard
 *
 * @package gutenverse-companion
 */
class Dashboard {
	/**
	 * Type
	 *
	 * @var string
	 */
	const TYPE = 'gutenverse-companion';

	/**
	 * Id
	 *
	 * @var id
	 */
	public $id;

	/**
	 * Init constructor.
	 */
	public function __construct() {
		$this->id = 'tabbed-template';
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ), 99 );
		add_action( 'enqueue_block_editor_assets', array( $this, 'open_global_sidebar_script' ), 10 );
		if ( apply_filters( 'gutenverse_companion_base_theme', false ) ) {
			add_action( 'admin_menu', array( $this, 'parent_menu' ) );
			add_action( 'admin_menu', array( $this, 'child_menu' ) );
			add_filter( 'submenu_file', array( $this, 'highlight_submenu_item' ) );
			add_filter(
				'admin_title',
				function ( $admin_title, $title ) {
					$theme = wp_get_theme();
					if ( isset( $_GET['page'] ) && 'gutenverse-companion-wizard' === $_GET['page'] ) {
						return $theme->get( 'Name' ) . ' Wizard';
					}
					return $admin_title;
				},
				10,
				2
			);
			add_action( 'admin_init', array( $this, 'companion_redirect' ), 99 );
		}
	}

	/**
	 * Open Global Sidebar Script
	 */
	public function open_global_sidebar_script() {
		wp_enqueue_script( 'wp-edit-post' );

		$js = "
			wp.domReady(() => {
				const urlParams = new URLSearchParams(window.location.search);
				if (urlParams.get('gutenverse-global-sidebar') === 'open') {
					wp.data.dispatch('core/edit-post').openGeneralSidebar('gutenverse-global-style/gutenverse-sidebar');
				}
			});
    	";
		wp_add_inline_script( 'wp-edit-post', $js );
	}

	/**
	 * Theme redirect
	 */
	public function companion_redirect() {
		if ( get_option( 'gutenverse-companion_wizard_init_done' ) !== 'yes' ) {
			update_option( 'gutenverse-companion_wizard_init_done', 'yes' );
			wp_safe_redirect( admin_url( 'admin.php?page=gutenverse-companion-wizard' ) );
			exit;
		}
	}

	/**
	 * Enqueue scripts
	 */
	public function enqueue_scripts() {
		global $current_screen;

		if ( $current_screen->is_block_editor ) {
			return;
		}

		$include = ( include GUTENVERSE_COMPANION_DIR . '/lib/dependencies/companion.asset.php' )['dependencies'];

		wp_enqueue_style(
			'gutenverse-companion-dashboard',
			GUTENVERSE_COMPANION_URL . '/assets/css/companion.css',
			array(),
			GUTENVERSE_COMPANION_VERSION
		);

		wp_enqueue_script(
			'gutenverse-companion-dashboard',
			GUTENVERSE_COMPANION_URL . '/assets/js/companion.js',
			$include,
			GUTENVERSE_COMPANION_VERSION,
			true
		);

		wp_localize_script(
			'gutenverse-companion-dashboard',
			'GutenverseRootConfig',
			$this->companion_config()
		);

		wp_enqueue_style(
			'gutenverse-companion-google-fonts',
			'https://fonts.googleapis.com/css?family=Inter:400,500,600|Roboto:400,500,700&display=swap',
			false,
			1
		);
		wp_enqueue_style(
			'gutenverse-companion-google-fonts-host-grostesk',
			'https://fonts.googleapis.com/css2?family=Host+Grotesk:ital,wght@0,300..800;1,300..800&display=swap',
			false,
			1
		);

		wp_localize_script( 'gutenverse-companion-dashboard', 'GutenverseCompanionConfig', $this->companion_config() );
	}

	/**
	 * Account config.
	 */
	public function companion_config() {
		global $pagenow;

		$config = array(
			'home_url'       => home_url(),
			'dashboard'      => admin_url( 'admin.php?page=gutenverse-companion-dashboard' ),
			'admin_url'      => admin_url(),
			'plugins_url'    => plugins_url(),
			'images'         => GUTENVERSE_COMPANION_URL . '/assets/img',
			'upgradePro'     => GUTENVERSE_COMPANION_LIBRARY_URL . '/pricing',
			'doc_url'        => GUTENVERSE_COMPANION_LIBRARY_URL . '/docs',
			'libraryApi'     => GUTENVERSE_COMPANION_LIBRARY_URL . '/wp-json/gutenverse-server/v1',
			'theme_slug'     => wp_get_theme()->get_template(),
			'version'        => GUTENVERSE_COMPANION_VERSION,
			'editor_url'     => admin_url() . 'site-editor.php?p=%2F&canvas=edit',
			'nonce'          => wp_create_nonce( 'wp_rest' ),
			'demoLibraryUrl' => GUTENVERSE_COMPANION_LIBRARY_URL,

		);
		$theme                = wp_get_theme();
		$config['theme_name'] = $theme->get( 'Name' );
		$config['plugins']    = self::list_plugin();

		if ( 'admin.php' === $pagenow && isset( $_GET['page'] ) && 'gutenverse-companion-dashboard' === $_GET['page'] ) {
			$config['system'] = $this->system_status();
		}

		return $config;
	}

	/**
	 * System Status.
	 *
	 * @return array
	 */
	public function system_status() {
		$status      = array();
		$active_demo = get_option( 'gutenverse_companion_template_options' );
		/** Themes */
		$theme                    = wp_get_theme();
		$parent                   = wp_get_theme( get_template() );
		$status['theme_name']     = $theme->get( 'Name' );
		$status['theme_version']  = $theme->get( 'Version' );
		$status['is_child_theme'] = is_child_theme();
		$status['parent_theme']   = $parent->get( 'Name' );
		$status['parent_version'] = $parent->get( 'Version' );

		$status['active_companion_demo'] = $active_demo['active_demo'] ?? 'You don\'t have any demo activated';

		/** WordPress Environment */
		$wp_upload_dir              = wp_upload_dir();
		$status['home_url']         = home_url( '/' );
		$status['site_url']         = site_url();
		$status['login_url']        = wp_login_url();
		$status['wp_version']       = get_bloginfo( 'version', 'display' );
		$status['is_multisite']     = is_multisite();
		$status['wp_debug']         = defined( 'WP_DEBUG' ) && WP_DEBUG;
		$status['memory_limit']     = ini_get( 'memory_limit' );
		$status['wp_memory_limit']  = WP_MEMORY_LIMIT;
		$status['wp_language']      = get_locale();
		$status['writeable_upload'] = wp_is_writable( $wp_upload_dir['basedir'] );
		$status['count_category']   = wp_count_terms( 'category' );
		$status['count_tag']        = wp_count_terms( 'post_tag' );

		/** Server Environment */
		$remote = get_transient( 'gutenverse_wp_remote_get_status_cache' );
		if ( ! $remote ) {
			$remote = wp_remote_get( home_url() );
			set_transient( 'gutenverse_wp_remote_get_status_cache', $remote, 30 * MINUTE_IN_SECONDS );
		}

		$gd_support = array();
		if ( function_exists( 'gd_info' ) ) {
			foreach ( gd_info() as $key => $value ) {
				$gd_support[ $key ] = $value;
			}
		}

		$status['server_info']        = isset( $_SERVER['SERVER_SOFTWARE'] ) ? sanitize_text_field( wp_unslash( $_SERVER['SERVER_SOFTWARE'] ) ) : '';
		$status['php_version']        = PHP_VERSION;
		$status['post_max_size']      = ini_get( 'post_max_size' );
		$status['max_input_vars']     = ini_get( 'max_input_vars' );
		$status['max_execution_time'] = ini_get( 'max_execution_time' );
		$status['suhosin']            = extension_loaded( 'suhosin' );
		$status['imagick']            = extension_loaded( 'imagick' );
		$status['gd']                 = extension_loaded( 'gd' ) && function_exists( 'gd_info' );
		$status['gd_webp']            = extension_loaded( 'gd' ) && $gd_support['WebP Support'];
		$status['fileinfo']           = extension_loaded( 'fileinfo' ) && ( function_exists( 'finfo_open' ) || function_exists( 'mime_content_type' ) );
		$status['curl']               = extension_loaded( 'curl' ) && function_exists( 'curl_version' );
		$status['wp_remote_get']      = ! is_wp_error( $remote ) && $remote['response']['code'] >= 200 && $remote['response']['code'] < 300;

		/** Plugins */
		$status['plugins'] = $this->data_active_plugin();

		return $status;
	}

	/**
	 * Data active plugin
	 *
	 * @return array
	 */
	public function data_active_plugin() {
		$active_plugin = array();

		$plugins = array_merge(
			array_flip( (array) get_option( 'active_plugins', array() ) ),
			(array) get_site_option( 'active_sitewide_plugins', array() )
		);

		$plugins = array_intersect_key( get_plugins(), $plugins );

		if ( count( $plugins ) > 0 ) {
			foreach ( $plugins as $plugin ) {
				$item                = array();
				$item['uri']         = isset( $plugin['PluginURI'] ) ? esc_url( $plugin['PluginURI'] ) : '#';
				$item['name']        = isset( $plugin['Name'] ) ? $plugin['Name'] : esc_html__( 'unknown', '--gctd--' );
				$item['author_uri']  = isset( $plugin['AuthorURI'] ) ? esc_url( $plugin['AuthorURI'] ) : '#';
				$item['author_name'] = isset( $plugin['Author'] ) ? $plugin['Author'] : esc_html__( 'unknown', '--gctd--' );
				$item['version']     = isset( $plugin['Version'] ) ? $plugin['Version'] : esc_html__( 'unknown', '--gctd--' );

				$content = esc_html__( 'by', '--gctd--' );

				$active_plugin[] = array(
					'type'            => 'status',
					'title'           => $item['name'],
					'content'         => $content,
					'link'            => $item['author_uri'],
					'link_text'       => $item['author_name'],
					'additional_text' => $item['version'],
				);
			}
		}

		return $active_plugin;
	}

	/**
	 * Get List Of Installed Plugin.
	 *
	 * @return array
	 */
	public static function list_plugin() {
		$plugins = array();
		$active  = array();

		foreach ( get_option( 'active_plugins' ) as  $plugin ) {
			$active[] = explode( '/', $plugin )[0];
		}

		foreach ( get_plugins() as $key => $plugin ) {
			$slug             = explode( '/', $key )[0];
			$data             = array();
			$data['active']   = in_array( $slug, $active, true );
			$data['version']  = $plugin['Version'];
			$data['name']     = $plugin['Name'];
			$data['path']     = str_replace( '.php', '', $key );
			$plugins[ $slug ] = $data;
		}

		return $plugins;
	}

	/**
	 * Gutenverse Dashboard Config
	 *
	 * @return array
	 */
	public function gutenverse_companion_dashboard_config() {
		$config = array();

		return apply_filters( 'gutenverse_companion_dashboard_config', $config );
	}

	/**
	 * Parent Menu
	 */
	public function parent_menu() {
		$theme = wp_get_theme();
		add_menu_page(
			$theme->name,
			$theme->name,
			'manage_options',
			self::TYPE . '-dashboard',
			null,
			apply_filters( 'gutenverse_companion_menu_icon', GUTENVERSE_COMPANION_URL . '/assets/img/icon-companion-dashboard.svg' ),
			30
		);
	}


	/**
	 * Child Menu
	 */
	public function child_menu() {
		$theme = wp_get_theme();
		$path  = admin_url( 'admin.php?page=gutenverse-companion-dashboard&path=' );

		add_submenu_page(
			self::TYPE . '-dashboard',
			esc_html__( 'Dashboard', 'gutenverse-companion' ),
			esc_html__( 'Dashboard', 'gutenverse-companion' ),
			'manage_options',
			self::TYPE . '-dashboard',
			array( $this, 'load_companion_dashboard' ),
			1
		);

		add_submenu_page(
			self::TYPE . '-dashboard',
			esc_html__( 'Demo', 'gutenverse-companion' ),
			esc_html__( 'Demo', 'gutenverse-companion' ),
			'manage_options',
			$path . 'demo',
			null,
			2
		);

		add_submenu_page(
			self::TYPE . '-dashboard',
			esc_html__( 'Settings', 'gutenverse-companion' ),
			esc_html__( 'Settings', 'gutenverse-companion' ),
			'manage_options',
			$path . 'settings',
			null,
			3
		);

		add_submenu_page(
			self::TYPE . '-dashboard',
			esc_html__( 'System Status', 'gutenverse-companion' ),
			esc_html__( 'System Status', 'gutenverse-companion' ),
			'manage_options',
			$path . 'system-status',
			null,
			4
		);

		add_submenu_page(
			self::TYPE . '-dashboard',
			esc_html__( 'Need Help?', 'gutenverse-companion' ),
			esc_html__( 'Need Help?', 'gutenverse-companion' ),
			'manage_options',
			'https://wordpress.org/support/theme/' . $theme->get_stylesheet(),
			null,
			5
		);

		add_submenu_page(
			'', // <== Set to null to hide from sidebar
			$theme->name . ' Wizard',
			$theme->name . ' Wizard',
			'manage_options',
			self::TYPE . '-wizard',
			array( $this, 'load_companion_wizard' )
		);
	}

	public function highlight_submenu_item( $submenu_file ) {
		$current_page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '';
		$current_path = isset( $_GET['path'] ) ? sanitize_text_field( wp_unslash( $_GET['path'] ) ) : '';

		$dashboard_slug = self::TYPE . '-dashboard';

		if ( $current_page === $dashboard_slug ) {

			$path_prefix = admin_url( 'admin.php?page=' . $dashboard_slug . '&path=' );

			switch ( $current_path ) {
				case 'demo':
					$submenu_file = $path_prefix . 'demo';
					break;
				case 'settings':
					$submenu_file = $path_prefix . 'settings';
					break;
				case 'system-status':
					$submenu_file = $path_prefix . 'system-status';
					break;
			}
		}

		return $submenu_file;
	}

	/**
	 * Load Companion Wizard
	 */
	public function load_companion_wizard() {
		?>
			<div id="gutenverse-companion-wizard">
			</div>
		<?php
	}

	/**
	 * Load Companion Dashboard
	 */
	public function load_companion_dashboard() {
		?>
		<div id="gutenverse-companion-dashboard">
		</div>
		<?php
	}
}
