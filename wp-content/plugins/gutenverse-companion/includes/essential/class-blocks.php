<?php
/**
 * Blocks class
 *
 * @author Jegstudio
 * @since 1.0.0
 * @package gutenverse-companion
 */

namespace Gutenverse_Companion\Essential;

/**
 * Class Blocks
 *
 * @package gutenverse-companion
 */
class Blocks {
	/**
	 * Blocks constructor.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'register_blocks' ), 101 );
	}

	/**
	 * Register All Blocks
	 */
	public function register_blocks() {
		// Static block.
		$directory = apply_filters( 'gutenverse_companion_essential_assets_directory', false );
		/**
		 * 'jeg_theme_essential_assets_directory' deprecated since version 1.0.1 Use 'gutenverse_companion_essential_assets_directory' instead.
		 */
		if ( ! $directory ) {
			$directory = apply_filters( 'jeg_theme_essential_assets_directory', false );
		}
		if ( $directory ) {
			register_block_type( $directory . '/block/mega-menu/block.json' );
			register_block_type( $directory . '/block/mega-menu-item/block.json' );
			register_block_type( $directory . '/block/advance-tabs/block.json' );
			register_block_type( $directory . '/block/advance-tab/block.json' );
		}
	}
}
