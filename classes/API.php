<?php
declare( strict_types=1 );

namespace Consolety;


use Consolety\Entity\Link;

class API {

	private const CONSOLETY_URI = 'https://my.consolety.net';


	private const VERSION = 'v3';
	private const API = 'api';
	private const SITE_DIR = 'site';
	private const API_URI = self::CONSOLETY_URI . '/' . self::API . '/' . self::VERSION;
	private const CONNECT = self::API_URI . '/' . self::SITE_DIR . '/connect';
	private const SITE_INFO = self::API_URI . '/' . self::SITE_DIR . '/info';
	private const NEWS = self::API_URI . '/news';
	private const CLICK = self::API_URI . '/click';
	private const POSTS = self::API_URI . '/posts';
	private const REPORT = self::POSTS . '/report';
	private const FLUSH = self::POSTS . '/flush';

	private static $instance;
	private static $connected = null;


	public static function getInstance() {

		if ( ! isset( self::$instance ) ) {
			self::$instance = new API();
		}

		return self::$instance;
	}

	public function __construct() {
		add_action( 'rest_api_init', function () {
			register_rest_route( 'consolety/' . self::VERSION, '/connect', array(
				'methods'             => 'GET',
				'callback'            => [ $this, 'check_secret_key' ],
				'permission_callback' => function () {
					return isset( $_GET['secret_key'] ) && $_GET['secret_key'] === \Consolety\Initialization::getInstance()->get_secret_key();
				}
			) );
		} );
//		add_action( 'rest_api_init', function () {
//			register_rest_route( 'consolety/' . self::VERSION, '/can-post-remote', array(
//				'methods'             => 'GET',
//				'callback'            => [ $this, 'can_post_remote' ],
//				'permission_callback' => function () {
//					return isset( $_GET['secret_key'] ) && $_GET['secret_key'] === \Consolety\Initialization::getInstance()->get_secret_key();
//				}
//			) );
//		} );
		add_action( 'rest_api_init', function () {
			register_rest_route( 'consolety/' . self::VERSION, '/reset-posts', array(
				'methods'             => 'DELETE',
				'callback'            => [ $this, 'reset_consolety' ],
				'permission_callback' => function () {
					return isset( $_GET['secret_key'] ) && $_GET['secret_key'] === \Consolety\Initialization::getInstance()->get_secret_key();
				}
			) );
		} );
		add_action( 'rest_api_init', function () {
			register_rest_route( 'consolety/' . self::VERSION, '/links', array(
				'methods'             => 'POST',
				'callback'            => [ $this, 'manage_link' ],
				'permission_callback' => function () {
					return isset( $_GET['secret_key'] ) && $_GET['secret_key'] === \Consolety\Initialization::getInstance()->get_secret_key();
				}
			) );
		} );
		add_action( 'rest_api_init', function () {
			register_rest_route( 'consolety/' . self::VERSION, '/links', array(
				'methods'             => 'DELETE',
				'callback'            => [ $this, 'remove_link' ],
				'permission_callback' => function () {
					return isset( $_GET['secret_key'] ) && $_GET['secret_key'] === \Consolety\Initialization::getInstance()->get_secret_key();
				}
			) );
		} );

	}

	public function get_consolety_news() {
		$lastupdate = get_site_option( 'consolety_news_lastupdate' );
		if ( ! $lastupdate || $lastupdate + 3600 < time() ) {
			update_site_option( 'consolety_news_lastupdate', time() );
			$request = wp_remote_get( self::NEWS . '?secret_key=' . \Consolety\Initialization::getInstance()->get_secret_key() );
			//echo self::NEWS . '?secret_key=' . \Consolety\Initialization::getInstance()->get_secret_key();
			$code = wp_remote_retrieve_response_code( $request );
			if ( $code === 200 ) {
				$news_data         = json_decode( wp_remote_retrieve_body( $request ) )->data;
				$consolety_news_id = get_site_option( 'consolety_news_id' );
				if ( $news_data->id != $consolety_news_id ) {
					update_site_option( 'consolety_news_id', $news_data->id );
					update_site_option( 'consolety_news_class', $news_data->class );
					update_site_option( 'consolety_news_content', $news_data->content );

				}

				return $news_data;
			}
		} else {
			$news_data          = new \stdClass();
			$news_data->id      = get_site_option( 'consolety_news_id' );
			$news_data->class   = get_site_option( 'consolety_news_class' );
			$news_data->content = get_site_option( 'consolety_news_content' );

			return $news_data;
		}
	}

	public function manage_link() {

		//$data      = (object)json_decode( file_get_contents( "php://input" ) );
		$data      = (object) $_POST;
		$validator = new \Consolety\Utils\Validator();
		$validator->validate_keys( [
			'post_id',
			'post_key',
			'hash',
			'title',
			'link',
			'description',
			'lastupdate'
		], $data );
		if ( $validator->get_errors() ) {
			return new \WP_Error( 'invalid_data', 'One or more fields missed.', array(
				'status' => 422,
				'errors' => $validator->get_errors()
			) );
		}
		if ( get_post( (int) $data->post_id ) ) {

			$this->link(
				(int) $data->post_id,
				(string) wp_strip_all_tags( $data->post_key, true ),
				(string) wp_strip_all_tags( $data->hash, true ),
				(string) wp_strip_all_tags( $data->title, true ),
				(string) wp_strip_all_tags( $data->link, true ),
				(string) wp_strip_all_tags( $data->description ),
				(string) wp_strip_all_tags( $data->lastupdate )
			);

			return true;
		} else {
			return new \WP_Error( 'invalid_post_id', 'post wasn\'t found', array(
				'status'    => 404,
				'post_data' => $data
			) );
		}

	}

	public function remove_link() {
		if ( isset( $_GET['post_key'] ) && ! empty( strip_tags( $_GET['post_key'] ) ) && Repository::getInstance()->delete_link_by_post_key( strip_tags( $_GET['post_key'] ) ) ) {
			return true;
		} else {
			return new \WP_Error( 'invalid_hash', 'link wasn\'t found or removed', array(
				'status'    => 404,
				'post_data' => $_REQUEST
			) );
		}
	}

	public function link(
		$post_id,
		$post_key,
		$hash,
		$title,
		$link,
		$description,
		$lastupdate
	) {
		/**
		 * @var Link $current_link
		 */
		$current_link = \Consolety\Repository::getInstance()->get_link_by_post_key( $post_key );
		if (  $current_link  ) {
			if ( $current_link->hash != $hash ) {
				\Consolety\Repository::getInstance()->update_link( $current_link->id, $hash, $post_id, $title, $description, $link, $lastupdate );
			}

		} else {
			\Consolety\Repository::getInstance()->insert_link( $post_id, $post_key, $hash, $title, $link, $description, $lastupdate );
		}

	}

	public function post_updated( $post_ID, \WP_Post $post_after, \WP_Post $post_before ) {
		if ( ( get_site_option( 'consolety_connected', false ) &&
		       ( $post_before->ID != $post_after->ID ||
		         $post_before->post_title != $post_after->post_title ||
		         strip_tags( get_the_excerpt( $post_before ) ) != strip_tags( get_the_excerpt( $post_after ) ) ||
		         get_permalink( $post_before->ID ) != get_permalink( $post_after->ID ) ||
		         $post_before->ID != $post_after->ID
		       ) && $post_after->post_status == 'publish' )
		     && in_array( $post_after->post_type, Initialization::getInstance()->enabled_post_types )
		     && get_post_meta( $post_ID, 'consolety-export', true ) !== 'on' ) {
			$data[]   = [
				'post_id'         => $post_after->ID,
				'title'           => $post_after->post_title,
				'description'     => strip_tags( get_the_excerpt( $post_after ) ),
				'url'             => get_permalink( $post_after->ID ),
				'modification_at' => $post_after->post_modified,
				'categories'      => $this->findCategoriesByPostId( $post_after->ID ),
				'type'            => $post_after->post_type

			];
			$after_in = false;
			foreach ( wp_get_post_categories( $post_after->ID, [ 'fields' => 'ids' ] ) as $category_id ) {
				if ( in_array( $category_id, \Consolety\Initialization::getInstance()->get_categories() ) ) {
					$after_in = true;
					break;
				}
			}
			if ( $after_in && get_post_meta( $post_ID, 'consolety-export', true ) !== 'on' ) {
				$this->post( $data );
			} elseif ( get_post_meta( $post_after->ID, 'exported_to_consolety' ) && get_site_option( 'consolety_connected', false ) ) {
				$this->delete( $post_before->ID );
			}

		}
	}

	public function post_publish( $new_status, $old_status, \WP_Post $post ) {
		if ( $new_status == 'publish' && $old_status !== 'publish' && get_site_option( 'consolety_connected', false ) &&
		     get_post_meta( $post->ID, 'consolety-export', true ) !== 'on'
		     && in_array( $post->post_type, Initialization::getInstance()->enabled_post_types )
		) {
			$data[] = [
				'post_id'         => $post->ID,
				'title'           => $post->post_title,
				'description'     => strip_tags( get_the_excerpt( $post ) ),
				'url'             => get_permalink( $post->ID ),
				'modification_at' => $post->post_date,
				'categories'      => $this->findCategoriesByPostId( $post->ID ),
				'type'            => $post->post_type

			];
			$this->post( $data );
		} elseif ( $new_status != 'publish' && $old_status === 'publish' ) {
			$this->delete( $post->ID );
		}
	}

	public function post_delete( $post_id, $post ) {
		//&& get_post_meta( $post_ID,'consolety-export',true)!=='on' if it was exported before - it must be deleted.
		if ( get_site_option( 'consolety_connected', false ) ) {
			$this->delete( $post_id );
		}
	}

	//webhooks
	public function check_secret_key() {
		return ['status'=>(isset( $_GET['secret_key'] ) && $_GET['secret_key'] === \Consolety\Initialization::getInstance()->get_secret_key()),'version'=>round(Consolety::$db_version,2)];
	}

//	public function can_post_remote() {
//		return \Consolety\ContentMarketing::getInstance()->can_post_remote();
//	}


	//webhook calls
	public function check_site_connected(): bool {
		if ( ! is_admin() && get_site_option( 'last_site_connect_check_user' ) + 86400 < time() ) {
			return (bool) get_site_option( 'consolety_connected', false );
		}
		if ( self::$connected === null ) {
			update_site_option( 'update_site_connect_check_user', time() );
			$request = wp_remote_get( self::CONNECT . '?secret_key=' . \Consolety\Initialization::getInstance()->get_secret_key() );
			$code    = wp_remote_retrieve_response_code( $request );
			if ( $code === 200 ) {
				self::$connected = true;
				update_site_option( 'consolety_connected', true );

				return true;
			}
			self::$connected = false;
			update_site_option( 'consolety_connected', false );

			return false;
		} else {

			return self::$connected;
		}
	}

	//webhook calls
	public function get_site_data( $forceRefresh = false ): \Consolety\Entity\Site {

		$lastupdate = get_option( 'consolety_site_object_lastupdate' );
		if ( ! $lastupdate || $lastupdate < time() + 60 || $forceRefresh ) {
			$request = wp_remote_get( self::SITE_INFO . '?secret_key=' . \Consolety\Initialization::getInstance()->get_secret_key() );
			$site    = json_decode( wp_remote_retrieve_body( $request ) );
			if ( $request['response']['code'] == 200 ) {
				$validator = new \Consolety\Utils\Validator();
				$validator->validate_keys( [
					'id',
					'categories',
					'network',
					'network_label',
					'language_label',
					'status',
					'points_used',
					'points_total',
					'points_per_day',
					'booster_label',
					'booster_expiration',
					'posts_exported',
					'next_available_link'
				], $site->info );

				if ( ! $validator->get_errors() ) {
					update_option( 'consolety_site_object', serialize( (object) $site->info ) );
					update_option( 'consolety_site_object_lastupdate', time() );
				} else {
					echo 'Problem with sync found: <code>' . implode( '; ', $validator->get_errors() ) . '</code>';
				}
			}

		}

		return \Consolety\Entity\Site::getInstance();
	}

	public function post( array $params ): array {
		$message = null;
		$request = wp_remote_post( self::POSTS . '?secret_key=' . \Consolety\Initialization::getInstance()->get_secret_key(), [ 'body' => json_encode( [ 'posts' => $params ] ) ] );
		$code    = wp_remote_retrieve_response_code( $request );
		$body    = wp_remote_retrieve_body( $request );
		if ( ( $code !== 200 ) ) {
			$message = $body;
			$error   = true;
		} else {
			$error = null;
			$this->set_post_exported( $params );
		}

		return [ 'success' => ( $code === 200 ), 'error' => $error, 'message' => $message, 'data'=>json_encode( [ 'posts' => $params ] ) ];
	}

	public function delete( $post_id ): void {
		if ( get_post_meta( $post_id, 'exported_to_consolety', true ) ) {
			$uri      = self::POSTS . '/' . $post_id . '?secret_key=' . \Consolety\Initialization::getInstance()->get_secret_key();
			$response = wp_remote_request( $uri, [
				'method' => 'DELETE'
			] );
			if ( wp_remote_retrieve_response_code( $response ) === 200 ) {
				delete_post_meta( $post_id, 'exported_to_consolety' );
			}
		}
	}

	public function set_post_exported( $posts ) {
		foreach ( $posts as $post ) {
			update_post_meta( $post->post_id, 'exported_to_consolety', true );
		}
	}

	public function consolety_export() {
		$error   = null;
		$offset  = (int) $_POST['offset'] ?? 0;
		$total   = (int) $_POST['total'] ?? 0;
		$skipped = (int) $_POST['skipped'] ?? 0;

		if ( isset( $_POST['date'] ) && current_user_can( 'manage_options' ) ) {
			$categories = Initialization::getInstance()->get_categories();
			if ( ! $categories ) {
				$categories = [];
			}
			$args = [
				'posts_per_page'   => 5,
				'post_type'        => $this->get_post_types_selected(),//'post',
				'post_status'      => 'publish',
				'offset'           => $offset,
				'date_query'       => array(
					'after' => date( 'Y-m-d', strtotime( $_POST['date'] ) )
				),
				'category__not_in' => [ 1 ] //remove uncategorized
			];
			if ( ! Initialization::getInstance()->get_isAllcategories() ) {
				$args['category__in'] = $categories;
			}
			$posts = new \WP_Query( $args );
			$data  = [];
			$total = $posts->found_posts;

			foreach ( $posts->posts as $post ) {
				/** @var \WP_Post $post */
				$post             = (object) $post;
				$id               = $post->ID;
				$title            = strip_tags( $post->post_title );
				$desc             = strip_tags( get_the_excerpt( $post ) );
				$url              = get_permalink( $post->ID );
				$date             = $post->post_modified;
				$categories_found = $this->findCategoriesByPostId( $post->ID );
				if (  empty( $title ) || empty( $desc ) || empty( $url ) || empty( $date ) || ! $categories_found || empty( $post->post_type)) {
					$skipped ++;
					continue;
				}


				$data[] = [
					'post_id'         => $id,
					'title'           => $title,
					'description'     => $desc,
					'url'             => $url,
					'modification_at' => $date,
					'categories'      => $categories_found,
					'type'            => $post->post_type

				];
				update_post_meta( $post->ID, 'exported_to_consolety', true );
			}
			if($data){
				$response = $this->post( $data );
				$error    = $response['error'];
				$message =  $response['message'];
				$post_data =  $response['data'];
			}else{
				$error    = false;
				$message =  'An empty request was sent, probably one of the post types does not match the original WordPress post structure';
				$post_data =  $data;
			}


		}
		wp_die( json_encode( array(
			'offset'  => $offset + 5,
			'total'   => $total,
			'error'   => $error,
			'skipped' => $skipped,
			'message' => $message,
			'post_data'=>$post_data
		) ) );
	}
	public function get_post_types_selected(): array
	{
		$post_types = Initialization::getInstance()->enabled_post_types;
		return $post_types;
	}

	public function consolety_export_single() {
		$error   = null;
		$post_id = (int) $_POST['post_id'] ?? 0;

		if ( $post_id && current_user_can( 'manage_options' ) ) {

			$post = get_post( $post_id );
			if ( $post ) {
				/** @var \WP_Post $post */
				$id               = $post->ID;
				$title            = strip_tags( $post->post_title );
				$desc             = strip_tags( get_the_excerpt( $post ) );
				$url              = get_permalink( $post->ID );
				$date             = $post->post_modified;
				$categories_found = $this->findCategoriesByPostId( $post->ID );
				if ( ! intval( $id ) || empty( $title ) || empty( $desc ) || empty( $url ) || empty( $date ) || ! $categories_found ) {
					wp_die( json_encode( array(
						'error' => true,
						'message'=>'Post has no categories (uncategorized) or invalid'
					) ) );
				}


				$data[] = [
					'post_id'         => $id,
					'title'           => $title,
					'description'     => $desc,
					'url'             => $url,
					'modification_at' => $date,
					'categories'      => $categories_found,
					'type'            => $post->post_type

				];
				update_post_meta( $post->ID, 'exported_to_consolety', true );


				$response = $this->post( $data );
				$error    = $response['error'];
				wp_die( json_encode( array(
					'error'   => $error,
					'message' => $response['message']
				) ) );
			}
			wp_die( json_encode( array(
				'error'   => true,
				'message' => "Post wasn't found"
			) ) );
		}
		wp_die( json_encode( array(
			'error'   => true,
			'message' => "Post id is invalid or you don't have permission for this action"
		) ) );
	}

	private function findCategoriesByPostId( int $postId ) {
		$cats = wp_get_post_categories( $postId, array( 'fields' => 'names' ) );
		if ( count( $cats ) === 1 ) {
			if($cats[0]==get_the_category_by_ID( 1)){
				$cats=[];
			}
		}
			if ( count( $cats ) > 0 ) {

			$cats = array_slice( $cats, 0, 5 );
		}
		$tags = wp_get_post_tags( $postId, array( 'fields' => 'names' ) );
		if ( count( $tags ) > 0 ) {
			$tags = array_slice( $tags, 0, 5 );
		}

		if ( empty( $tags ) && empty( $cats ) ) {
			return false;
		}

		return array_merge( $tags, $cats );
	}


	public function consolety_click_record() {
		if ( ! empty( $_POST['hash'] ) ) {
			wp_remote_post( self::CLICK . '?secret_key=' . \Consolety\Initialization::getInstance()->get_secret_key(), array(
				'body' => array(
					'hash' => $_POST['hash'],
					'ip'   => $_SERVER['REMOTE_ADDR']
				)
			) );
		}
	}


	public function flush_my_posts_on_consolety(): void {
		$last_flush = get_site_option( 'consolety_last_flush' );
		if ( current_user_can( 'manage_options' ) && ( ! $last_flush || $last_flush + 86400 * 7 < time() ) ) {
			$response = wp_remote_get( self::FLUSH . '/?secret_key=' . \Consolety\Initialization::getInstance()->get_secret_key(), [ 'method' => 'DELETE' ] );
			if ( ! is_wp_error( $response ) ) {
				update_option( 'consolety_last_flush', time() );
				delete_post_meta_by_key( 'exported_to_consolety' );
			}

		}
	}


	public function consolety_report() {
		if ( isset( $_POST['post_id'] ) && (int) $_POST['post_id'] && ! empty( $_POST['hash'] ) && ! empty( $_POST['report'] ) ) {
			$uri = self::REPORT . '?secret_key=' . \Consolety\Initialization::getInstance()->get_secret_key();
			wp_remote_request( $uri, [
				'method' => 'PUT',
				'body'   => array(
					'hash'        => $_POST['hash'],
					'description' => $_POST['report'],
					'ip'          => $_SERVER['REMOTE_ADDR']
				)
			] );

		}


		wp_die(); // this is required to terminate immediately and return a proper response
	}
}