<?php
/**
 * Theme Functions
 *
 * @author Jegstudio
 * @package unibiz
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

defined( 'UNIBIZ_VERSION' ) || define( 'UNIBIZ_VERSION', '1.0.6' );
defined( 'UNIBIZ_DIR' ) || define( 'UNIBIZ_DIR', trailingslashit( get_template_directory() ) );

defined( 'GUTENVERSE_COMPANION_REQUIRED_VERSION' ) || define( 'GUTENVERSE_COMPANION_REQUIRED_VERSION', '2.0.0' );

require get_parent_theme_file_path( 'inc/autoload.php' );

Unibiz\Init::instance();
