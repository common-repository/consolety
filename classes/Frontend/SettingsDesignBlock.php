<?php
declare( strict_types=1 );

namespace Consolety\Frontend;


class SettingsDesignBlock {
	private static $instance;
	private $styles;

	public static function getInstance() {

		if ( ! isset( self::$instance ) ) {
			self::$instance = new static();
		}

		return self::$instance;
	}

	public function __construct() {
		$this->styles = get_option( 'consolety_styles', $this->get_default_styles() );
		add_action( 'wp_head', [ $this, 'consolety_styles' ], 100 );
	}


	public function display_design_settings() {

		if ( isset( $_POST ) && ( isset( $_POST['settings-form-preview'] ) || isset( $_POST['settings-form-submit'] ) || isset( $_POST['settings-form-reset'] ) ) && check_admin_referer( plugin_basename( __FILE__ ), 'consolety_Seo_nonce_name' ) ) {
			if ( isset( $_POST['styles'] ) && isset( $_POST['settings-form-submit'] ) ) {
				$this->set_styles( [ 'h4'          => $_POST['styles']['h4'],
				                     'description' => $_POST['styles']['description'],
				                     'button'      => $_POST['styles']['button']
				] );
				update_option( 'consolety_styles', $this->get_styles() );
			} elseif ( isset( $_POST['settings-form-preview'] ) ) {
				$this->set_styles( [ 'h4'          => $_POST['styles']['h4'],
				                     'description' => $_POST['styles']['description'],
				                     'button'      => $_POST['styles']['button']
				] );
			} elseif ( isset( $_POST['settings-form-reset'] ) ) {
				$this->set_styles( $this->get_default_styles() );
				update_option( 'consolety_styles', $this->get_styles() );
			}

		}
		?>


                        <form id="consolety_seo_design_form" method="post" action="<?=esc_url( add_query_arg( 'tab', 'design' ) )?>">
                            <table class="form-table">
                                <tr valign="top">
                                    <th scope="row">Block Title font size</th>
                                    <td>
                                        <input type="text" value="<?= $this->get_styles()['h4']['font-size'] ?>"
                                               style="width:40px" class="text" name="styles[h4][font-size]"/>px
                                    </td>
                                </tr>
                                <tr valign="top">
                                    <th scope="row">Block Title font color</th>
                                    <td>
                                        <input type="text" value="<?= $this->get_styles()['h4']['color'] ?>"
                                               class="consolety-styles-color" name="styles[h4][color]"
                                               data-default-color="#404040"/>
                                    </td>
                                </tr>
                                <tr valign="top">
                                    <th scope="row">Block Content font size</th>
                                    <td>
                                        <input type="text"
                                               value="<?= $this->get_styles()['description']['font-size'] ?>"
                                               style="width:40px" class="text" name="styles[description][font-size]"/>px
                                    </td>
                                </tr>
                                <tr valign="top">
                                    <th scope="row">Block Content font color</th>
                                    <td>
                                        <input type="text" value="<?= $this->get_styles()['description']['color'] ?>"
                                               class="consolety-styles-color" name="styles[description][color]"
                                               data-default-color="#000"/>
                                    </td>
                                </tr>
                                <tr valign="top">
                                    <th scope="row">Max Content length</th>
                                    <td>
                                        <input type="range" min="100" step="25" max="250" list="description_length" value="<?= (empty($this->get_styles()['description']['length'])?250:$this->get_styles()['description']['length']) ?>"
                                               class="input-text-wrap" name="styles[description][length]"/>
                                        <datalist id="description_length">
                                            <option value="100" label="100">
                                            <option value="125">
                                            <option value="150">
                                            <option value="175" label="175">
                                            <option value="200">
                                            <option value="225">
                                            <option value="100" label="250">
                                        </datalist>
                                        <p class="description">Maximum number of characters of link content length(100-250 symbols)</p>
                                    </td>

                                </tr>
                                <tr valign="top">
                                    <th scope="row">Button font size</th>
                                    <td>
                                        <input type="text" value="<?= $this->get_styles()['button']['font-size'] ?>"
                                               style="width:40px" class="text" name="styles[button][font-size]"/>px
                                    </td>
                                </tr>
                                <tr valign="top">
                                    <th scope="row">Button font color</th>
                                    <td>
                                        <input type="text" value="<?= $this->get_styles()['button']['color'] ?>"
                                               class="consolety-styles-color" name="styles[button][color]"
                                               data-default-color="#fff"/>
                                    </td>
                                </tr>
                                <tr valign="top">
                                    <th scope="row">Button background color</th>
                                    <td>
                                        <input type="text"
                                               value="<?= $this->get_styles()['button']['background-color'] ?>"
                                               class="consolety-styles-color" name="styles[button][background-color]"
                                               data-default-color="#3498db"/>
                                    </td>
                                </tr>
                                <tr valign="top">
                                    <th scope="row">Button font color on hover</th>
                                    <td>
                                        <input type="text" value="<?= $this->get_styles()['button']['hover-color'] ?>"
                                               class="consolety-styles-color" name="styles[button][hover-color]"
                                               data-default-color="#fff"/>
                                    </td>
                                </tr>
                                <tr valign="top">
                                    <th scope="row">Button background color on hover</th>
                                    <td>
                                        <input type="text"
                                               value="<?= $this->get_styles()['button']['hover-background-color'] ?>"
                                               class="consolety-styles-color"
                                               name="styles[button][hover-background-color]"
                                               data-default-color="#2980b9"/>
                                    </td>
                                </tr>
                                <tr valign="top">
                                    <th scope="row">Button text</th>
                                    <td>
                                        <input type="text" value="<?= $this->get_styles()['button']['text'] ?>"
                                               class="input-text-wrap" name="styles[button][text]"/>
                                    </td>
                                </tr>
                                <tr valign="top">
                                    <th scope="row">Report Button text</th>
                                    <td>
                                        <input type="text" value="<?= $this->get_styles()['button']['report'] ?>"
                                               class="input-text-wrap" name="styles[button][report]"/>
                                    </td>
                                </tr>

                            </table>
                            <p class="submit">
								<?php wp_nonce_field( plugin_basename( __FILE__ ), 'consolety_Seo_nonce_name' ); ?>
                                <input type="submit" id="settings-form-design-preview" name="settings-form-preview"
                                       class="button-primary"
                                       value="Preview"/>
                                <input type="submit" id="settings-form-design-submit-save" name="settings-form-submit"
                                       class="button-primary"
                                       value="Save Changes"/>
                                <input type="submit" id="settings-form-design-submit-reset" name="settings-form-reset"
                                       class="button-primary"
                                       value="Reset"/>

                            </p>
                        </form>
                        <div class="preview-consolety">
                            <h1>Preview of block</h1>
							<?php $this->consolety_styles() ?>
							<?= $this->get_design_preview() ?>
                        </div>

		<?php
	}

	public function get_design_preview() {
		$preview_data = (object) [
			'title'       => 'What is Lorem Ipsum',
			'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Morbi leo odio, posuere ac dapibus eu, tincidunt mattis dolor. Aliquam sagittis quam quis nulla consectetur, sed sodales magna lobortis.'

		];

		$HTML = '<div id="consolety-seo-block">';
		$HTML .= '<div class="consolety_row">';
		for ( $i = 1; $i <= 3; $i ++ ) {
			$HTML .= '<div class="consolety_col-md-4">
           <h4 class="consolety_h4">' . $preview_data->title . '<span class="consolety_flag"><img  title="' . $this->get_styles()['button']['report'] . '" alt="' . $this->get_styles()['button']['report'] . '" src="' . plugins_url( 'img/flag.png', \Consolety\BASEFILE ) . '"></span></h4>
            <div class="consolety_decription">' . $this->mbCutString($preview_data->description, (empty($this->get_styles()['description']['length'])?250:(int)$this->get_styles()['description']['length'])) . '</div>
            <div class="consolety_btn_div"><a class="consolety_btn"   role="button">' . $this->get_styles()['button']['text'] . ' »</a></div>
          </div>';
		}

		$HTML .= '<div class="consolety_clearfix"></div></div>';
		if ( \Consolety\Entity\Site::getInstance()->network === 0 ) {
			$HTML .= '<small><a href="https://consolety.net/" target="_blank">Powered by Consolety.net</a></small>';
		}

		$HTML .= '</div>';

		return $HTML;
	}

	public function get_styles(): array {
		return $this->styles;
	}

	public function set_styles( array $styles ) {
		$this->styles = $styles;
	}
	public function mbCutString($str, $length, $postfix='...', $encoding='UTF-8')
	{
		if (mb_strlen($str, $encoding) <= $length) {
			return $str;
		}

		$tmp = mb_substr($str, 0, $length, $encoding);
		return mb_substr($tmp, 0, mb_strripos($tmp, ' ', 0, $encoding), $encoding) . $postfix;
	}

	public function get_default_styles(): array {
		$default_styles['h4']          = [ 'font-size' => '18', 'color' => '#404040' ];
		$default_styles['button']      = [
			'font-size'              => '14',
			'background-color'       => '#3498db',
			'color'                  => '#fff',
			'hover-color'            => '#fff',
			'hover-background-color' => '#3498db',
			'text'                   => 'Read more',
			'report'                 => 'Report this post'
		];
		$default_styles['description'] = [ 'font-size' => '14', 'color' => '#000','length'=>250 ];

		return $default_styles;
	}

	public function consolety_styles() {
		echo "<style>#consolety-seo-block{border: 1px solid #f3f3f3;padding-bottom: 10px;}
.consolety_row {display:table;width:100%;margin-top:20px;}
.consolety_row > [class*=\"consolety_col-\"] {float:none;display:table-cell;vertical-align:top;}
.consolety_decription{font-size: " . $this->get_styles()['description']['font-size'] . "px;line-height: 130%;color:" . $this->get_styles()['description']['color'] . ";}
.consolety_clearfix:after {content: \" \";display: block; height: 0; clear: both;}
#consolety-seo-block small{margin-top: 10px;float: right;color: #ccc;}
.consolety_btn_div{margin-top: 10px;position: absolute;bottom: 0;right: 15px;}
.consolety_flag{display: none;position: absolute;margin-left:5px;cursor: pointer;}
.consolety_col-md-4:hover .consolety_flag{display: inline-flex}
h4.consolety_h4{font-size:" . $this->get_styles()['h4']['font-size'] . "px;margin-top:10px;margin-bottom:10px;font-weight:500;line-height:1.1;color:" . $this->get_styles()['h4']['color'] . "}
.consolety_btn{background-color: " . $this->get_styles()['button']['background-color'] . ";
    color: " . $this->get_styles()['button']['color'] . ";
    padding: 6px 12px 7px;
    font-size: " . $this->get_styles()['button']['font-size'] . "px;
    font-weight: 700;
    display: inline-block;
    line-height: 1.428571;
    text-align: center;
    white-space: nowrap;
    cursor:pointer;
    transition: all .2s linear;}
.consolety_btn:hover{background-color:" . $this->get_styles()['button']['hover-background-color'] . ";color:" . $this->get_styles()['button']['hover-color'] . ";}
.consolety_col-md-4{width:33.33333333%;padding-bottom: 30px;float:left;position:relative;min-height:1px;padding-left:15px;padding-right:15px}
@media (max-width:768px){.consolety_col-md-4{width:100%;}</style>";
	}
}