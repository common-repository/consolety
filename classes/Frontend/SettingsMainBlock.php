<?php


namespace Consolety\Frontend;


class SettingsMainBlock {
	private static $instance;

	private static $remote_post = 'consolety_remote_post';

	public static function getInstance(): SettingsMainBlock {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new static();
		}

		return self::$instance;
	}

	public function __construct() {

		add_action( 'admin_init', [ $this, 'register_design_settings' ] );

	}

	public function register_main_settings() {
		register_setting( 'consolety-key-group', 'consolety_seo_secretkey' );
		register_setting( 'consolety-design-group', 'consolety-design_to_emails' );
		register_setting( 'consolety-design-group', 'consolety-design_period_minutes' );
		register_setting( 'consolety-design-group', 'consolety-design_with_delay' );
	}


	public function display_main_settings() {
		?>
<!--        <form id="consolety-settings" method="post" action="options.php">-->
			<?php //settings_fields( 'consolety-settings-group' );
			//do_settings_sections( 'consolety-settings-group' );
			?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Site status</th>
                    <td>
						<?php
						switch ( \Consolety\Entity\Site::getInstance()->status ) {
							case 'active':
								echo '<img src="' . plugins_url( 'img/site-status/active.png', \Consolety\BASEFILE ) . '">';
								break;
							case 'declined':
								echo '<img src="' . plugins_url( 'img/site-status/declined.png', \Consolety\BASEFILE ) . '">';
								?>
                                <br/>
                                <p class="description">Your website has been declined. Please
                                    read the report in your <a target="_blank"
                                                               href="https://my.consolety.net/sites">consolety
                                        account</a> to understand the reason so that you can fix
                                    the issues and request a for validation again.</p>
								<?php
								break;
							case 'inactive':
								echo '<img src="' . plugins_url( 'img/site-status/inactive.png', \Consolety\BASEFILE ) . '">';
								?>
                                <br/>
                                <p class="description">Site is inactive, this can mean that the
                                    site has been switched to "inactive" in your <a target="_blank"
                                                                                    href="https://my.consolety.net/sites">my.consolety.net</a>
                                    account by you, or the plugin has been
                                    deactivated or your site was unavaliable.You can see the reason in your <a
                                            target="_blank"
                                            href="https://my.consolety.net/sites">my.consolety</a> notifications. Please
                                    fix all issues and change status back
                                    to "Active" in your <a target="_blank"
                                                           href="https://my.consolety.net/sites">consolety
                                        account</a> to activate your site again.</p>
								<?php
								break;
							case 'ownership':
								echo '<img src="' . plugins_url( 'img/site-status/validation.png', \Consolety\BASEFILE ) . '">';
								?>
                                <br/>
                                <p class="description">You need to finish up site validation in
                                    your consolety user account.</p>
								<?php
								break;
							case 'verification':
								echo '<img src="' . plugins_url( 'img/site-status/verification.png', \Consolety\BASEFILE ) . '">';
								?>
                                <br/>
                                <p class="description">Site needs to be verified by an
                                    admin.</p>
								<?php
								break;
							case 'blocked':
								echo '<img src="' . plugins_url( 'img/site-status/blocked.png', \Consolety\BASEFILE ) . '">';
								?>
                                <br/>
                                <p class="description">Unfortunately, your site does not meet the requirements or rules of our system and cannot be included in our system..</p>
								<?php
                                return false;
								break;
						}


						?>

                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Reset All my backlinks</th>
                    <td>
                        <?php
                        $last_flush = get_site_option( 'consolety_last_flush');
                        ?>
                        <button class="button action" type="button" name="consoletyReset"
                                <?=((!$last_flush || $last_flush+86400*7<time())?
                                'onclick="if(confirm(\'All posts we collected from your site will be removed. Are you sure?\')){flush_my_posts_at_consolety()}"':'disabled=disabled')?>
                                id="consoletyFlush">Reset my backlinks
                        </button>
                        <br/>
                        <p class="description">
                            If you want to reset posts we collected and
                            displaying on other sites -
                            click this button.You always can export them back to our system.While posts will be removed
                            from other sites your available points will be updated multiple times
                            <b>(Available once per week. Later time delay will be extended to 30 days)</b>
                        </p>
                    </td>
                </tr>



            </table>
			<?php //submit_button(); ?>
<!--        </form>-->
		<?php
		\Consolety\Initialization::getInstance()->display_export_block( true );
		?>


		<?php

	}

}