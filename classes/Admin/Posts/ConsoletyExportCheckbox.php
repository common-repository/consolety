<?php

namespace Consolety\Admin\Posts;

class ConsoletyExportCheckbox {
	private static $instance;

	public static function getInstance() {

		if ( ! isset( self::$instance ) ) {
			self::$instance = new static();
		}

		return self::$instance;
	}

	public function __construct() {
		add_action( "add_meta_boxes", [ $this, "add_consolety_meta_box" ] );
		add_action("save_post", [$this,"save_consolety_meta_box"], 10, 3);
	}

	public function add_consolety_meta_box() {
		add_meta_box( "demo-meta-box", "Consolety Export", [$this,"consolety_meta_box_markup"], "post", "side", "high", null );
	}

	public function consolety_meta_box_markup( $object ) {
		wp_nonce_field( basename( __FILE__ ), "meta-box-nonce" );

		?>
        <div>
            <label for="consolety-export">Don't export this post to consolety</label>
			<?php
			$checkbox_value = get_post_meta( $object->ID, "consolety-export", true );

				?>
                <input style="vertical-align: -webkit-baseline-middle;" id="consolety-export" name="consolety-export" type="checkbox" value="on" <?=($checkbox_value=='on'?'checked':'')?>>
				<?php
			?>
        </div><br/><br/>
        <div>
            <label for="consolety-export">Export this post manually:</label>
            <div><button onclick="export_post('<?=$object->ID?>')" type="button" class="components-button is-primary">Export</button></div>
        </div>
		<?php
	}
	function save_consolety_meta_box($post_id, $post, $update)
	{
		if (!isset($_POST["meta-box-nonce"]) || !wp_verify_nonce($_POST["meta-box-nonce"], basename(__FILE__)))
			return $post_id;

		if(!current_user_can("edit_post", $post_id))
			return $post_id;

		if(defined("DOING_AUTOSAVE") && DOING_AUTOSAVE)
			return $post_id;

		$slug = "post";
		if($slug != $post->post_type)
			return $post_id;


		if(isset($_POST["consolety-export"]))
		{
			$post_export = $_POST["consolety-export"];
			update_post_meta($post_id, "consolety-export", $post_export);
		}else{
		    delete_post_meta( $post_id, "consolety-export");
		}


	}



}