<?php
declare( strict_types=1 );

namespace Consolety\Frontend;


class DisplayBlock {
	private static $instance;

	public static function getInstance() {

		if ( ! isset( self::$instance ) ) {
			self::$instance = new static();
		}

		return self::$instance;
	}

	public function __construct() {
		add_filter( 'the_content', [ $this, 'display_links' ], 99 );
	}

	function display_links( $content ) {
		$content .= $this->display_seo();

		return $content;
	}

	public function display_seo() {
		if ( is_home() || is_front_page() || !\Consolety\API::getInstance()->check_site_connected()) {
			return '';
		}

		$post_id = get_the_ID();
		if ( \Consolety\Entity\Site::getInstance()->isActive() && $post_id ) {

			$feeds = \Consolety\Repository::getInstance()->get_links( $post_id );

			if ( ! count( $feeds ) ) {
				return '';
			}

		} else {
			return '';
		}
		$show = 0;
		$HTML = '<div id="consolety-seo-block">';
		/**
		 * @var \Consolety\Entity\Link $f
		 */
		foreach ( $feeds as $key => $f ) {

			if ( $key == 3 ) {
				$HTML .= '</div>';
			}
			if ( ! $key || $key == 3 ) {
				$HTML .= '<div class="consolety_row">';
			}
			$HTML .= '<div class="consolety_col-md-4">
           <h4 class="consolety_h4">' . $f->title . '<span class="consolety_flag"><img onclick="consolety_report(\'' . $f->hash . '\',\'' . preg_replace( '/[^A-Za-z0-9\. -]/', '', $f->title ) . '\')" title="' . SettingsDesignBlock::getInstance()->get_styles()['button']['report'] . '" alt="' .SettingsDesignBlock::getInstance()->get_styles()['button']['report'] . '" src="' . plugins_url( 'img/flag.png', \Consolety\BASEFILE ) . '"></span></h4>
            <div class="consolety_decription">' . SettingsDesignBlock::getInstance()->mbCutString($f->description,(empty(SettingsDesignBlock::getInstance()->get_styles()['description']['length'])?250:(int)SettingsDesignBlock::getInstance()->get_styles()['description']['length'])) . '</div>
            <div class="consolety_btn_div"><a class="consolety_btn" onclick="consolety_click_record(\'' . $f->hash . '\')" href="' . $f->link . '"  role="button">' . SettingsDesignBlock::getInstance()->get_styles()['button']['text'] . ' »</a></div>
          
          </div>';
			$show ++;


		}

		$HTML .= '<div class="consolety_clearfix"></div></div>';

		if ( \Consolety\Entity\Site::getInstance()->network === 0 ) {

			$HTML .= '<small><a href="https://consolety.net/" target="_blank">Powered by Consolety.net</a></small>';
		}

		$HTML .= '</div>';
		if ( ! $show ) {
			$HTML = '';
		}

		return $HTML;
	}

}