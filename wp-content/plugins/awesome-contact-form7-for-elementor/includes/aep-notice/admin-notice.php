<?php

if( get_option( 'aep_ac7_notice' ) != '29acf7_never_show' ) {
    add_action( 'admin_notices', 'aep_ac7_notice_options' );
}

function aep_ac7_notice_options() {
      ?>
      <div class='notice aep-ac7-notice'>
          <div class="aep-ac7-notice-logo">
              <img src="<?php echo esc_url(plugins_url( '/', __FILE__ ) .'/img/aep-inf-img.jpg')?>" >
            </div>
        <div class="aep-notice-content">
            <h3>Elementor Timeline Widget</h3>
        
            
			<p class="aep-link">
            Thank you for using <strong>Elementor Timeline Widget.</strong> If you like this plugin please make a small donation or give us a good review for our motivation.
            <br>
            <br>
                <a href="https://www.paypal.com/donate?hosted_button_id=ZC2N2PY77T9HL" class="donate"> <i class="icon-donation"></i> Donate</a>  | 
            <a target="_blank" href="https://wordpress.org/support/plugin/3r-elementor-timeline-widget/reviews/?filter=5" class="review">
            <i class="icon-star-empty"></i> 
            Leave a Review</a> | 
            <a href="javascript:void()" class="aep-ac7-never-show"><i class="icon-cancel-circle"></i> Never Show</a>
            <!--a href="#review_link" class="later">Maybe later</a-->
            </p>
        </div>
      </div>
<?php
}

add_action( 'admin_enqueue_scripts', 'aep_ac7_add_script' );
function aep_ac7_add_script() {
        wp_register_script( 'aep-ac7-notice-update',  plugins_url( '/', __FILE__ ) . '/js/update-notice.js','','1.0', false );
        wp_enqueue_style( 'aep-ac7-notice-update-css',  plugins_url( '/', __FILE__ ) . '/css/aep-notice.css',array());
        
        wp_localize_script( 'aep-ac7-notice-update', 'aep_ac7_notice_params', array(
            'ajaxurl' => admin_url( 'admin-ajax.php' ),
        ));
        
        wp_enqueue_script(  'aep-ac7-notice-update' );
}

add_action( 'wp_ajax_aep_ac7_never_show', 'aep_ac7_never_show' );

function aep_ac7_never_show() {
      update_option( 'aep_ac7_notice', '29acf7_never_show' );
}