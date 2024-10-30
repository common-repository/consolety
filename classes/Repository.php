<?php
declare( strict_types=1 );

namespace Consolety;


class Repository {
	private static $instance;
	private $table_links;

	public static function getInstance() {

		if ( ! isset( self::$instance ) ) {
			self::$instance = new Repository();
		}

		return self::$instance;
	}

	public function __construct() {
		global $wpdb;
		$this->table_links = $wpdb->prefix . "consolety_links";
	}

	public function get_links( int $post_id ): array {
		global $wpdb;

		return $wpdb->get_results( $wpdb->prepare( "SELECT * FROM " . $this->table_links . " WHERE `post_id` = %d", $post_id ) );

	}

	public function reset_links() {
		global $wpdb;
		$wpdb->query( "TRUNCATE TABLE " . $this->table_links );
	}


	public function get_link_by_post_key( string $post_key ) {
		global $wpdb;

		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM " . $this->table_links . " WHERE `post_key` = %s", $post_key ) );

	}
	public function delete_link_by_post_key( string $post_key ) {
		global $wpdb;
		return $wpdb->delete( $this->table_links, ['post_key'=>$post_key]);

	}

	public function insert_link(
		int $post_id,
		string $post_key,
		string $hash,
		string $title,
		string $link,
		string $description,
		string $lastudpate
	) {
		global $wpdb;

		return $wpdb->insert( $this->table_links, [
			'post_id'     => $post_id,
			'post_key' =>$post_key,
			'hash'        => $hash,
			'title'       => $title,
			'link'        => $link,
			'description' => $description,
			'lastupdate'=>$lastudpate
		] );


	}

	public function update_link( int $id, string $hash, int $post_id, string $title, string $description, string $link, string $lastupdate) {
		global $wpdb;

		return $wpdb->update( $this->table_links,
			[
				'post_id'     => $post_id,
				'title'       => $title,
				'hash'=>$hash,
				'description' => $description,
				'link'        => $link,
				'lastupdate'=>$lastupdate
			],
			[
				'id' => $id
			]
		);

	}


	public function install() {
		global $wpdb;
		if ( $wpdb->get_var( "SHOW TABLES LIKE '$this->table_links'" ) != $this->table_links ) {
			$sql = "CREATE TABLE " . $this->table_links . " (
                     `id` int(11) NOT NULL AUTO_INCREMENT,
                        `post_id` int(11) NOT NULL,
                        `post_key` varchar(32) NOT NULL,
                        `hash` varchar(32) NOT NULL,
                        `title` varchar(256) NOT NULL,
                        `link` varchar(1024) NOT NULL,
                        `description` varchar(1024) NOT NULL,
                        `lastupdate` datetime DEFAULT NULL,
                            PRIMARY KEY (`id`),
                            UNIQUE KEY id (id)
								);";
			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $sql );
			add_site_option( "consolety_db_version", \Consolety\Consolety::$db_version );
		}
	}

	public function updates( $old_version, $new_version ) {
		global $wpdb;
		if ( $new_version > 2.0 && $old_version <= 2.0 ) {
			if ( $wpdb->get_var( "SHOW TABLES LIKE 'consolety_backlinks'" ) == 'consolety_backlinks' ) {//destroy old data
				$wpdb->query( "TRUNCATE TABLE consolety_backlinks" );
				delete_option( 'consolety_copyright' );
				delete_option( 'consolety_feed_mode' );
				delete_option( 'consolety_feed_url' );
				delete_option( 'consolety_event_lastupdate' );
				delete_option( 'consolety_sync_lastupdate' );
				delete_option( 'consolety_sync_last_id' );
				delete_option( 'consolety_site_network' );
				delete_option( 'consoletySeoDisp' );
				delete_option( 'consolety_post_types' );
				delete_post_meta_by_key( 'consolety_seo_posts' );
			}

		}
		if($old_version <= 4.0 && $new_version >= 4.0){
			if ( $wpdb->get_var( "SHOW TABLES LIKE 'consolety_backlinks'" ) == 'consolety_backlinks' ) { //remove old table completely if its exists
				$wpdb->query( "DROP TABLE `consolety_backlinks`" );
			}
			if ( $wpdb->get_var( "SHOW TABLES LIKE '".$wpdb->prefix."consolety_backlinks'" ) == 'consolety_backlinks' ) { //remove old table completely if its exists
				$wpdb->query( "DROP TABLE ` wp_consolety_backlinks `" );
				delete_option( 'consolety_copyright' );
				delete_option( 'consolety_feed_mode' );
				delete_option( 'consolety_feed_url' );
				delete_option( 'consolety_event_lastupdate' );
				delete_option( 'consolety_sync_lastupdate' );
				delete_option( 'consolety_sync_last_id' );
				delete_option( 'consolety_site_network' );
				delete_option( 'consoletySeoDisp' );
				delete_option( 'consolety_post_types' );
				delete_post_meta_by_key( 'consolety_seo_posts' );
			}

		$wpdb->query( "ALTER TABLE `".$this->table_links."` ADD `post_key` VARCHAR(32) NOT NULL AFTER `post_id`");
			update_site_option( 'consolety_db_version', $new_version);
			Consolety::$installed = get_site_option('consolety_install_finished');
			if(Consolety::$installed===false){
				Consolety::$installed = get_option('install_finished');
				if(Consolety::$installed){
					update_site_option('consolety_install_finished',true);
					Consolety::$installed = true;
				}
			}
		}

	}
}