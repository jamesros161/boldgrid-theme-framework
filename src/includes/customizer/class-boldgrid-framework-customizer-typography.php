<?php
/**
 * Class: Boldgrid_Framework_Customizer_Typography
 *
 * This is the class responsible for adding the typography
 * section to the WordPress Customizer.
 *
 * @since      1.0.0
 * @category   Customizer
 * @package    Boldgrid_Framework
 * @subpackage Boldgrid_Framework_Customizer_Typography
 * @author     BoldGrid <support@boldgrid.com>
 * @link       https://boldgrid.com
 */

// If this file is called directly, abort.
defined( 'WPINC' ) ? : die;

/**
 * Boldgrid_Framework_Customizer_Typography Class
 *
 * This is responsible for adding Typography section to the
 * WordPress customizer.
 *
 * @since 1.1
 */
class Boldgrid_Framework_Customizer_Typography {

	/**
	 * The BoldGrid Theme Framework configurations.
	 *
	 * @since     1.0.0
	 * @access    protected
	 * @var       string     $configs       The BoldGrid Theme Framework configurations.
	 */
	protected $configs;

	/**
	 * A list of typography settings and css class name relationships.
	 *
	 * This could be autogenerated, code in config for now. Used for creating classes
	 * as well as pass values into the post and page builder.
	 *
	 * @since 2.0.0
	 *
	 * @var array $typography_settings.
	 */
	protected static $typography_settings = array(
		array(
			'settings' => 'bgtfw_body_typography',
			'class_name' => 'bg-font-family-body',
		),
		array(
			'settings' => 'bgtfw_headings_typography',
			'class_name' => 'bg-font-family-heading',
		),
		array(
			'settings' => 'bgtfw_site_title_typography',
			'class_name' => 'bg-font-family-site-title',
		),
		array(
			'settings' => 'bgtfw_tagline_typography',
			'class_name' => 'bg-font-family-tagline',
		),
	);

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since 1.0.0
	 *
	 * @param array $configs The BoldGrid Theme Framework configurations.
	 */
	public function __construct( $configs ) {
		$this->configs = $configs;
		$this->set_menu_typography( $configs );
	}

	/**
	 * Set dynamic menu typography settings.
	 *
	 * @since 2.0.0
	 *
	 * @param array $configs The BoldGrid Theme Framework configurations.
	 */
	public function set_menu_typography( $configs ) {
		foreach ( array_keys( $configs['menu']['locations'] ) as $location ) {
			self::$typography_settings[] = [
				'settings' => "bgtfw_menu_typography_{$location}",
				'class_name' => 'bg-font-family-menu-' . str_replace( '_', '-', $location ),
			];
		}
	}

	/**
	 * Get the current font settings.
	 *
	 * This method includes the currently saved theme mod value.
	 *
	 * @since 2.0.0
	 *
	 * @return array Settings.
	 */
	public function get_typography_settings() {
		$configs = $this->configs['customizer']['controls'];

		$settings = self::$typography_settings;
		foreach ( $settings as &$setting ) {
			$setting['value'] = get_theme_mod( $setting['settings'],
				$configs[ $setting['settings'] ]['default'] );
		}

		return $settings;
	}

	/**
	 * Classes that represent the font families chosen for theme.
	 *
	 * @since 1.2.4
	 *
	 * @return string css.
	 */
	public function generate_font_classes() {
		$menu_font = get_theme_mod( 'navigation_main_typography', false );
		$menu_font_family = ! empty( $menu_font['font-family'] ) ? $menu_font['font-family'] :
			$this->configs['customizer-options']['typography']['defaults']['navigation_font_family'];

		$css = '';
		$css .= ".bg-font-family-menu { font-family: $menu_font_family !important }";

		foreach ( $this->get_typography_settings() as $typography_setting ) {
			$css .= ".{$typography_setting['class_name']} { font-family: {$typography_setting['value']['font-family']['font-family']} !important }";
		}

		return $css;
	}

	/**
	 * Adds font size CSS to style.css inline.
	 *
	 * @since 2.0.0
	 */
	public function add_font_size_css( $css ) {
		return $this->generate_font_size_css( $css );
	}

	/**
	 * Generates font sizes based on Bootstrap's LESS implementation.
	 *
	 * @since  2.0.0
	 *
	 * @param  string $css CSS to append styles to.
	 *
	 * @return string $css Generated CSS styles.
	 */
	public function generate_font_size_css( $css = '' ) {
		$css .= $this->generate_headings_css();
		$css .= $this->generate_font_classes();

		return apply_filters( 'bgtfw_inline_css', $css );
	}

	/**
	 * Generates headings CSS to apply to frontend.
	 *
	 * @since  2.0.0
	 *
	 * @param  string $css CSS to append headings styles to.
	 *
	 * @return string $css CSS for headings styles.
	 */
	public function generate_headings_css( $css = '' ) {
		$headings_font = get_theme_mod( 'bgtfw_headings_typography' );
		$selectors = $this->configs['customizer-options']['typography']['selectors'];

		$css .= $this->generate_headings_color_css( 'bgtfw_headings_color', '', $selectors );

		return $css;
	}

	/**
	 * Generates headings color CSS to apply to frontend.
	 *
	 * @since  2.0.0
	 *
	 * @param  string $theme_mod Name of thememod to get color palette settings from.
	 * @param  string $section   CSS selector for section to apply heading colors to.
	 * @param  array  $selectors Array of heading CSS selectors from configs.
	 * @param  string $css       CSS to append headings styles to.
	 *
	 * @return string $css       Output CSS for headings color styles.
	 */
	public function generate_headings_color_css( $theme_mod, $section, $selectors = array(), $css = '' ) {
		$theme_mod = get_theme_mod( $theme_mod, false );

		if ( empty( $theme_mod ) ) {
			return;
		}

		list( $theme_mod ) = explode( ':', $theme_mod );
		$theme_mod = "var(--{$theme_mod})";

		if ( empty( $selectors ) ) {
			$selectors = $this->configs['customizer-options']['typography']['selectors'];
		}

		$found = array();

		foreach ( $selectors as $selector => $options ) {
			if ( 'headings' === $options['type'] ) {
				$found[] = "$section $selector";
			}
		}

		$found = implode( ', ', $found );
		$css .= "$found{color:{$theme_mod};}";

		return $css;
	}

	/**
	 * Retrieves formatted output configs for headings selectors.
	 *
	 * @since  2.0.0
	 *
	 * @param  string $configs  BGTFW Configurations.
	 * @param  string $elements Elements to apply settings to.
	 *
	 * @return string $values  Formatted output configs.
	 */
	public function get_output_values( $configs, $elements = '' ) {
		if ( empty( $elements ) ) {
			$elements = implode( ', ', array_keys( $configs['customizer-options']['typography']['selectors'] ) );
		}

		$props = [ 'font-family', 'line-height', 'text-transform', 'variant', 'font-style' ];
		$values = [];

		foreach ( $props as $prop ) {
			$values[] = [
				'element' => $elements,
				'property' => 'variant' === $prop ? 'font-weight' : $prop,
				'choice' => $prop,
			];
		}

		return $values;
	}
}
