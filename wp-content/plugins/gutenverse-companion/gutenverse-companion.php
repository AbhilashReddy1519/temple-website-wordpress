<?php
/**
 * Plugin Name: Gutenverse Companion
 * Description: Mandatory plugin to install when installing Base themes from Gutenverse.
 * Plugin URI: https://gutenverse.com/
 * Author: Jegstudio
 * Version: 2.0.2
 * Author URI: https://jegtheme.com/
 * License: GPLv3
 * Text Domain: gutenverse-companion
 *
 * @package gutenverse-companion
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Gutenverse_Companion\Init;

defined( 'GUTENVERSE_COMPANION' ) || define( 'GUTENVERSE_COMPANION', 'gutenverse-companion' );
defined( 'GUTENVERSE_COMPANION_VERSION' ) || define( 'GUTENVERSE_COMPANION_VERSION', '2.0.2' );
defined( 'GUTENVERSE_COMPANION_NOTICE_VERSION' ) || define( 'GUTENVERSE_COMPANION_NOTICE_VERSION', '1.0.0' );
defined( 'GUTENVERSE_COMPANION_NAME' ) || define( 'GUTENVERSE_COMPANION_NAME', 'Gutenverse Companion' );
defined( 'GUTENVERSE_COMPANION_URL' ) || define( 'GUTENVERSE_COMPANION_URL', plugins_url( GUTENVERSE_COMPANION ) );
defined( 'GUTENVERSE_COMPANION_PLUGIN_URL' ) || define( 'GUTENVERSE_COMPANION_PLUGIN_URL', plugins_url( GUTENVERSE_COMPANION ) );
defined( 'GUTENVERSE_COMPANION_FILE' ) || define( 'GUTENVERSE_COMPANION_FILE', __FILE__ );
defined( 'GUTENVERSE_COMPANION_DIR' ) || define( 'GUTENVERSE_COMPANION_DIR', plugin_dir_path( __FILE__ ) );
defined( 'GUTENVERSE_COMPANION_CLASS_DIR' ) || define( 'GUTENVERSE_COMPANION_CLASS_DIR', GUTENVERSE_COMPANION_DIR . 'includes/' );
defined( 'GUTENVERSE_COMPANION_LANG_DIR' ) || define( 'GUTENVERSE_COMPANION_LANG_DIR', GUTENVERSE_COMPANION_DIR . 'languages' );
defined( 'GUTENVERSE_COMPANION_PATH' ) || define( 'GUTENVERSE_COMPANION_PATH', plugin_basename( __FILE__ ) );
defined( 'GUTENVERSE_COMPANION_LIBRARY_URL' ) || define( 'GUTENVERSE_COMPANION_LIBRARY_URL', 'https://gutenverse.com' );
defined( 'GUTENVERSE_LICENSE_SERVER' ) || define( 'GUTENVERSE_LICENSE_SERVER', 'https://pro.gutenverse.com' );


require_once GUTENVERSE_COMPANION_DIR . 'lib/autoload.php';

Init::instance();
