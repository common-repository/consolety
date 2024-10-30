<?php
//
//
//namespace Consolety;
//
//
//class ContentMarketing {
//	private static $instance;
//	private  $remote_post;
//
//	public function __construct() {
//		add_action('wp_loaded', [$this, 'consolety_post_remote'], 0);
//		$this->remote_post = get_option('consolety_remote_post');
//	}
//	public static function getInstance(){
//
//		if (!isset(self::$instance)) {
//			self::$instance = new ContentMarketing();
//		}
//		return self::$instance;
//	}
//
//	function consolety_post_remote()
//	{
//
//		if (isset($_POST['create-post']) && isset($_POST['secret-key']) && !empty($_POST['secret-key']) && Consolety::getInstance()->get_secret_key() == $_POST['secret-key'] && $this->get_remote_post()=='on') {
//			$status = 'draft';
//			$output=array();
//			if (isset($_POST['consolety-publish']) && $_POST['consolety-publish'] == "publish") {
//				$status = 'publish';
//			}
//			$_POST['consolety-content'] = trim($_POST['consolety-content']);
//
//			// Go ahead if the post_content contains more than 1500 characters (
//			// INFO: This ensures that the post_content contains at least one base64 image
//			if (isset($_POST['consolety-content']) && strlen($_POST['consolety-content']) > 1500) {
//				// Collect and prepare the base64 information of each article for further processing
//				$mime_types_map = array('png' => 'image/png', 'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg');
//				$basesf_array = self::parse_code($_POST['consolety-content'], $mime_types_map);
//// Go ahead if we collect at least one embedded base64 image in the article
//				if ($basesf_array) {
//
//					foreach ($basesf_array as $key => $baseimage) {
//
//						// Define a fictional filename for the media attachment
//						// TODO: Collect the alt or title attributes for a better filename
//						$filename = sanitize_title(substr($baseimage['base64'], 0, 15)) . '.' . $baseimage['extension'];
//// For the forthcoming upload procedure, We have to create a physical file by decoding the base64 image
//
//						if (!function_exists('wp_handle_sideload')) {
//
//							require_once(ABSPATH . 'wp-admin/includes/file.php');
//
//						}
//						$upload_dir = wp_upload_dir();
//
//						// @new
//						$upload_path = str_replace('/', DIRECTORY_SEPARATOR, $upload_dir['path']) . DIRECTORY_SEPARATOR;
//						// Without that I'm getting a debug error!?
//						if (!function_exists('wp_get_current_user')) {
//
//							require_once(ABSPATH . 'wp-includes/pluggable.php');
//
//						}
//
//						$hashed_filename = md5($filename . microtime()) . '_' . $filename;
//						if ($image_upload = @file_put_contents($upload_path . $hashed_filename, base64_decode($baseimage['base64']))) {
//							// @new
//							$file = array();
//							$file['error'] = '';
//							$file['tmp_name'] = $upload_path . $hashed_filename;
//							$file['name'] = $hashed_filename;
//							$file['type'] = mime_content_type($upload_path . $hashed_filename);
//							$file['size'] = filesize($upload_path . $hashed_filename);
//
//							// upload file to server
//							// @new use $file instead of $image_upload
//							$file_return = wp_handle_sideload($file, array('test_form' => false));
//
//							$filename = $file_return['file'];
//							$attachment = array(
//								'post_mime_type' => $file_return['type'],
//								'post_title' => preg_replace('/\.[^.]+$/', '', basename($filename)),
//								'post_content' => '',
//								'post_status' => 'inherit',
//								'guid' => $upload_dir['url'] . '/' . basename($filename)
//							);
//							$attach_id = wp_insert_attachment($attachment, $filename, 0);
//							if ($attach_id) {
//								$_POST['consolety-content'] = str_replace($baseimage['full'], wp_get_attachment_url($attach_id), $_POST['consolety-content']);
//							}
//
//
//						}
//
//					}
//				}
//			}
//
//
//			// $post_content = str_replace( "$base64", $attachment_url, $post->post_content );
//			$cat_ids = array();
//			$cats = explode(',', $_POST['consolety-categories']);
//			if (!count($cats)) {
//				$cats[] = $_POST['consolety-categories'];
//			}
//			if (count($cats)) {
//				require_once(ABSPATH . 'wp-admin/includes/taxonomy.php');
//				foreach ($cats as $cat) {
//					$cat_ids[] = wp_create_category($cat);
//				}
//			}
//
//
//			$post_id = wp_insert_post(array(
//				'post_status' => $status,
//				'post_title' => $_POST['consolety-title'],
//				'post_content' => trim($_POST['consolety-content']), // take images from base64 and upload them to wp
//				'post_category' => $cat_ids,
//				'tags_input' => $_POST['consolety-tags']
//			));
//
//			if($post_id){
//				add_post_meta($post_id,'market_post_key',$_POST['market_post_key']);
//				$output['post_id']=$post_id;
//				$output['post_url']=get_permalink($post_id);
//				$output['post_status']=$status;
//			}
//			if ($post_id && count($_FILES) && isset($_FILES["file"])) {
//				$upload = wp_upload_bits($_FILES["file"]["name"], null, file_get_contents($_FILES["file"]["tmp_name"]));
//
//				$filename = $upload['file'];
//
//				$wp_filetype = wp_check_filetype($filename, null);
//
//				$attachment = array(
//					'post_mime_type' => $wp_filetype['type'],
//
//					'post_title' => sanitize_file_name($filename),
//
//					'post_content' => '',
//
//					'post_status' => 'inherit'
//
//				);
//
//				$attach_id = wp_insert_attachment($attachment, $filename, $post_id);
//
//				require_once(ABSPATH . 'wp-admin/includes/image.php');
//
//				$attach_data = wp_generate_attachment_metadata($attach_id, $filename);
//
//				wp_update_attachment_metadata($attach_id, $attach_data);
//
//				set_post_thumbnail($post_id, $attach_id);
//				if($attach_id){
//					$output['post_image_url']=wp_get_attachment_image_url($attach_id);
//				}
//
//			}
//
//			die(json_encode($output));
//		}
//
//	}
//
//	/**
//	 * Check whether a media file with the 'filebasename' exist
//	 *
//	 * @param string $content The post_content
//	 * @param integer $post_id The post_id (needed for the array structure)
//	 * @param array $mime_types_map An array with mime-types and extensions
//	 * @return array Return an array with the collected base64 information
//	 */
//	function parse_code($content, $mime_types_map)
//	{
//		$dom = @DOMDocument::loadHTML($content);
//		$tags = @$dom->getElementsByTagName('img');
//
//		$basesf_array = array();
//		if ($tags) {
//			foreach ($tags as $tag) {
//
//				$tag = $tag->getAttribute('src');
//
//				$base64 = explode(',', $tag);
//				$data = explode(';', $base64[0]);
//				$type = explode(':', $data[0]);//"data[0]=>data:image/jpeg
//				if (isset($type[1]) && isset($base64[1])) {
//					$basesf_array[] = self::prepare_matches($type[1], $base64[1], $tag, $mime_types_map);
//
//				}
//
//
//			}
//		}
//		if (empty($basesf_array)) {
//			unset($basesf_array);
//		}
//		return $basesf_array;
//	}
//
//	/**
//	 * Create array structure based on the base64 matches
//	 *
//	 * @param array $matches The matches from the previous regex
//	 * @param array $mime_types_map An array with mapped mime-types and file extensions
//	 * @return array Return an array with base64 informations
//	 */
//	static function prepare_matches($mime_type, $base64, $full, $mime_types_map)
//	{
//		return
//			array(
//				'extension' => self::get_file_extension($mime_types_map, $mime_type),
//				'mime-type' => $mime_type,
//				'base64' => $base64,
//				'full' => $full,
//			);
//	}
//
//	/**
//	 * Get the file extension by the array of mapped mime-types
//	 *
//	 * @param array $mime_types_map mime-type and extension mapping
//	 * @param array $mime_content_type the mime-type from embedded base64 code
//	 * @return string Return the matched file extension
//	 */
//	static function get_file_extension($mime_types_map, $mime_content_type)
//	{
//		foreach ($mime_types_map as $ext => $mime_type) {
//			if ($mime_type === $mime_content_type) {
//				return $ext;
//			}
//		}
//	}
//
//	/**
//	 * @return false|mixed|void
//	 */
//	public function get_remote_post() {
//		return $this->remote_post;
//	}
//
//	/**
//	 * @param false|mixed|void $remote_post
//	 */
//	public function set_remote_post( $remote_post ) {
//		$this->remote_post = $remote_post;
//	}
//
//	public function can_post_remote(){
//		if($this->remote_post=='on'){
//			return true;
//		}
//		return false;
//	}
//
//}