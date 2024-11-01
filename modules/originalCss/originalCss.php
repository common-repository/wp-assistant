<?php
/*
Plugin Name: オリジナルCSS
Description: General setting for this site.
Text Domain: wp-assistant
Domain Path: ../../languages/
*/

namespace WP_Assistant\modules\originalCss;

use WP_Assistant\inc\config;
use WP_Assistant\modules\module;

class originalCss extends module {

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

		if ( ! is_admin() ){
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
				'id'        => 'originalCss',
				'title'     => __( 'Original Css', 'wp-assistant' ),
				'tabs_name' => __( 'Original Css', 'wp-assistant' ),
			)
		)->add_field(
			array(
				'id'      => 'original_css',
				'title'   => __( 'オリジナルCSS', 'wp-assistant' ),
				'type'    => 'source',
				'default' => '',
				'section' => 'originalCss',
				'desc'    => __( '<p></p>', 'wp-assistant' ),
				'options' => array(
					'mode'   => 'css',
					'height' => '500px',
					'width'  => '100%',
				)
			)
		);
	}

	/**
	 * 出力
	 */
	public function render(){
		$css = config::get_option("original_css");
		if ( $css ) {
			echo "<style>" .stripslashes_deep($css). "</style>";
	     }
	}


}
