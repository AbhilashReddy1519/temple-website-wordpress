<?php
/**
 * Pattern content.
 */
return array(
	'title'      => __( 'Unibiz Core Post Block', 'unibiz' ),
	'categories' => array( 'unibiz-core' ),
	'content'    => '<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"48px","bottom":"80px","right":"20px","left":"20px"}}},"layout":{"type":"constrained","wideSize":"1140px","contentSize":"1140px"}} -->
<div class="wp-block-group alignfull" style="padding-top:48px;padding-right:20px;padding-bottom:80px;padding-left:20px"><!-- wp:columns {"style":{"spacing":{"blockGap":{"top":"60px","left":"60px"}}}} -->
<div class="wp-block-columns"><!-- wp:column {"width":"760px"} -->
<div class="wp-block-column" style="flex-basis:760px"><!-- wp:query {"queryId":47,"query":{"perPage":3,"pages":0,"offset":0,"postType":"post","order":"desc","orderBy":"date","author":"","search":"","exclude":[],"sticky":"","inherit":false},"metadata":{"categories":["posts"],"patternName":"core/query-standard-posts","name":"Standard"}} -->
<div class="wp-block-query"><!-- wp:group {"style":{"spacing":{"blockGap":"56px"}},"layout":{"type":"flex","orientation":"vertical","justifyContent":"stretch"}} -->
<div class="wp-block-group"><!-- wp:post-template {"className":"unibiz-post-template","style":{"spacing":{"blockGap":"40px"},"elements":{"link":{"color":{"text":"var:preset|color|black"}}}},"textColor":"black","layout":{"type":"default"}} -->
<!-- wp:group {"style":{"spacing":{"padding":{"bottom":"40px"}}},"layout":{"type":"flex","orientation":"vertical","justifyContent":"stretch"}} -->
<div class="wp-block-group" style="padding-bottom:40px"><!-- wp:group {"style":{"spacing":{"blockGap":"5px"}},"layout":{"type":"flex","orientation":"vertical","justifyContent":"stretch"}} -->
<div class="wp-block-group"><!-- wp:group {"style":{"spacing":{"blockGap":"16px"}},"layout":{"type":"flex","orientation":"vertical","justifyContent":"stretch"}} -->
<div class="wp-block-group"><!-- wp:post-terms {"term":"category","style":{"typography":{"fontStyle":"normal","fontWeight":"600","lineHeight":"1","textTransform":"uppercase"},"elements":{"link":{"color":{"text":"var:preset|color|gv-color-primary"},":hover":{"color":{"text":"var:preset|color|gutenverse-secondary"}}}}},"textColor":"gv-color-primary","fontSize":"mini","fontFamily":"host-grotesk"} /-->

<!-- wp:post-title {"level":3,"isLink":true,"className":".unibiz-h3-alt-2","style":{"typography":{"lineHeight":"1.3","fontStyle":"normal","fontWeight":"500"},"elements":{"link":{"color":{"text":"var:preset|color|gv-color-text-primary"},":hover":{"color":{"text":"var:preset|color|gv-color-primary"}}}}},"fontSize":"largest","fontFamily":"host-grotesk"} /-->

<!-- wp:group {"style":{"layout":{"selfStretch":"fit","flexSize":null},"spacing":{"blockGap":"16px","margin":{"bottom":"24px"}},"dimensions":{"minHeight":""}},"layout":{"type":"flex","flexWrap":"nowrap","verticalAlignment":"top","justifyContent":"left"}} -->
<div class="wp-block-group" style="margin-bottom:24px"><!-- wp:post-author-name {"style":{"elements":{"link":{"color":{"text":"var:preset|color|gv-color-text-primary"}}},"typography":{"lineHeight":"1","fontStyle":"normal","fontWeight":"600"}},"textColor":"gv-color-text-primary","fontSize":"mini","fontFamily":"host-grotesk"} /-->

<!-- wp:separator {"className":"is-style-default","style":{"layout":{"selfStretch":"fixed","flexSize":"2.4px"},"spacing":{"margin":{"top":"5px"}}},"backgroundColor":"gv-color-border"} -->
<hr class="wp-block-separator has-text-color has-gv-color-border-color has-alpha-channel-opacity has-gv-color-border-background-color has-background is-style-default" style="margin-top:5px"/>
<!-- /wp:separator -->

<!-- wp:post-date {"style":{"typography":{"lineHeight":"1"},"elements":{"link":{"color":{"text":"var:preset|color|gv-color-text-secondary"},":hover":{"color":{"text":"var:preset|color|gv-color-primary"}}}}},"fontSize":"mini","fontFamily":"host-grotesk"} /--></div>
<!-- /wp:group --></div>
<!-- /wp:group -->

<!-- wp:post-featured-image {"isLink":true,"width":"760px","height":"380px","align":"wide","style":{"border":{"radius":"16px"},"spacing":{"margin":{"bottom":"27px"}}}} /-->

<!-- wp:post-excerpt {"moreText":"Read More","style":{"elements":{"link":{"color":{"text":"var:preset|color|gv-color-text-secondary"}}}},"textColor":"gv-color-text-secondary","fontSize":"tiny","fontFamily":"host-grotesk"} /--></div>
<!-- /wp:group --></div>
<!-- /wp:group -->

<!-- wp:separator {"className":"is-style-default","style":{"spacing":{"margin":{"top":"0","bottom":"0"}}},"backgroundColor":"gv-color-border"} -->
<hr class="wp-block-separator has-text-color has-gv-color-border-color has-alpha-channel-opacity has-gv-color-border-background-color has-background is-style-default" style="margin-top:0;margin-bottom:0"/>
<!-- /wp:separator -->
<!-- /wp:post-template -->

<!-- wp:query-pagination {"paginationArrow":"chevron","showLabel":false,"layout":{"type":"flex","justifyContent":"center"}} -->
<!-- wp:query-pagination-previous /-->

<!-- wp:query-pagination-numbers {"fontSize":"little","fontFamily":"host-grotesk"} /-->

<!-- wp:query-pagination-next /-->
<!-- /wp:query-pagination --></div>
<!-- /wp:group --></div>
<!-- /wp:query --></div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column"><!-- wp:template-part {"slug":"sidebar","area":"uncategorized"} /--></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:group -->',
	'is_sync' => false,
);
