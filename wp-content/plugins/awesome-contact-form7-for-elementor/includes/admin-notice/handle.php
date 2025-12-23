<?php 
include __DIR__ . '/ca-framework/loader.php';

$req_plugin_slug = 'ultraaddons-elementor-lite/init.php';
$this_plugin_slug = '3r-elementor-timeline-widget/init.php';
$args = array(
    'Name' => "UltraAddons Elementor"
);

$my_notice = new TB_Framework\Notice('10oct');
//$my_notice->start_date = '8/31/2022 00:00:00';
$my_notice->notice_type = 'ca-success';
$my_notice->set_message('for Elementor. Get <span style="color:#333">50+ Widgets & 13+ Extentions</span> :: Discount Upto 70% <a class="tb-get-btn" href="https://ultraaddons.com/pricing/?ref=8" target="_blank">GET DISCOUNT HERE &#8594;	</a>');
$my_notice->set_img_target('https://ultraaddons.com/pricing/?ref=8');
$my_notice->notice_style='notice';

$my_notice->set_img(plugins_url( 'ca-framework/assets/img/Ultra-Addons-Loto-for-white.png', __FILE__ ))
->show();

