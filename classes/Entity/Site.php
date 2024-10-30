<?php

namespace Consolety\Entity;

class Site {
	public static $instance;
	public $id;
	public $categories;
	public $network;
	public $network_label;
	public $language_label;
	public $status;
	public $points_used;
	public $points_total;
	public $points_per_day;
	public $booster_label;
	public $booster_expiration;
	public $posts_exported;
	public $next_available_link;

	public static function getInstance() {

		if ( ! isset( self::$instance ) ) {
			self::$instance = new static();
		}

		return self::$instance;
	}

	private function __construct() {
		$obj = get_option( 'consolety_site_object');
		if($obj){
			$site = unserialize($obj);
			foreach ($site as $key => $value){
				$this->$key = $value;
			}
		}

		return $this;
	}
	public function isActive():bool
	{
		if($this->status=='active'){
			return true;
		}
		return false;
	}
	public function isInactive():bool
	{

		if($this->status=='inactive'){
			return true;
		}
		return false;
	}
}