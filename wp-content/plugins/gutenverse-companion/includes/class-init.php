<?php
/**
 * Gutenverse Companion Main class
 *
 * @author Jegstudio
 * @since 1.0.0
 * @package gutenverse
 */

namespace Gutenverse_Companion;

use Gutenverse_Companion\Essential\Init as EssentialInit;
use Gutenverse_Companion\Gutenverse_Theme\Gutenverse_Theme;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Init
 *
 * @package gutenverse-companion
 */
class Init {
	/**
	 * Instance of Init.
	 *
	 * @var Init
	 */
	private static $instance;

	/**
	 * Hold instance of dashboard
	 *
	 * @var Dashboard
	 */
	public $dashboard;

	/**
	 * Hold instance of essential
	 *
	 * @var Essential
	 */
	public $essential;

	/**
	 * Hold instance of gutenverse theme
	 *
	 * @var GutenverseTheme
	 */
	public $gutenverse_theme;

	/**
	 * Hold instance of wizard
	 *
	 * @var Wizard
	 */
	public $wizard;

	/**
	 * Hold API Variable Instance.
	 *
	 * @var Api
	 */
	public $api;

	/**
	 * Singleton page for Init Class
	 *
	 * @return Gutenverse
	 */
	public static function instance() {
		if ( null === static::$instance ) {
			static::$instance = new static();
		}

		return static::$instance;
	}

	/**
	 * Init constructor.
	 */
	private function __construct() {
		add_action( 'rest_api_init', array( $this, 'init_api' ) );
		add_action( 'after_setup_theme', array( $this, 'plugin_loaded' ) );
		add_action( 'init', array( $this, 'register_block_patterns' ), 9 );
		add_action( 'init', array( $this, 'activating_gutenverse_theme_dashboard' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_global_scripts' ), 99 );
		add_action( 'wp_enqueue_scripts', array( $this, 'remove_enqueue_default_style' ), 21 );
	}

	/**
	 * Remove Default Style when import default demo on Unibiz Theme
	 */
	public function remove_enqueue_default_style() {
		$theme        = wp_get_theme(); // omit slug to get current theme
		$demo_options = get_option( 'gutenverse-companion-imported-options', false );
		if ( $demo_options && isset( $demo_options['demo_id'] ) && 'default' !== $demo_options['demo_id'] && 'Unibiz' === $theme->get( 'Name' ) ) {
			wp_dequeue_style( 'unibiz-style' );
			wp_dequeue_style( 'preset' );
		}
	}
	/**
	 * Enqueue Global Script
	 */
	public function enqueue_global_scripts() {
		$include = ( include GUTENVERSE_COMPANION_DIR . '/lib/dependencies/notices.asset.php' )['dependencies'];

		wp_enqueue_script(
			'gutenverse-companion-notices',
			GUTENVERSE_COMPANION_URL . '/assets/js/notices.js',
			$include,
			GUTENVERSE_COMPANION_VERSION,
			true
		);
	}

	/**
	 * Activating Gutenverse Theme Dashboard
	 */
	public function activating_gutenverse_theme_dashboard() {
		if ( defined( 'GUTENVERSE_COMPANION_REQUIRED_VERSION' ) ) {
			$active_plugins = get_option( 'active_plugins' );
			$companion_ver  = null;
			include_once ABSPATH . 'wp-admin/includes/plugin.php';
			foreach ( $active_plugins as $plugin_path ) {
				$slug = dirname( $plugin_path );
				if ( 'gutenverse-companion' === $slug ) {
					$plugin_data   = get_plugin_data( WP_PLUGIN_DIR . '/' . $plugin_path );
					$companion_ver = $plugin_data['Version'];
				}
			}
			if ( isset( $companion_ver ) && version_compare( $companion_ver, GUTENVERSE_COMPANION_REQUIRED_VERSION, '>=' ) && ! apply_filters( 'gutenverse_companion_base_theme', false ) ) {
				$this->gutenverse_theme = new Gutenverse_Theme();
			}
		}
	}

	/**
	 * Register Block Patterns.
	 */
	public function register_block_patterns() {
		$companion_data = get_option( 'gutenverse_companion_template_options', false );
		if ( ! isset( $companion_data['active_demo'] ) ) {
			return;
		}
		$slug         = strtolower( str_replace( ' ', '-', $companion_data['active_demo'] ) );
		$pattern_list = get_option( $slug . '_' . get_stylesheet() . '_companion_synced_pattern_imported', false );
		if ( ! $pattern_list ) {
			return;
		}
		foreach ( $pattern_list as $block_pattern ) {
			register_block_pattern(
				$block_pattern['slug'],
				$block_pattern
			);
		}
	}

	/**
	 * Change Stylesheet Directory.
	 *
	 * @return string
	 */
	public function change_stylesheet_directory( $def ) {
		return isset( get_option( 'gutenverse_companion_template_options' )['template_dir'] ) ? get_option( 'gutenverse_companion_template_options' )['template_dir'] : $def;
	}

	/**
	 * Enable Override Stylesheet Directory.
	 *
	 * @return mixed
	 */
	public function is_change_stylesheet_directory() {
		return (bool) get_option( 'gutenverse_companion_template_options' ) && isset( get_option( 'gutenverse_companion_template_options' )['active_theme'] ) && get_option( 'gutenverse_companion_template_options' )['active_theme'] === wp_get_theme()->get_template();
	}

	/**
	 * Plugin Loaded.
	 */
	public function plugin_loaded() {
		if ( apply_filters( 'jeg_theme_essential_mode_on', false ) || apply_filters( 'gutenverse_companion_essential_mode_on', false ) ) {
			$this->essential = new EssentialInit();
		} else {
			global $wp_version;
			if ( version_compare( $wp_version, '6.5', '>=' ) ) {
				add_filter( 'gutenverse_themes_override_mechanism', array( $this, 'is_change_stylesheet_directory' ) );
			} else {
				add_filter( 'gutenverse_template_path', array( $this, 'template_path' ), null, 3 );
				add_filter( 'gutenverse_themes_template', array( $this, 'add_template' ), 10, 2 );
				add_filter( 'gutenverse_themes_override_mechanism', '__return_false', 20 );
			}
			add_filter( 'gutenverse_stylesheet_directory', array( $this, 'change_stylesheet_directory' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'dashboard_enqueue_scripts' ) );
			add_action( 'wp_ajax_gutenverse_companion_notice_close', array( $this, 'companion_notice_close' ) );
			add_action( 'admin_notices', array( $this, 'notice_unibiz' ), 11 );
			$this->dashboard = new Dashboard();
		}
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
		$dir = isset( get_option( 'gutenverse_companion_template_options' )['template_dir'] ) ? get_option( 'gutenverse_companion_template_options' )['template_dir'] : false;

		if ( ! $dir ) {
			return $template_files;
		}
		$template_files = array();
		if ( 'wp_template' === $template_type ) {
			$demo_template_path  = trailingslashit( $dir ) . 'templates/';
			$demo_template_files = glob( $demo_template_path . '*.html' );
			if ( $demo_template_files ) {
				foreach ( $demo_template_files as $file ) {
					$slug             = pathinfo( $file, PATHINFO_FILENAME );
					$template_files[] = array(
						'slug'  => $slug,
						'path'  => $file,
						'theme' => get_template(),
						'type'  => 'wp_template',
					);
				}
			}
		}

		if ( 'wp_template_part' === $template_type ) {
			$demo_part_path  = trailingslashit( $dir ) . 'parts/';
			$demo_part_files = glob( $demo_part_path . '*.html' );
			if ( $demo_part_files ) {
				foreach ( $demo_part_files as $file ) {
					$slug             = pathinfo( $file, PATHINFO_FILENAME );
					$template_files[] = array(
						'slug'  => $slug,
						'path'  => $file,
						'theme' => get_template(),
						'type'  => 'wp_template_part',
					);
				}
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
		$dir = isset( get_option( 'gutenverse_companion_template_options' )['template_dir'] ) ? get_option( 'gutenverse_companion_template_options' )['template_dir'] : false;

		if ( ! $dir ) {
			return $template_file;
		}

		$template_file = $this->get_template_path( $template_slug );

		return $template_file;
	}

	public function get_template_path( $template_slug ) {

		$dir = isset( get_option( 'gutenverse_companion_template_options' )['template_dir'] ) ? get_option( 'gutenverse_companion_template_options' )['template_dir'] : false;

		if ( ! $dir ) {
			return false;
		}

		$demo_template_path  = trailingslashit( $dir ) . 'templates/';
		$demo_template_files = glob( $demo_template_path . '*.html' );

		if ( $demo_template_files ) {
			foreach ( $demo_template_files as $file ) {
				$slug = pathinfo( $file, PATHINFO_FILENAME );
				if ( $template_slug === $slug ) {
					return $file;
				}
			}
		}

		$demo_part_path  = trailingslashit( $dir ) . 'parts/';
		$demo_part_files = glob( $demo_part_path . '*.html' );

		if ( $demo_part_files ) {
			foreach ( $demo_part_files as $file ) {
				$slug = pathinfo( $file, PATHINFO_FILENAME );
				if ( $template_slug === $slug ) {
					return $file;
				}
			}
		}
	}

	/**
	 * Init Rest API
	 */
	public function init_api() {
		$this->api = Api::instance();
	}

	/**
	 * Dashboard scripts.
	 */
	public function dashboard_enqueue_scripts() {
		if ( current_user_can( 'manage_options' ) && ! get_option( 'gutenverse-companion-base-theme-notice' ) ) {
			wp_enqueue_script(
				'notice-script',
				GUTENVERSE_COMPANION_URL . '/assets/admin/js/notice.js',
				array(),
				GUTENVERSE_COMPANION_NOTICE_VERSION,
				true
			);
		}
	}

	/**
	 * Dismiss Notice After closed.
	 */
	public function dismiss_notice() {
		check_ajax_referer( 'gutenverse_unibiz_dismiss' );

		if ( ! get_option( 'gutenverse_unibiz_notice_dismissed' ) ) {
			update_option( 'gutenverse_unibiz_notice_dismissed', true );
		}

		wp_send_json_success();
	}

	/**
	 * Hide doing_wp_cron query argument in url
	 */
	public function notice_unibiz() {
		if ( ! get_option( 'gutenverse_unibiz_notice' ) ) {
			update_option( 'gutenverse_unibiz_notice', true );
		}
		$notice_flag   = is_plugin_active( 'gutenverse/gutenverse.php' ) && get_option( 'gutenverse_unibiz_notice' );
		$current_theme = get_stylesheet();
		if ( 'unibiz' === $current_theme || ! current_user_can( 'manage_options' ) || $notice_flag ) {
			return;
		}
		$image_dir = GUTENVERSE_COMPANION_URL . '/assets/img';
		ob_start();
		?>
		<style>
			.update-php .wrap{
				max-width: 100% !important;
			}
			.gutenverse-companion-unibiz-notice{
				background : url(<?php echo esc_html( $image_dir ) . '/unibiz-bg-banner-gradient.png'; ?>);
				background-position: center;
				background-repeat: no-repeat;
				background-size: cover;
				position: relative;
			}
			.gutenverse-companion-unibiz-notice .unibiz-gutenverse-badge{
				position: absolute;
				bottom: 0;
				right: 0;
			}
			.notice.gutenverse-companion-unibiz-notice{
				border: none;
				padding: 0px;
			}
			.gutenverse-companion-unibiz-notice .content-wrapper{
				width: 100%;
				height: 100%;
				display: flex;
				overflow: hidden;
				position: relative;
			}

			.gutenverse-companion-unibiz-notice .content-wrapper .close-button{
				position: absolute;
				top: 5px;
				right: 5px;
				cursor: pointer;
				transition: transform .3s ease;
				z-index: 5;
			}
			.gutenverse-companion-unibiz-notice .content-wrapper .close-button:hover{
				transform: scale(.93);
			}
			
			.gutenverse-companion-unibiz-notice .content-wrapper .col-1{
				width: 50%;
				position: relative;
				z-index: 3;
			}
			.gutenverse-companion-unibiz-notice .content-wrapper .col-1 .content{
				margin: 40px 0px 40px 60px;
			}

			.gutenverse-companion-unibiz-notice .content-wrapper .col-1 .title{
				font-family: Host Grotesk;
				font-weight: 700;
				font-size: 24px;
				line-height: 1.14;
				background: linear-gradient(93.32deg, #00223D 0.65%, #371C73 68.04%);
				background-clip: text;
				-webkit-background-clip: text;
				-webkit-text-fill-color: transparent;
			}

			.gutenverse-companion-unibiz-notice .content-wrapper .col-1 .title .highlight-title{
				background: linear-gradient(84.2deg, #7032FF 15.94%, #4B8EFF 97.2%);
				background-clip: text;
				-webkit-background-clip: text;
				-webkit-text-fill-color: transparent;
			}
			.gutenverse-companion-unibiz-notice .content-wrapper .col-1 .description{
				font-family: Host Grotesk;
				font-weight: 400;
				font-size: 14px;
				color: #00223D99;
			}
			.gutenverse-companion-unibiz-notice .content-wrapper .col-1 .feature-wrapper{
				display: flex;
				gap: 10px;
				text-wrap: nowrap;
				align-items: center;
			}
			.gutenverse-companion-unibiz-notice .content-wrapper .col-1 .feature-wrapper .feature-item{
				display: flex;
				gap: 5px;
				align-items: center;
				font-family: Host Grotesk;
				font-weight: 500;
				font-size: 12px;
				color: #5C51F3;
				border-radius: 24px;
				padding: 3px 10px 3px 5px;
				background: #FFFFFF;
				border: 1px solid #5C51F34D
			}
			.gutenverse-companion-unibiz-notice .content-wrapper .col-1 .button-wrapper{
				display: flex;
				align-items: center;
				margin-top: 20px;
			}

			.gutenverse-companion-unibiz-notice .content-wrapper .col-1 .button-wrapper .button-install{
				width: 142px;
				height: 36px;
				border-radius: 8px;
				padding: 5px 16px;
				background: radial-gradient(103.69% 112% at 51.27% 100%, #4992FF 0%, #7722FF 100%);
				border: 1px solid #9760FF;
				color: white;
				cursor: pointer;
				transition: transform .3s ease;
				display: flex;
				justify-content: center;
				align-items: center;
			}
			.gutenverse-companion-unibiz-notice .content-wrapper .col-1 .button-wrapper .button-install svg {
				animation: infinite rotate 2s linear;
			}
			.gutenverse-companion-unibiz-notice .content-wrapper .col-1 .button-wrapper .button-install:hover{
				transform: scale(.93);
			}
			.gutenverse-companion-unibiz-notice .content-wrapper .col-1 .button-wrapper .arrow-wrapper{
				position: relative;
			}
			.gutenverse-companion-unibiz-notice .content-wrapper .col-1 .button-wrapper .unibiz-arrow{
				position: absolute;
				top: -55px;
				right: -130px;
			}

			.gutenverse-companion-unibiz-notice .content-wrapper .col-2{
				position: relative;
				width: 100%;
				display: flex;
				justify-content: center;
			}

			.gutenverse-companion-unibiz-notice .content-wrapper .col-2 .unibiz-wave{
				position:absolute;
				right: 0;
			}
			.gutenverse-companion-unibiz-notice .content-wrapper .col-2 .mockup-wrapper{
				display: flex;
				justify-content: center;
				position: relative;
			}
			.gutenverse-companion-unibiz-notice .content-wrapper .col-2 .mockup-wrapper .unibiz-mockup{
				z-index: 2;
				max-width: 550px;
			}
			.gutenverse-companion-unibiz-notice .content-wrapper .col-2 .mockup-wrapper .unibiz-confetti{
				z-index: 2;
				position: absolute;
				top: -10px;
				bottom: 0;
				height: 110%;
			}
			.gutenverse-companion-unibiz-notice .content-wrapper .col-2 .mockup-wrapper .unibiz-wave{
				z-index: 2;
				position: absolute;
				top: -200%;
				right: -70%;
			}
			@media screen and (max-width: 1440px) {
				.gutenverse-companion-unibiz-notice .content-wrapper .col-2{
					position: relative;
					width: 100%;
					display: flex;
					justify-content: end;
				}
			}
			@media screen and (max-width: 1300px) {
				.gutenverse-companion-unibiz-notice .content-wrapper .col-1{
					width: 100%;
				}
				.gutenverse-companion-unibiz-notice .content-wrapper .col-2{
					display: none;
				}
			}
			@media screen and (max-width: 768px) {
				.gutenverse-companion-unibiz-notice .content-wrapper .col-1 .title {
					font-size: 20px;
				}
				.gutenverse-companion-unibiz-notice .content-wrapper .col-1 .description {
					font-size: 12px;
				}
				.gutenverse-companion-unibiz-notice .content-wrapper .col-1 .content{
					margin: 20px;
				}
				.gutenverse-companion-unibiz-notice .content-wrapper .col-1 .button-wrapper .button-install{
					font-size: 12px;
				}
			}
			@media screen and (max-width: 530px) {
				.gutenverse-companion-unibiz-notice .content-wrapper .col-1 .button-wrapper .arrow-wrapper{
					display: none;
				}
			}
			@media screen and (max-width: 425px) {
				.gutenverse-companion-unibiz-notice .content-wrapper .col-1 .button-wrapper .button-install{
					width: auto;
					height: auto;
					padding: 6px 10px;
					font-size: 10px;
				}
				.gutenverse-companion-unibiz-notice .unibiz-gutenverse-badge{
					width: 100px;
					margin: 0 10px 15px 0;
				}
			}
			@keyframes rotate {
				from {
					transform: rotate(0deg);
				}

				to {
					transform: rotate(360deg);
				}
			}
		</style>
		<div class="notice gutenverse-companion-unibiz-notice">
			<div class="content-wrapper">
				<div class="close-button" id="gutenverse-unibiz-notice-close">
					<svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
						<foreignObject x="-3" y="-3" width="20" height="20"><div xmlns="http://www.w3.org/1999/xhtml" style="backdrop-filter:blur(1.5px);clip-path:url(#bgblur_0_23210_9188_clip_path);height:100%;width:100%"></div></foreignObject><g data-figma-bg-blur-radius="3">
						<rect width="14" height="14" rx="2" fill="#4F389C" fill-opacity="0.3"/>
						<path d="M9 5L5 9M5 5L9 9" stroke="white" stroke-width="0.8" stroke-linecap="round"/>
						</g>
						<defs>
						<clipPath id="bgblur_0_23210_9188_clip_path" transform="translate(3 3)"><rect width="14" height="14" rx="2"/>
						</clipPath></defs>
					</svg>

				</div>
				<div class="col-1">
					<div class="content">
						<h3 class="title"><?php esc_html_e( 'Action Required - Please Install', 'gutenverse' ); ?> <span class="highlight-title"><?php esc_html_e( 'Unibiz Theme!', 'gutenverse' ); ?></span></h3>
						<p class="description"><?php esc_html_e( 'Gutenverse Companion works at its full potential when paired with the Unibiz Theme, unlocking powerful features, enhanced customization, and seamless integration for building your ideal website.', 'gutenverse' ); ?></p>
						<div class="button-wrapper">
							<div class="button-install"><?php esc_html_e( 'Install Unibiz Theme', 'gutenverse' ); ?></div>
							<div class="arrow-wrapper">
								<img class="unibiz-arrow" src="<?php echo esc_html( $image_dir ) . '/unibiz-arrow.png'; ?>"  alt="image arrow unibiz"/>
							</div>
						</div>
					</div>
				</div>
				<div class="col-2">
					<div class="mockup-wrapper">
						<img class="unibiz-wave" src="<?php echo esc_html( $image_dir ) . '/unibiz-bg-banner-circle-full.png'; ?>" alt='wave'/>
						<img class="unibiz-confetti" src="<?php echo esc_html( $image_dir ) . '/unibiz-confetti.png'; ?>"  alt="image confetti"/>
						<img class="unibiz-mockup" src="<?php echo esc_html( $image_dir ) . '/unibiz-mockup.png'; ?>"  alt="image mockup"/>
					</div>
				</div>
			</div>
			<img class="unibiz-gutenverse-badge" src="<?php echo esc_html( $image_dir ) . '/unibiz-gutenverse-badge.png'; ?>"  alt="image gutenverse badge"/>
		</div>
		<script>
			const versionCompare = (v1, v2, operator) => {
				const a = v1.split('.').map(Number);
				const b = v2.split('.').map(Number);
				const len = Math.max(a.length, b.length);

				for (let i = 0; i < len; i++) {
					const num1 = a[i] || 0;
					const num2 = b[i] || 0;
					if (num1 > num2) {
						switch (operator) {
							case '>': case '>=': case '!=': return true;
							case '<': case '<=': case '==': return false;
						}
					}
					if (num1 < num2) {
						switch (operator) {
							case '<': case '<=': case '!=': return true;
							case '>': case '>=': case '==': return false;
						}
					}
				}

				// If equal so far
				switch (operator) {
					case '==': case '>=': case '<=': return true;
					case '!=': return false;
					case '>': case '<': return false;
				}
			};
			const installingPluginsCompanion = (pluginsList) => {
				return new Promise((resolve, reject) => {
					const { plugins: installedPlugin } = window['GutenverseConfig'] || window['GutenverseDashboard'] || {plugins: { 'gutenverse-companion': { active: true }}};
					const plugins = pluginsList.map(plgn => ({
						needUpdate: installedPlugin[plgn.slug] ? versionCompare(plgn.version, installedPlugin[plgn.slug]?.version , '>') : false,
						name: plgn.name,
						slug: plgn.slug,
						version: plgn.version,
						url: plgn.url,
						installed: !!installedPlugin[plgn.slug],
						active: !!installedPlugin[plgn.slug]?.active,
					}));
					setTimeout(() => {
						const installPlugins = (index = 0) => {
							if (index >= plugins.length) {
								resolve();
								return;
							}

							const plugin = plugins[index];
							if (plugin) {
								// Not installed
								if (plugin.needUpdate) {
									wp.apiFetch({
										path: `wp/v2/plugins/plugin?plugin=${plugin.slug}/${plugin.slug}`,
										method: 'PUT',
										data: {
											status: 'inactive'
										}
									}).then (() => {
										wp.apiFetch({
											path: `wp/v2/plugins/plugin?plugin=${plugin.slug}/${plugin.slug}`,
											method: 'DELETE'
										});
									}).then(() => {
										wp.apiFetch({
											path: 'wp/v2/plugins',
											method: 'POST',
											data: {
												slug: plugin.slug,
												status: 'active'
											}
										}).then(()=> setTimeout(() => installPlugins(index + 1), 1500)
										).catch((err) => console.log(err));
									});
								} else if (!plugin.installed) {
									wp.apiFetch({
										path: 'wp/v2/plugins',
										method: 'POST',
										data: {
											slug: plugin.slug,
											status: 'active'
										}
									}).then(()=> setTimeout(() => installPlugins(index + 1), 1500)
									).catch((err) => console.log(err));

									// Installed but not active
								} else if (!plugin.active) {
									const slug = plugin.slug;
									const path = `${slug}/${slug}`;
									wp.apiFetch({
										path: `wp/v2/plugins/plugin?plugin=${path}`,
										method: 'POST',
										data: {
											status: 'active'
										}
									}).then(()=> setTimeout(() => installPlugins(index + 1), 1500)
									).catch((err) => console.log(err));

									// Already installed & active
								} else {
									setTimeout(() => installPlugins(index + 1), 1500);
								}
							}
						};

						installPlugins();
					}, 500);
				});
			};
			const installAndActivateThemeCompanion = (slug) => {
				return new Promise((resolve, reject) => {
					wp.apiFetch({
						path: `gutenverse-companion/v1/library/install-activate-theme`,
						method: 'POST',
						data: {
							slug: slug,
						},
					}).then((data) => resolve(data)
					).catch((err) => {
						console.error('Error', err);
						reject(err);
					});
				});
			}
			(function($) {

				$('.gutenverse-companion-unibiz-notice #gutenverse-unibiz-notice-close').on('click', function() {
					$('.gutenverse-companion-unibiz-notice').fadeOut();

					jQuery.post(ajaxurl, {
						action: 'gutenverse_unibiz_dismiss_notice',
						_ajax_nonce: '<?php echo esc_js( wp_create_nonce( 'gutenverse_unibiz_dismiss' ) ); ?>'
					});
				});
				$('.gutenverse-companion-unibiz-notice .col-1 .button-wrapper .button-install').on('click', function() {
					const themeSlug = 'unibiz'; // change this to your theme slug
					const pluginsList = [
						{ name: 'Gutenverse Companion', slug: 'gutenverse-companion', version: '2.0.0', url: '' },
						{ name: 'Gutenverse', slug: 'gutenverse', version: '3.0.0', url: '' },
					];
					const installBtn = document.querySelector('.button-install');
					if (installBtn) {
						installBtn.innerHTML = `<svg width="17" height="17" viewBox="0 0 17 17" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path d="M8.69737 1V2.89873M8.69737 12.962V16M3.76316 8.40506H1M16 8.40506H14.8158M13.7951 13.3092L13.2368 12.7722M13.9586 3.40439L12.8421 4.47848M3.10914 13.7811L5.34211 11.6329M3.27264 3.2471L4.94737 4.85823" stroke="white" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round" />
						</svg>`;
					}
					// Step 1: Install + Activate Theme
					installAndActivateThemeCompanion(themeSlug)
						.then(themeResponse => {
							return installingPluginsCompanion(pluginsList);
						})
						.then(() => {
							// Redirect to Gutenverse Companion dashboard demo page
							window.location.replace(
								`${window.location.origin}/wp-admin/admin.php?page=gutenverse-companion-dashboard&path=demo`
							);
						})
						.catch(err => {
							console.error('Installation failed:', err);
							alert('Something went wrong during installation. Please try again.');
						});
				});
			})(jQuery);
		</script>
		<?php
		$data_html = ob_get_contents();
		ob_end_clean();
		echo $data_html;
	}

	/**
	 * Change option page upgrade to true.
	 */
	public function companion_notice_close() {
		update_option( 'gutenverse-companion-base-theme-notice', true );
	}
}
