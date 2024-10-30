<?php


namespace Consolety;


class Ajax {
	private static $instance;

	public static function getInstance(){

		if (!isset(self::$instance)) {
			self::$instance = new static();
		}
		return self::$instance;
	}
	public function __construct() {
		add_action('wp_ajax_flush_consolety', array($this, 'flush_consolety'));
		add_action('wp_ajax_consolety_report', array($this, 'consolety_report'));
		add_action('wp_ajax_consolety_export', array($this, 'consolety_export'));
		add_action('wp_ajax_consolety_export_single', array($this, 'consolety_export_single'));
		add_action('wp_ajax_save_categories', array($this, 'save_categories'));
		add_action('wp_ajax_nopriv_consolety_report', array($this, 'consolety_report'));
		add_action('wp_ajax_consolety_click_record', array($this, 'consolety_click_record'));
		add_action('wp_ajax_nopriv_consolety_click_record', array($this, 'consolety_click_record'));
	}

	public function save_categories(){

		if (  current_user_can( 'manage_options' ) ) {

			if(isset( $_POST['categories'])){
					update_option( \Consolety\Initialization::$opt_categories,  $_POST['categories']);
			}
			if(isset( $_POST[\Consolety\Initialization::$opt_post_types])){
					update_option( \Consolety\Initialization::$opt_post_types,  $_POST[\Consolety\Initialization::$opt_post_types]);
			}
			if(isset($_POST['consolety_no_sync'])){
				update_option( 'consolety_no_sync',$_POST['consolety_no_sync']);
			}
			if(isset($_POST['all_categories']) && $_POST['all_categories']=='true'){
				update_option( Initialization::$opt_all_categories,true);
			}else{
				update_option( Initialization::$opt_all_categories,false);
			}
			update_option( 'consolety_export_finished_once', true); //if user skipped export
			return true;
		}
		return false;
	}
	public function consolety_export(){
		\Consolety\API::getInstance()->consolety_export();

		update_option( 'consolety_export_finished_once', true);
		wp_die(json_encode( ['offset' => $_POST['offset'] + 1, 'total' => 10, 'error' => null]));
	}
	public function consolety_export_single(){
		\Consolety\API::getInstance()->consolety_export_single();
	}
	public function flush_consolety(){
		\Consolety\API::getInstance()->flush_my_posts_on_consolety();
	}
	public function consolety_report(){
		\Consolety\API::getInstance()->consolety_report();
	}
	public function consolety_click_record(){
		\Consolety\API::getInstance()->consolety_click_record();
	}
}