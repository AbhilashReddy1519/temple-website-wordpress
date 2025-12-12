<?php
/**
 * Block Pattern Class
 *
 * @author Jegstudio
 * @package unibiz
 */

namespace Unibiz;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WP_Block_Pattern_Categories_Registry;

/**
 * Init Class
 *
 * @package unibiz
 */
class Block_Patterns {

	/**
	 * Instance variable
	 *
	 * @var $instance
	 */
	private static $instance;

	/**
	 * Class instance.
	 *
	 * @return BlockPatterns
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
	public function __construct() {
		$this->register_block_patterns();
		$this->register_synced_patterns();
	}

	/**
	 * Register Block Patterns
	 */
	private function register_block_patterns() {
		$block_pattern_categories = array(
			'unibiz-core' => array( 'label' => __( 'Unibiz Core Patterns', 'unibiz' ) ),
		);

		if ( defined( 'GUTENVERSE' ) ) {
			$block_pattern_categories['unibiz-gutenverse'] = array( 'label' => __( 'Unibiz Gutenverse Patterns', 'unibiz' ) );
			$block_pattern_categories['unibiz-pro'] = array( 'label' => __( 'Unibiz Gutenverse PRO Patterns', 'unibiz' ) );
		}

		$block_pattern_categories = apply_filters( 'unibiz_block_pattern_categories', $block_pattern_categories );

		foreach ( $block_pattern_categories as $name => $properties ) {
			if ( ! WP_Block_Pattern_Categories_Registry::get_instance()->is_registered( $name ) ) {
				register_block_pattern_category( $name, $properties );
			}
		}

		$block_patterns = array(
            'unibiz-single-core-hero',			'unibiz-single-core-content',			'unibiz-archive-core-hero',			'unibiz-core-post-block',			'unibiz-search-core-hero',			'unibiz-search-core-search',			'unibiz-core-404',			'unibiz-index-core-hero',			'unibiz-core-post-block',			'unibiz-page-no-sidebar-core-hero',			'unibiz-page-no-sidebar-core-content',			'unibiz-core-footer-alternate',			'unibiz-core-home-hero',			'unibiz-core-home-benefits',			'unibiz-core-home-about',			'unibiz-core-home-services',			'unibiz-core-home-clients',			'unibiz-core-home-authority',			'unibiz-core-home-testimonials',			'unibiz-core-home-blog',			'unibiz-page-core-hero',			'unibiz-page-core-content',
		);

		if ( defined( 'GUTENVERSE' ) ) {
            $block_patterns[] = 'unibiz-index-gutenverse-hero';			$block_patterns[] = 'unibiz-gutenverse-post-block';			$block_patterns[] = 'unibiz-archive-gutenverse-hero';			$block_patterns[] = 'unibiz-gutenverse-post-block';			$block_patterns[] = 'unibiz-search-gutenverse-hero';			$block_patterns[] = 'unibiz-search-gutenverse-search';			$block_patterns[] = 'unibiz-single-gutenverse-hero';			$block_patterns[] = 'unibiz-single-gutenverse-content';			$block_patterns[] = 'unibiz-gutenverse-404';			$block_patterns[] = 'unibiz-gutenverse-header';			$block_patterns[] = 'unibiz-gutenverse-footer';			$block_patterns[] = 'unibiz-gutenverse-header-alternate';			$block_patterns[] = 'unibiz-gutenverse-footer-alternate';			$block_patterns[] = 'unibiz-page-no-sidebar-gutenverse-hero';			$block_patterns[] = 'unibiz-page-no-sidebar-gutenverse-content';			$block_patterns[] = 'unibiz-gutenverse-home-hero';			$block_patterns[] = 'unibiz-gutenverse-home-benefits';			$block_patterns[] = 'unibiz-gutenverse-home-about';			$block_patterns[] = 'unibiz-gutenverse-home-services';			$block_patterns[] = 'unibiz-gutenverse-home-clients';			$block_patterns[] = 'unibiz-gutenverse-home-authority';			$block_patterns[] = 'unibiz-gutenverse-home-testimonials';			$block_patterns[] = 'unibiz-gutenverse-home-blog';			$block_patterns[] = 'unibiz-page-gutenverse-hero';			$block_patterns[] = 'unibiz-page-gutenverse-content';
            
		}

		$block_patterns = apply_filters( 'unibiz_block_patterns', $block_patterns );
		$pattern_list   = get_option( 'unibiz_synced_pattern_imported', false );
		if ( ! $pattern_list ) {
			$pattern_list = array();
		}

		if ( function_exists( 'register_block_pattern' ) ) {
			foreach ( $block_patterns as $block_pattern ) {
				$pattern_file = get_theme_file_path( '/inc/patterns/' . $block_pattern . '.php' );
				$pattern_data = require $pattern_file;

				if ( (bool) $pattern_data['is_sync'] ) {
					$post = get_page_by_path( $block_pattern . '-synced', OBJECT, 'wp_block' );
					if ( empty( $post ) ) {
						/**Download Image */
						$content = wp_slash( $pattern_data['content'] );
						if ( isset( $pattern_data['images'] ) ) {
							$images = json_decode( $pattern_data['images'] );
							foreach ( $images as $key => $image ) {
								$url  = $image->image_url;
								$data = Helper::check_image_exist( $url );
								if ( ! $data ) {
									$data = Helper::handle_file( $url );
								}
								$content = str_replace( $url, $data['url'], $content );
								$image_id = $image->image_id;
								if ( $image_id && 'null' !== $image_id ) {
									$content = str_replace( '"imageId\":' . $image_id, '"imageId\":' . $data['id'], $content );
								}
							}
						}
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
							foreach( $pattern_category as $category ){
								wp_set_object_terms( $post_id, $category, 'wp_pattern_category' );
							}
						}
						$pattern_data['content']  = '<!-- wp:block {"ref":' . $post_id . '} /-->';
						$pattern_data['inserter'] = false;
						$pattern_data['slug']     = $block_pattern;

						$pattern_list[] = $pattern_data;
					}
				} else {
					register_block_pattern(
						'unibiz/' . $block_pattern,
						require $pattern_file
					);
				}
			}
			update_option( 'unibiz_synced_pattern_imported', $pattern_list );
		}
	}

	/**
	 * Register Synced Patterns
	 */
	 private function register_synced_patterns() {
		$patterns = get_option( 'unibiz_synced_pattern_imported' );

		 foreach ( $patterns as $block_pattern ) {
			 register_block_pattern(
				'unibiz/' . $block_pattern['slug'],
				$block_pattern
			);
		 }
	 }
}
