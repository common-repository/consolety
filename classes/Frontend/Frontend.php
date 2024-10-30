<?php


namespace Consolety\Frontend;


use Consolety\Admin\Tables\ExportedPosts;
use Consolety\API;use Consolety\Consolety;use Consolety\Initialization;use const Consolety\BASEURL;

class Frontend {
		private static $instance;

			public static function getInstance(){

				if (!isset(self::$instance)) {
					self::$instance = new static();
				}
				return self::$instance;
			}
			public function page_init()
			{
				add_menu_page('Consolety', 'Consolety', 'manage_options', 'consolety', [$this, 'consolety_seo_settings'], BASEURL . 'img/consolety-logo-eye.png');
			}

			    public function consolety_seo_settings()
		    {
		        if ( ! current_user_can( 'manage_options' ) ) {
		            return;
		        }

		        ?>
		        <div class="consolety consolety-<?=(!API::getInstance()->check_site_connected() || !Consolety::$installed)?'install':'installed'?>">
		        <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
		        <div class="tabs">
		        <?php
		        if(!API::getInstance()->check_site_connected() || !Consolety::$installed){
		            $this->display_install_block();
		        }else{
		            $this->display_active_block();
		        }
		 ?>
		        </div>




		<?php
		    }
		    private function display_install_block(){
				?>
		<input type="radio" id="tab1" name="tab-control" <?=(!isset($_GET['step'])?'checked':'')?>>
		  <input type="radio" id="tab2" name="tab-control" <?=((isset($_GET['step']) && $_GET['step']==2)?'checked':'')?>>
		  <input type="radio" id="tab3" name="tab-control" <?=((isset($_GET['step']) && $_GET['step']==3)?'checked':'')?>>
		  <ul>
		    <li title="Main Settings"><label for="tab1" role="button"><svg viewBox="0 0 24 24"><path d="M14,2A8,8 0 0,0 6,10A8,8 0 0,0 14,18A8,8 0 0,0 22,10H20C20,13.32 17.32,16 14,16A6,6 0 0,1 8,10A6,6 0 0,1 14,4C14.43,4 14.86,4.05 15.27,4.14L16.88,2.54C15.96,2.18 15,2 14,2M20.59,3.58L14,10.17L11.62,7.79L10.21,9.21L14,13L22,5M4.93,5.82C3.08,7.34 2,9.61 2,12A8,8 0 0,0 10,20C10.64,20 11.27,19.92 11.88,19.77C10.12,19.38 8.5,18.5 7.17,17.29C5.22,16.25 4,14.21 4,12C4,11.7 4.03,11.41 4.07,11.11C4.03,10.74 4,10.37 4,10C4,8.56 4.32,7.13 4.93,5.82Z"/>
		</svg><br><span>Install Key</span></label></li>
		    <li title="Delivery Contents"><label for="tab2" role="button"><svg viewBox="0 0 24 24"><path d="M2,10.96C1.5,10.68 1.35,10.07 1.63,9.59L3.13,7C3.24,6.8 3.41,6.66 3.6,6.58L11.43,2.18C11.59,2.06 11.79,2 12,2C12.21,2 12.41,2.06 12.57,2.18L20.47,6.62C20.66,6.72 20.82,6.88 20.91,7.08L22.36,9.6C22.64,10.08 22.47,10.69 22,10.96L21,11.54V16.5C21,16.88 20.79,17.21 20.47,17.38L12.57,21.82C12.41,21.94 12.21,22 12,22C11.79,22 11.59,21.94 11.43,21.82L3.53,17.38C3.21,17.21 3,16.88 3,16.5V10.96C2.7,11.13 2.32,11.14 2,10.96M12,4.15V4.15L12,10.85V10.85L17.96,7.5L12,4.15M5,15.91L11,19.29V12.58L5,9.21V15.91M19,15.91V12.69L14,15.59C13.67,15.77 13.3,15.76 13,15.6V19.29L19,15.91M13.85,13.36L20.13,9.73L19.55,8.72L13.27,12.35L13.85,13.36Z" />
		</svg><br><span>Export posts</span></label></li>
		<li title="Returns"><label for="tab3" role="button"><svg viewBox="0 0 24 24">
		    <path d="M11,9H13V7H11M12,20C7.59,20 4,16.41 4,12C4,7.59 7.59,4 12,4C16.41,4 20,7.59 20,12C20,16.41 16.41,20 12,20M12,2A10,10 0 0,0 2,12A10,10 0 0,0 12,22A10,10 0 0,0 22,12A10,10 0 0,0 12,2M11,17H13V11H11V17Z" />
		</svg><br><span>Meet system details</span></label></li>
		  </ul>

		  <div class="slider"><div class="indicator"></div></div>
		  <div class="content">
		    <section><?php Initialization::getInstance()->display_key_block()?></section>
		    <section><?php Initialization::getInstance()->display_export_block()?></section>
		    <section><?php Initialization::getInstance()->display_details_block();?></section>
		       </div>
		<?php
		    }
		    private function display_active_block(){
		        ?>

		<input type="radio" id="tab1" class="tab-main" name="tab-control" <?=(!isset($_GET['tab'])?'checked':'')?>>
		  <input type="radio" id="tab2" class="tab-design" name="tab-control" <?=((isset($_GET['tab']) && $_GET['tab']=='design')?'checked':'')?>>
		  <input type="radio" id="tab3" class="tab-details" name="tab-control"<?=((isset($_GET['tab']) && $_GET['tab']=='details')?'checked':'')?>>
		  <input type="radio" id="tab4" class="tab-links" name="tab-control"<?=((isset($_GET['tab']) && $_GET['tab']=='links')?'checked':'')?>>
		  <ul>
		    <li><label onclick="window.location.hash = 'main';" for="tab1" role="button"><svg viewBox="0 0 24 24"><path d="M14,2A8,8 0 0,0 6,10A8,8 0 0,0 14,18A8,8 0 0,0 22,10H20C20,13.32 17.32,16 14,16A6,6 0 0,1 8,10A6,6 0 0,1 14,4C14.43,4 14.86,4.05 15.27,4.14L16.88,2.54C15.96,2.18 15,2 14,2M20.59,3.58L14,10.17L11.62,7.79L10.21,9.21L14,13L22,5M4.93,5.82C3.08,7.34 2,9.61 2,12A8,8 0 0,0 10,20C10.64,20 11.27,19.92 11.88,19.77C10.12,19.38 8.5,18.5 7.17,17.29C5.22,16.25 4,14.21 4,12C4,11.7 4.03,11.41 4.07,11.11C4.03,10.74 4,10.37 4,10C4,8.56 4.32,7.13 4.93,5.82Z"/>
		</svg><br><span>Main Settings</span></label></li>
		    <li><label onclick="window.location.hash = 'design';" for="tab2" role="button"><svg viewBox="0 0 24 24"><path d="M2,10.96C1.5,10.68 1.35,10.07 1.63,9.59L3.13,7C3.24,6.8 3.41,6.66 3.6,6.58L11.43,2.18C11.59,2.06 11.79,2 12,2C12.21,2 12.41,2.06 12.57,2.18L20.47,6.62C20.66,6.72 20.82,6.88 20.91,7.08L22.36,9.6C22.64,10.08 22.47,10.69 22,10.96L21,11.54V16.5C21,16.88 20.79,17.21 20.47,17.38L12.57,21.82C12.41,21.94 12.21,22 12,22C11.79,22 11.59,21.94 11.43,21.82L3.53,17.38C3.21,17.21 3,16.88 3,16.5V10.96C2.7,11.13 2.32,11.14 2,10.96M12,4.15V4.15L12,10.85V10.85L17.96,7.5L12,4.15M5,15.91L11,19.29V12.58L5,9.21V15.91M19,15.91V12.69L14,15.59C13.67,15.77 13.3,15.76 13,15.6V19.29L19,15.91M13.85,13.36L20.13,9.73L19.55,8.72L13.27,12.35L13.85,13.36Z" />
		</svg><br><span>Design</span></label></li>
		<li><label onclick="window.location.hash = 'details';" for="tab3" role="button">
		<svg viewBox="0 0 24 24">
		    <path d="M11,9H13V7H11M12,20C7.59,20 4,16.41 4,12C4,7.59 7.59,4 12,4C16.41,4 20,7.59 20,12C20,16.41 16.41,20 12,20M12,2A10,10 0 0,0 2,12A10,10 0 0,0 12,22A10,10 0 0,0 22,12A10,10 0 0,0 12,2M11,17H13V11H11V17Z" />
		</svg>
		<br><span>Details</span></label></li>
		<li><label onclick="window.location.hash = 'links';" for="tab4" role="button">
		<svg viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg"><defs><style>.cls-1{fill:none;stroke:currentColor;stroke-width:2px;}</style></defs><title/><g data-name="94-List" id="_94-List"><rect class="cls-1" height="30" rx="2" ry="2" width="30" x="1" y="1"/><line class="cls-1" x1="11" x2="27" y1="9" y2="9"/><line class="cls-1" x1="11" x2="27" y1="16" y2="16"/><line class="cls-1" x1="11" x2="27" y1="23" y2="23"/><rect class="cls-1" height="2" width="2" x="6" y="8"/><rect class="cls-1" height="2" width="2" x="6" y="15"/><rect class="cls-1" height="2" width="2" x="6" y="22"/></g></svg>
		<br><span>Exported Posts</span></label></li>
		  </ul>

		  <div class="slider"><div class="indicator"></div></div>
		  <div id="consolety-notification-block"></div>
		  <div class="content">
		    <section><?php SettingsMainBlock::getInstance()->display_main_settings()?></section>
		    <section><?php SettingsDesignBlock::getInstance()->display_design_settings()?></section>
		    <section><?php Initialization::getInstance()->display_details_block(true);?></section>
		    <section id="exported-posts"><?php ExportedPosts::getInstance()->display();?></section>
		       </div>
		       <?php
		    }
}