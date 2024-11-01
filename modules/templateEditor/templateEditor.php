<?php
/*
Plugin Name: Original Dashboard Widget
Description: Please input the content to be displayed on the dashboard widget.
Text Domain: wp-assistant
Domain Path: /languages/
*/
namespace WP_Assistant\modules\templateEditor;

use WP_Assistant\inc\config;
use WP_Assistant\modules\module;

class templateEditor extends module {

	/**
	 * 初期化
	 */
	public function __construct( $parent ) {
		$this->parent = $parent;

		if ( intval( config::get_option( 'modules_list_templateEditor' ) ) === 0 ) {
			return false;
		}
		/* admin_menu アクションフックでカスタムボックスを定義 */
		add_action( 'admin_menu', array( $this, 'add_meta_box' ) );

		/* データが入力された際 save_post アクションフックを使って何か行う */
		add_action( 'save_post', array( $this, 'template_save_data' ) );

		add_filter( 'manage_pages_columns', array( $this, 'template_editor_head' ) );
		add_action( 'manage_pages_custom_column', array( $this, 'template_editor_content' ), 10, 2 );

	}

	// ADD NEW COLUMN
	function template_editor_head( $defaults ) {
		$defaults['code_status'] = '編集方法';
		return $defaults;
	}

// SHOW THE FEATURED IMAGE
	function template_editor_content( $column_name, $post_ID ) {
		if ( $column_name == 'code_status' ) {
			$post=get_post($post_ID);
			if ( $post->page_template !==  "default"){
				echo "コード管理";
			} else {
				echo "ビジュアルエディタ";
			}
		}
	}

	public function add_meta_box() {
		add_meta_box( 'template_editor_sectionid', __( 'Template Editor', 'myplugin_textdomain' ),
			array( $this, 'template_editor_custom_box' ), 'page', 'advanced' );
	}


	function template_editor_custom_box() {
		global $post;
		$content       = false;
		$template      = $post->page_template;
		$theme         = wp_get_theme();
		$template_name = $theme->theme_root . "/" . get_template() . "/" . $template;

		$mce_hide = get_post_meta( $post->ID, "template_editor_mce_hide", true );
		if ( $template !== "default" ) {
			$content = file_get_contents( $template_name );
		}

		if ( $content ) {

			// 認証に nonce を使う
			echo '<input type="hidden" name="wpa_template_editor_nonce" id="wpa_template_editor_nonce" value="' .
			     wp_create_nonce( "wp-assistant_template_editor" ) . '" />';
			echo '<input type="hidden" name="file_path" id="file_path" value="' .
			     $template_name . '" />';
			echo '<input type="checkbox" name="template_editor_mce_hide" id="template_editor_mce_hide" value="1" ' . checked( $mce_hide,
					"1", false ) . ' /> このページでビジュアルエディターを非表示に';

			// データ入力用の実際のフォーム
			echo '<label for="template_editor">' . __( "<p>テンプレートを編集</p>",
					'myplugin_textdomain' ) . '</label> ';
			echo '<br><textarea type="text" id="template_editor" name="template_editor" value="whatever" size="25" style="width:100%; height: 800px;">' . $content . '</textarea>';
			?>
			<div id="editor<?php echo $post->ID; ?>_ace"></div>
			<script>
				;
				(function ($) {
					function aceEditorInit() {

						ace.require("ace/ext/language_tools");
						ace.require("ace/ext/emmet");
						var editor = ace.edit("editor<?php echo $post->ID;?>_ace");

						editor.setTheme("ace/theme/github");
						editor.getSession().setMode("ace/mode/php");
						editor.setOption({
							enableEmmet: true,
							enableBasicAutocompletion: true,
							enableSnippets: true,
							enableLiveAutocompletion: false
						});
						var textarea = $('#template_editor').hide();
						editor.getSession().setValue(textarea.val());
						editor.getSession().on('change', function () {
							textarea.val(editor.getSession().getValue());
							$('#wpa-submit').removeAttr('disabled');
						});
						textarea.on('change', function () {
							editor.getSession().setValue(textarea.val());

						});
					}


					$(function () {
						<?php if ( (int) $mce_hide === 1 ) {
						?>
						$("#postdivrich").hide();
						<?php
						}?>
						var textarea = $('#template_editor');

						var aceEditor = $('#editor<?php echo $post->ID; ?>_ace');
						if (textarea.width() < 600) {
							aceEditor.width('600');
						} else {
							aceEditor.width(textarea.width());
						}
						aceEditor.height(textarea.height() + 200);
						aceEditorInit();
					});
				})(jQuery);
			</script>
			<?php
		} else {
			echo "<p>テンプレートが設定されている時のみ有効となります。</p>";
		}
	}

	/**
	 * データの保存
	 *
	 * @param $post_id
	 *
	 * @return mixed
	 */
	function template_save_data( $post_id ) {

		// データが先ほど作った編集フォームのから適切な認証とともに送られてきたかどうかを確認。
		// save_post は他の時にも起動する場合がある。

		$nonce = ( isset( $_POST['wpa_template_editor_nonce'] ) ) ? $_POST['wpa_template_editor_nonce'] : false;
		if ( ! wp_verify_nonce( $nonce, "wp-assistant_template_editor" ) ) {
			return $post_id;
		}

		/**
		 * パーミッションのチェック
		 */
		if ( 'page' == $_POST['post_type'] ) {
			if ( ! current_user_can( 'edit_page', $post_id ) ) {
				return $post_id;
			}
		} else {
			if ( ! current_user_can( 'edit_post', $post_id ) ) {
				return $post_id;
			}
		}

		// 承認ができたのでデータを探して保存
		$file_path = isset( $_POST['file_path'] ) ? $_POST['file_path'] : false;
		$mydata    = $_POST['template_editor'];
		$mydata    = stripslashes_deep( $mydata );
		if ( is_writeable( $file_path ) ) {
			$f = fopen( $file_path, 'w+' );
			if ( $f !== false ) {
				fwrite( $f, $mydata );
				fclose( $f );
			}
		}
		update_post_meta( $post_id, "template_editor_mce_hide", $_POST["template_editor_mce_hide"] );

		// $mydata を使って何かを行う
		// （add_post_meta()、update_post_meta()、またはカスタムテーブルを使うなど）

		return $mydata;
	}

}
