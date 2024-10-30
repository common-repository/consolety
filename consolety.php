<?php
declare(strict_types=1);
namespace Consolety;
use Consolety\Admin\Posts\ConsoletyExportCheckbox;
use Consolety\Admin\Tables\ExportedPosts;
use Consolety\Frontend\DisplayBlock;
use Consolety\Frontend\Frontend;
use Consolety\Frontend\SettingsDesignBlock;
use Consolety\Frontend\SettingsMainBlock;
/**
 * Plugin Name: Consolety - SEO plugin for Traffic, Authority & Backlinks
 * Description: Consolety Plugin - Link Exchange plugin for WordPress creates backlinks automatically based on matching titles, categories, tags, content & language.
 * Version: 4.0.2
 * Author: Consolety
 * Author URI: https://profiles.wordpress.org/brainiacx
 *
 */
define(__NAMESPACE__ . '\BASEPATH', dirname(__FILE__).'/');
define(__NAMESPACE__ . '\BASEFILE', __FILE__);
define(__NAMESPACE__ . '\BASEURL', plugin_dir_url(__FILE__));
require_once __DIR__ . '/vendor/autoload.php';

register_activation_hook( __FILE__, ['\\Consolety\\Consolety','install'] );
class Consolety {
    private static $instance;
    public static $db_version = 4.0;
    public static $installed;

    public static function getInstance(){

             if (!isset(self::$instance)) {
                 self::$instance = new Consolety();
             }
             return self::$instance;
         }


    public function __construct()
    {
        $api = API::getInstance();
        Ajax::getInstance();
        Initialization::getInstance();
        DisplayBlock::getInstance();
        ConsoletyExportCheckbox::getInstance();
        //ContentMarketing::getInstance();
        SettingsDesignBlock::getInstance();
        add_action('admin_menu', [Frontend::getInstance(), 'page_init']);
        add_action('wp_footer', [$this, 'consolety_scripts'], 100);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin']);
        add_action('plugins_loaded', [$this, 'consolety_update_db_check']);
        add_action('plugins_loaded', [$this, 'consolety_flush_if_needed']);
        add_action( 'admin_init', [$this, 'register_settings'] );
        add_action( 'post_updated', [$api, 'post_updated'],10,3 );
        if(!get_option( 'consolety_no_sync',false)){
            add_action( 'transition_post_status', [$api,'post_publish'], 10, 3 );
        }
        add_action( 'delete_post', [$api,'post_delete'], 10, 2 );

        update_site_option('consolety_last_activity',time());
        self::$installed = get_site_option('consolety_install_finished');

    }

    public function register_settings(){
		register_setting( 'consolety-key-group', 'consoletySeoKey' );
		register_setting( 'consolety-install-group', 'consolety_install_finished' );
		//register_setting( 'consolety-settings-group', 'consolety_remote_post' );

	}










    public function consolety_scripts()
    {
        echo "<script>function consolety_report(hash,title){
    var report = prompt('Report «'+title+'». Give us a reason why you report this post:');
    if(report){
                  var data = {
                    'action': 'consolety_report',
                    'hash':hash,
                    'report':report,
                    'post_id':" . get_the_ID() . "
                };

                
                jQuery.post('" . admin_url('admin-ajax.php') . "', data, function () {
                    alert('This post was reported!');
                });
              }
            }
                function consolety_click_record(hash){
                  var data = {
                    'action': 'consolety_click_record',
                    'hash':hash
                };
                jQuery.post('" . admin_url('admin-ajax.php') . "', data);
                    }
                    </script>";
    }

    /**
     * Enqueue the date picker
     */
    function enqueue_admin(): void
    {

        wp_enqueue_style('jquery-ui-min-css', 'https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css');
        wp_enqueue_script('jquery-ui-core');
        wp_enqueue_script('jquery-ui-datepicker', ['jquery']);
        wp_enqueue_style( 'wp-color-picker' );
        wp_enqueue_script( 'wp-color-picker' );
        wp_enqueue_style( 'consolety-admin', plugins_url('css/admin.css', __FILE__),[],Consolety::$installed );
        wp_enqueue_script( 'consolety-admin', plugins_url('js/admin.js', __FILE__),['jquery'],Consolety::$installed );
    }

    static function install()
    {
        Repository::getInstance()->install();
    }
    public function consolety_flush_if_needed(){
        if(get_site_option('consolety_last_activity') < time()-86400*3){
            Repository::getInstance()->reset_links();
        }

    }

    public function consolety_update_db_check(): void
    {
        $current_verion = get_site_option('consolety_db_version',0);

        if ($current_verion < $this->get_db_version()) {
            Repository::getInstance()->updates($current_verion,$this->get_db_version());
        }
    }
        public function get_db_version(): float
        {
             return self::$db_version;
        }




}
Consolety::getInstance();