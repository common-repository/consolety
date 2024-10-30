<?php

namespace Consolety\Admin\Tables;


if ( ! class_exists( 'Link_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class ExportedPosts extends \WP_List_Table {
	protected static $instance;

	public static function getInstance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new static();
		}

		return self::$instance;
	}

	public function __construct() {
		parent::__construct( array(

			'singular' => 'Exported Post',     //singular name of the listed records

			'plural' => 'Exported Posts',    //plural name of the listed records

			'ajax' => false,

		) );
		$this->prepare_items();

	}

	public function get_columns() {
		$columns = [
			'id'              => 'ID',
			'title'           => 'Title',
			'content'         => 'Content',
			'url'             => 'Url',
			'modification_at' => 'Date',
		];

		return $columns;
	}

	public function column_id( $item ) {
		return $item->ID;
	}

	public function column_title( $item ) {
		return strip_tags( $item->post_title );
	}

	public function column_content( $item ) {
		return strip_tags( get_the_excerpt( $item ) );
	}

	public function column_modification_at( $item ) {
		return $item->post_modified;
	}


	public function column_url( $item ) {
		return '<a target="_blank" href="' . $item->guid . '">' . $item->guid . '</a>';
	}

	public function get_sortable_columns() {
		return [
		];
	}

	/**
	 * Prepare table list items.
	 */
	public function prepare_items() {
		$per_page = 10;

		$args  = array(
			'meta_query' => array(
				array(
					'key'     => 'exported_to_consolety',
					'value'   => 1,
					'compare' => '='
				)
			)
		);
		$query = new \WP_Query( $args );

		$this->items           = $query->get_posts();
		$count                 = $query->post_count;
		$this->_column_headers = array( $this->get_columns(), [], $this->get_sortable_columns() );
		// Set the pagination.
		$this->set_pagination_args(
			array(
				'total_items' => $count,
				'per_page'    => $per_page,
				'total_pages' => ceil( $count / $per_page ),
			)
		);
	}
}
