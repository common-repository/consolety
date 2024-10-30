<?php
declare( strict_types=1 );

namespace Consolety;


class Initialization {
	private static $instance;
	private $secretKey;
	private $categories;
	public static $opt_categories = 'consolety_seo_categories';
	public static $opt_all_categories = 'consolety_seo_all_categories';
	public static $opt_post_types = 'consolety_post_types';
	public $enabled_post_types;


	public static function getInstance() {

		if ( ! isset( self::$instance ) ) {
			self::$instance = new Initialization();
		}

		return self::$instance;
	}

	public function __construct() {
		$this->secretKey          = get_option( 'consoletySeoKey', '' );
		$this->categories         = get_option( self::$opt_categories );
		$this->enabled_post_types = get_site_option( self::$opt_post_types, array( 'post' ) );
		add_action( 'admin_notices', [ $this, 'consolety_admin_notice' ] );
	}

	public function consolety_admin_notice() {
		if ( isset( $_GET['сonsolety_news_dismiss_id'] ) && current_user_can( 'manage_options' ) ) {
			update_site_option( 'сonsolety_news_dismiss_id', $_GET['сonsolety_news_dismiss_id'] );
		}
		$news_data = API::getInstance()->get_consolety_news();
		if ( isset( $news_data->id ) && get_site_option( 'сonsolety_news_dismiss_id' ) != $news_data->id ) {
			?>
            <div class="notice notice-<?= $news_data->class ?> is-dismissible">
                <h4>Consolety - SEO plugin for Traffic, Authority & Backlinks</h4>
                <p><?= $news_data->content ?></p>
                <div onclick="location.href='<?= add_query_arg( 'сonsolety_news_dismiss_id', $news_data->id ) ?>'"
                     class="notice-dismiss"></div>
            </div>
			<?php
		}
		if ( \Consolety\Entity\Site::getInstance()->isInactive() ) {
			?>
            <div class="notice notice-error"><!--- is-dismissible-->
                <p>Your site is <b style="color: #df5640">Inactive</b> at my.consolety.net. <a
                            href="https://my.consolety.net/login">Login</a> into your account and change status to <b
                            style="color: #70ca63">Active</b>.</p>

            </div>

			<?php

		}
	}

	public function display_key_block() {
		?>

        <p>To get your secret key, after you added your site at your account at <b>my.consolety.net</b> click on button:
            <img
                    style="position: absolute;
									margin-top: -10px;
									margin-left: 5px;"
                    src="<?= plugins_url( 'img/secret-key-button.png', \Consolety\BASEFILE ) ?>"><br/>
            <a target="_blank" href="https://my.consolety.net/register">(Don't
                have an account yet? Register
                here for free!)</a></p>


        <form id="consolety_seo_settings_form" method="post" action="options.php">
			<?php settings_fields( 'consolety-key-group' );
			do_settings_sections( 'consolety-key-group' );
			?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Secret Key</th>
                    <td>
                        <input type="text" style="width: 270px;" name="consoletySeoKey"
                               value="<?php echo esc_attr( $this->get_secret_key() ); ?>"/>
						<?php


						if ( $this->is_secret_key_setup() ) {
							$valid = \Consolety\API::getInstance()->check_site_connected();
						} else {
							$valid = false;
						}
						if ( $valid ) {
							echo '<img style="
    margin-top: 0px;
    position: absolute;
" src="' . plugins_url( 'img/valid.png', \Consolety\BASEFILE ) . '">';

						} else {
							echo '<img style="
    margin-top: 0px;
    position: absolute;
" src="' . plugins_url( 'img/invalid.png', \Consolety\BASEFILE ) . '">';
						}

						?>
                        <br/>
                        <p class="description">Enter your Secret key here to connect and verify your
                            website with consolety.
                    </td>
                </tr>
            </table>
            <p>
				<?= submit_button( null, 'primary', 'submit', false ) ?>
				<?php if ( $valid ) { ?>
                    <button style="margin-left: 10px" onclick="location.href=location.href+'&step=2'" type="button"
                            class="button">Next step
                    </button>
				<?php } ?>
            </p>
        </form>
		<?php
	}

	public function display_export_block( $settings_version = false ) {
		if ( \Consolety\API::getInstance()->get_site_data()->status ) {
			?>
            <form id="consolety-export-form" method="post">
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row"><?php _e( 'Post Types for export:', 'cpt-selector' ); ?></th>
                        <td>
							<?php
							foreach ( get_post_types( array( 'public' => true ) ) as $post_type ):
								/**
								 * attachments never have the a "publish" status, so they'll never show up
								 * on the front end, So we shouldn't include them here.
								 * used in array so we can (possibly exclude other post types
								 */
								if ( in_array( $post_type, array( 'attachment' ) ) ) {
									continue;
								}
								$checked = in_array( $post_type, array_values( $this->enabled_post_types ) ) ? 'checked="checked"' : '';
								$typeobj = get_post_type_object( $post_type );
								$label   = isset( $typeobj->labels->name ) ? $typeobj->labels->name : $typeobj->name;
								?>
                                <input type="checkbox" name="<?php echo self::$opt_post_types; ?>[]"
                                       value="<?php echo esc_attr( $post_type ); ?>"
                                       id="consolety_<?php echo $post_type ?>" <?php echo $checked; ?> />
                                <label for="consolety_<?php echo $post_type ?>"><?php echo ucwords( esc_html( $label ) ); ?></label>
                                <br/>
							<?php endforeach; ?>
                            <br/>
                            <p class="description">Only posts of selected post_type's will be exported & synchronized. (<b>NOTE: If an error occurs while exporting posts with the "page" type, leave only the "posts" type checked. At the moment we only support "post" type, please contact us to add more types.</b>)</p>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php _e( 'Manual export only:', 'cpt-selector' ); ?></th>
                        <td>
							<?php


							?>
                            <input type="checkbox" name="consolety_no_sync"
                                   id="consolety_no_sync" <?php echo get_option( 'consolety_no_sync', false ) == 'true' ? 'checked="checked"' : ''; ?> />
                            <label for="consolety_no_sync">No sync</label>
                            <br/>

                            <br/>
                            <p class="description">Posts and updates won't sync automatically, however, deleted posts that were previously exported will be deleted on my.consolety.net </p>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th class="row">Categories for export:</th>
                        <td>
                            <input type="checkbox" <?=(Initialization::getInstance()->get_isAllcategories()?'checked':'')?> name="consolety-select-all" id="consolety-select-all"/> <label for="consolety-select-all">All categories</label><br/><br/>
                            <div class="consolety-categories-box" style="<?=(Initialization::getInstance()->get_isAllcategories()?'display:none;':'')?>">
							<?php
							foreach ( get_categories( array( 'hide_empty' => 0, 'parent' => 0 ) ) as $category ) {
								$disabled = $category->term_id === 1;
								$checked  = ( in_array( $category->term_id, $this->get_categories() ) && $category->term_id !== 1 ) ? 'checked="checked"' : '';
								?>
                                <input <?= ( $disabled ? 'disabled' : '' ) ?> type="checkbox"
                                                                              class="consolety-categories <?= ( ! $disabled ? 'available' : '' ) ?>"
                                                                              value="<?= $category->term_id ?>"
                                                                              name="<?php echo self::$opt_categories; ?>[]"
                                                                              id="consolety_<?php echo $category->term_id ?>" <?php echo $checked; ?> />
                                <label for="consolety_<?php echo $category->term_id ?>"><?php echo ucwords( esc_html( $category->name ) ); ?></label>
                                <br/>
								<?php
								$subcategories = get_categories( array(
									'hide_empty' => 0,
									'parent'     => $category->term_id
								) );
								if ( count( $subcategories ) ) {
									?>
                                    <div style="margin: 5px 0 5px 15px">
										<?php
										foreach ( $subcategories as $subcategory ) {
											$checked = in_array( $subcategory->term_id, $this->get_categories() ) ? 'checked="checked"' : '';

											?>
                                            <input <?= ( $disabled ? 'disabled' : '' ) ?> class="consolety-categories"
                                                                                          value="<?= $subcategory->term_id ?>"
                                                                                          type="checkbox"
                                                                                          name="<?php echo self::$opt_categories; ?>[]"
                                                                                          id="consolety_<?php echo $subcategory->term_id ?>" <?php echo $checked; ?> />
                                            <label for="consolety_<?php echo $subcategory->term_id ?>"><?php echo ucwords( esc_html( $subcategory->name ) ); ?></label>
                                            <br/>
											<?php
										} ?>

                                    </div>

									<?php
								}
								?>

							<?php } ?>
                            </div>
                            <p class="description">Only posts of these categories will be exported and indexed by <b>my.consolety.net</b>.Posts "uncategorized" will not be exported.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <th class="row">Start date:</th>
						<?php
						$posts = get_posts( array(
							'post_type'   => 'post',
							'numberposts' => 1,
							'order_by'    => 'publish_date',
							'order'       => 'ASC'
						) );
						if ( isset( $posts[0] ) ) {
							$date = $posts[0]->post_date;
							$date = date( 'm-d-Y', strtotime( $date ) - 86400 );
						} else {
							$date = date( 'm-d-Y', time() - 86400 );
						}
						?>
                        <td><input id="consolety_datepicker" name="consolety_datepicker"
                                   value="<?= $date ?>" class="consolety-datepicker"/>
                            <div id="consolety_progress_block">
                                <div class="consolety_bar_progress">
                                    <span id="consolety_exported_val">0</span>/
                                    <span id="consolety_exported_total">0</span> of posts exported
                                </div>
                                <span id="consolety_done">Done!</span>
                                <progress id="consolety_progressbar" value="0" max="100"></progress>
                            </div>
                            <div class="consolety_posts_skipped">
                                <p>Posts were skipped:
                                    <span id="consolety_skipped" style="font-weight: bold">0</span></p>
                            </div>
                            <div id="consolety_error_export"></div>
                            <p class="description">Select the start date for manual export of posts. By default, the
                                date of the first post is set. Uncategorized posts cannot be exported. Settings will be automatically saved before export.<br/></p><br/>
                        </td>
                    </tr>

                    <tr>

                        <td colspan="2">


							<?php if ( ! $settings_version ) { ?>
                                <button type="button" onclick="save_settings_skip()" class="button button-primary">Skip
                                    export
                                </button>
                                <button style="margin-left: 10px;<?= ! get_option( 'consolety_export_finished_once' ) ? 'display: none' : '' ?>"
                                        onclick="location.href=location.href+'&step=3'" type="button"
                                        class="button next-step">Next step
                                </button>
							<?php } else { ?>
                                <button type="button" onclick="save_settings()" class="button button-primary">Save
                                    settings
                                </button>
							<?php } ?>
                            <button type="button" onclick="export_posts()" class="button button-primary">Export posts
                            </button>
                        </td>
                    </tr>
                </table>
            </form>
            <script>
                jQuery(document).ready(function ($) {
                    $('.consolety-datepicker').datepicker({dateFormat: 'yy-mm-dd'}).datepicker("setDate", new Date('<?=$date?>'));
                });
            </script>
			<?php
		} else {
			?>
            <center><b>Install valid secret key first.</b></center>
			<?php
		}
	}

	public function display_details_block( $settings_version = false ) {
		if ( \Consolety\API::getInstance()->check_site_connected() ) {
			if ( ! $settings_version ) {
				?>
                <form id="consolety_seo_settings_form" method="post" action="options.php">
			<?php } ?>
            <table class="form-table" style="text-align: center;">
                <tr>
                    <td style="vertical-align: text-top;">
                        <table style="margin: 0 auto;">
                            <tr>
                                <td colspan="2" style="text-align: center;text-decoration: underline;"><b>Info from
                                        my.consolety.net</b></td>
                            </tr>
                            <tr>
                                <th class="row">Site Id:</th>
                                <td><?= \Consolety\Entity\Site::getInstance()->id ?></td>
                            </tr>
                            <tr>
                                <th class="row">Site categories:</th>
                                <td><?= implode( ',', \Consolety\Entity\Site::getInstance()->categories )//TODO make cute badges    ?></td>
                            </tr>
                            <tr>
                                <th class="row">Language:</th>
                                <td><?= \Consolety\Entity\Site::getInstance()->language_label ?></td>
                            </tr>
                            <tr>
                                <th class="row">Network:</th>
                                <td><?= \Consolety\Entity\Site::getInstance()->network_label ?></td>
                            </tr>
                            <tr>
                                <th class="row">Site Status:</th>
                                <td><?= \Consolety\Entity\Site::getInstance()->status //TODO make cute badges    ?></td>
                            </tr>
                            <tr>
                                <th class="row">Points Used:</th>
                                <td><?= \Consolety\Entity\Site::getInstance()->points_used . '/' . \Consolety\Entity\Site::getInstance()->points_total ?></td>
                            </tr>
                            <tr>
                                <th class="row">Points per day:</th>
                                <td><?= \Consolety\Entity\Site::getInstance()->points_per_day ?></td>
                            </tr>
                            <tr>
                                <th class="row">Next link available:</th>
                                <td><?= \Consolety\Entity\Site::getInstance()->next_available_link ?><p
                                            class="description">(If no matched sites found - link receiving can be
                                        delayed)</p></td>

                            </tr>
                            <tr>
                                <th class="row">Booster:</th>
                                <td><?= \Consolety\Entity\Site::getInstance()->booster_label ?></td>
                            </tr>
                            <tr>
                                <th class="row">Booster expiration:</th>
                                <td><?= \Consolety\Entity\Site::getInstance()->booster_expiration ?></td>
                            </tr>
                        </table>
                    </td>
                    <td style="vertical-align: text-top;">
                        <table style="margin: 0 auto;">
                            <tr>
                                <td colspan="2" style="text-align: center;text-decoration: underline;"><b>Info from your
                                        site</b></td>
                            </tr>
                            <tr>
                                <th class="row">Categories for export:</th>
                                <td><?php
									$cat_names  = [];
									$categories = get_categories( array( 'hide_empty' => 0 ) );
									foreach ( $categories as $cat ) {
										if ( in_array( $cat->term_id, get_option( \Consolety\Initialization::$opt_categories, [] ) ) ) {
											$cat_names[] = $cat->name;
										}
									}
									echo implode( '<br/>', $cat_names );
									?></td>
                            </tr>
                            <tr>
                                <th class="row">Posts exported:</th>
                                <td><?= \Consolety\Entity\Site::getInstance()->posts_exported ?></td>
                            </tr>
                        </table>
						<?php if ( ! $settings_version ){ ?>
                <tr>
                    <td colspan="2" style="text-align: center"><?php settings_fields( 'consolety-install-group' );
						do_settings_sections( 'consolety-install-group' ); ?>
                        <input type="hidden" name="consolety_install_finished" value="1"/>
						<?php
						submit_button( 'Finish installation', 'primary', 'submit', false );
						?></td>
                </tr>
				<?php } ?>
            </table>


			<?php if ( ! $settings_version ) { ?>
                </form>
				<?php
			}
		} else {
			?>
            <center><b>Install valid secret key first.</b></center>
			<?php
		}
	}


	public function get_secret_key(): string {
		return $this->secretKey;
	}


	public function is_secret_key_setup(): bool {
		if ( ! $this->secretKey || empty( $this->secretKey ) ) {
			return false;
		}

		return true;
	}


	/**
	 * @return false|mixed|void
	 */
	public function get_categories() {

		return get_option( self::$opt_categories,[] );
	}

	public function get_isAllcategories() : bool
    {
		return (bool)get_site_option( self::$opt_all_categories,false );
	}
	/**
	 * @return false|mixed|void
	 */
	public function get_post_types() {

		return get_option( self::$opt_post_types,['post','page'] );
	}


}