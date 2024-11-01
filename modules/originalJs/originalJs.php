<?php
/*
Plugin Name: オリジナルCSS
Description: General setting for this site.
Text Domain: wp-assistant
Domain Path: ../../languages/
*/

namespace WP_Assistant\modules\originalJs;

use WP_Assistant\inc\config;
use WP_Assistant\modules\module;

class originalJs extends module {

	/**
	 * オプション
	 * @var null
	 */
	public $options = null;

	/**
	 * 初期化
	 */
	public function __construct() {

		$this->settings = parent::get_settings();

		add_action( 'admin_init', array( $this, 'add_settings' ), 10 );

		if ( ! get_option( config::get( 'prefix' ) . 'install' ) ) {
			$this->options = get_option( config::get( 'prefix' ) . 'options' );
		} else {
			$this->options = config::get( 'options' );
		}

		add_action( 'wp', array( $this, "init" ) );
	}

	/**
	 * サイトに反映する
	 */
	public function init() {

		if ( ! is_admin() ) {
			add_filter( "wp_head", array( $this, "render" ) );
		}
	}

	/**
	 * 管理画面の設定
	 * @return void
	 */
	public function add_settings() {
		/**
		 * 1. サイト設定
		 */
		$this->settings->add_section(
			array(
				'id'        => 'originalJs',
				'title'     => __( 'Original JavaScript', 'wp-assistant' ),
				'tabs_name' => __( 'Original JavaScript', 'wp-assistant' ),
			)
		)->add_field(
			array(
				'id'      => 'original_js',
				'title'   => __( 'オリジナル JavaScript', 'wp-assistant' ),
				'type'    => 'source',
				'default' => '(function($){

    $(function(){
        
    });
    
})(jQuery);',
				'section' => 'originalJs',
				'desc'    => __( '<p></p>', 'wp-assistant' ),
				'options' => array(
					'mode'   => 'javascript',
					'height' => '500px',
					'width'  => '100%',
				)
			)
		);
	}

	/**
	 * 出力
	 */
	public function render() {
		$js = config::get_option( "original_js" );
		if ( $js ) {
			echo "<script>" . stripslashes_deep($js) . "</script>";
		}
	}


}
