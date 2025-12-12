<?php
/**
 * Init Class
 *
 * @author Jegstudio
 * @since 1.0.0
 * @package gutenverse-companion
 */

namespace Gutenverse_Companion\Essential;

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
	protected static $instance;

	/**
	 * Hold instance of assets
	 *
	 * @var Assets
	 */
	public $assets;

	/**
	 * Style Generator
	 *
	 * @var Style_Generator
	 */
	public $style_generator;

	/**
	 * Instance of Blocks.
	 *
	 * @var Blocks
	 */
	protected $blocks;

	/**
	 * API
	 *
	 * @var API
	 */
	public $api;

	/**
	 * Singleton page for Init Class
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
	 * Init constructor.
	 */
	public function __construct() {
		/**
		 * 'jeg_theme_essential_mode_on' deprecated since version 1.0.1 Use 'gutenverse_companion_essential_mode_on' instead.
		 */
		if ( class_exists( 'Gutenverse_Initialize_Framework' ) ) {
			$this->init_hook();
		}
	}

	/**
	 * Initialize Class.
	 */
	public function init_class() {
		if ( ! class_exists( '\Gutenverse\Pro\License' ) ) {
			$this->blocks          = new Blocks();
			$this->style_generator = new Style_Generator();
		}
		$this->assets = new Assets();
		$this->api    = new Api();
	}

	/**
	 * Init Hook
	 */
	public function init_hook() {
		add_action( 'gutenverse_after_init_framework', array( $this, 'init_class' ) );
		add_filter( 'gutenverse_dashboard_config', array( $this, 'dashboard_config' ) );
	}

	/**
	 * Dashboard config
	 *
	 * @param array $config config .
	 *
	 * @return array
	 */
	public function dashboard_config( $config ) {
		$config['noticeActions'] = ! empty( $config['noticeActions'] ) ? $config['noticeActions'] : array();

		$config['noticeActions']['gutenverse-theme-version-notice'] = array(
			'show' => defined( 'GUTENVERSE_FRAMEWORK_VERSION' ) && defined( 'GUTENVERSE_FRAMEWORK_REQUIRED_VERSION' ) && version_compare( GUTENVERSE_FRAMEWORK_VERSION, GUTENVERSE_FRAMEWORK_REQUIRED_VERSION, '>=' ),
		);
		return $config;
	}
}
