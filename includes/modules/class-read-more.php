<?php
/**
 * Read More module.
 *
 * Collapses long product descriptions and short descriptions behind
 * a "Read more" toggle, and keeps table-of-contents (EZ TOC) anchor
 * links working when the target sits inside a collapsed description.
 *
 * Ported from the standalone "Read More for Product Descriptions"
 * plugin, rewritten to the toolkit's standards: collapse heights and
 * toggle labels are settings instead of hardcoded values, and the
 * front-end assets can be limited to product pages or loaded
 * site-wide for sticky table-of-contents support.
 *
 * @package WPStoreToolkit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Read More for Product Descriptions module.
 */
class WPST_Read_More extends WPST_Module {

	/**
	 * Default collapsed height (px) for the long description.
	 *
	 * @var int
	 */
	const DEFAULT_LONG_HEIGHT = 250;

	/**
	 * Default collapsed height (px) for the short description.
	 *
	 * @var int
	 */
	const DEFAULT_SHORT_HEIGHT = 120;

	/**
	 * Asset scope: load assets on product pages only.
	 *
	 * @var string
	 */
	const SCOPE_PRODUCT = 'product';

	/**
	 * Asset scope: load assets on every page (for sticky TOC).
	 *
	 * @var string
	 */
	const SCOPE_SITE = 'site';

	/**
	 * Module id.
	 *
	 * @var string
	 */
	protected $id = 'read-more';

	/**
	 * Module title.
	 *
	 * @var string
	 */
	protected $title = 'Read More for Product Descriptions';

	/**
	 * Module description.
	 *
	 * @var string
	 */
	protected $description = 'Collapses long product descriptions and short descriptions behind a "Read more" toggle, with table-of-contents compatibility.';

	/**
	 * Hooks the module into WordPress.
	 *
	 * The module only runs when WooCommerce is active: both filters
	 * target WooCommerce product output, and is_product() does not
	 * exist without it.
	 */
	public function init() {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return;
		}

		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_filter( 'the_content', array( $this, 'filter_long_description' ), 10 );
		add_filter( 'woocommerce_short_description', array( $this, 'filter_short_description' ), 10 );
	}

	/**
	 * Registers the module's settings fields.
	 */
	public function register_settings() {
		add_settings_section(
			'wpst_read_more_section',
			__( 'Read More for Product Descriptions', 'wp-store-toolkit' ),
			array( $this, 'render_section' ),
			WPST_Settings::PAGE_SLUG
		);

		add_settings_field(
			'wpst_read_more_long_height',
			__( 'Long description height', 'wp-store-toolkit' ),
			array( $this, 'render_long_height_field' ),
			WPST_Settings::PAGE_SLUG,
			'wpst_read_more_section'
		);

		add_settings_field(
			'wpst_read_more_short_height',
			__( 'Short description height', 'wp-store-toolkit' ),
			array( $this, 'render_short_height_field' ),
			WPST_Settings::PAGE_SLUG,
			'wpst_read_more_section'
		);

		add_settings_field(
			'wpst_read_more_expand_label',
			__( 'Expand label', 'wp-store-toolkit' ),
			array( $this, 'render_expand_label_field' ),
			WPST_Settings::PAGE_SLUG,
			'wpst_read_more_section'
		);

		add_settings_field(
			'wpst_read_more_collapse_label',
			__( 'Collapse label', 'wp-store-toolkit' ),
			array( $this, 'render_collapse_label_field' ),
			WPST_Settings::PAGE_SLUG,
			'wpst_read_more_section'
		);

		add_settings_field(
			'wpst_read_more_asset_scope',
			__( 'Asset scope', 'wp-store-toolkit' ),
			array( $this, 'render_asset_scope_field' ),
			WPST_Settings::PAGE_SLUG,
			'wpst_read_more_section'
		);
	}

	/**
	 * Prints the Read More section intro.
	 */
	public function render_section() {
		echo '<p>' . esc_html__( 'Collapse heights and toggle labels for product descriptions. Descriptions shorter than the height show no toggle.', 'wp-store-toolkit' ) . '</p>';
	}

	/**
	 * Prints the long description height field.
	 */
	public function render_long_height_field() {
		printf(
			'<input type="number" min="0" step="1" name="%1$s[module_settings][%2$s][long_height]" value="%3$d" class="small-text" /> %4$s',
			esc_attr( WPST_Settings::OPTION_NAME ),
			esc_attr( $this->id ),
			$this->get_height( 'long_height', self::DEFAULT_LONG_HEIGHT ),
			esc_html__( 'pixels', 'wp-store-toolkit' )
		);
		echo '<p class="description">' . esc_html__( 'Collapsed height of the long product description.', 'wp-store-toolkit' ) . '</p>';
	}

	/**
	 * Prints the short description height field.
	 */
	public function render_short_height_field() {
		printf(
			'<input type="number" min="0" step="1" name="%1$s[module_settings][%2$s][short_height]" value="%3$d" class="small-text" /> %4$s',
			esc_attr( WPST_Settings::OPTION_NAME ),
			esc_attr( $this->id ),
			$this->get_height( 'short_height', self::DEFAULT_SHORT_HEIGHT ),
			esc_html__( 'pixels', 'wp-store-toolkit' )
		);
		echo '<p class="description">' . esc_html__( 'Collapsed height of the short product description.', 'wp-store-toolkit' ) . '</p>';
	}

	/**
	 * Prints the expand label field.
	 */
	public function render_expand_label_field() {
		printf(
			'<input type="text" name="%1$s[module_settings][%2$s][expand_label]" value="%3$s" class="regular-text" />',
			esc_attr( WPST_Settings::OPTION_NAME ),
			esc_attr( $this->id ),
			esc_attr( $this->get_label( 'expand_label', __( 'Read more ▼', 'wp-store-toolkit' ) ) )
		);
		echo '<p class="description">' . esc_html__( 'Text of the toggle while the description is collapsed.', 'wp-store-toolkit' ) . '</p>';
	}

	/**
	 * Prints the collapse label field.
	 */
	public function render_collapse_label_field() {
		printf(
			'<input type="text" name="%1$s[module_settings][%2$s][collapse_label]" value="%3$s" class="regular-text" />',
			esc_attr( WPST_Settings::OPTION_NAME ),
			esc_attr( $this->id ),
			esc_attr( $this->get_label( 'collapse_label', __( 'Show less ▲', 'wp-store-toolkit' ) ) )
		);
		echo '<p class="description">' . esc_html__( 'Text of the toggle while the description is expanded.', 'wp-store-toolkit' ) . '</p>';
	}

	/**
	 * Prints the asset scope field.
	 */
	public function render_asset_scope_field() {
		$scope = $this->get_asset_scope();
		$name  = WPST_Settings::OPTION_NAME . '[module_settings][' . $this->id . '][asset_scope]';
		?>
		<select name="<?php echo esc_attr( $name ); ?>">
			<option value="<?php echo esc_attr( self::SCOPE_PRODUCT ); ?>" <?php selected( $scope, self::SCOPE_PRODUCT ); ?>><?php esc_html_e( 'Product pages only', 'wp-store-toolkit' ); ?></option>
			<option value="<?php echo esc_attr( self::SCOPE_SITE ); ?>" <?php selected( $scope, self::SCOPE_SITE ); ?>><?php esc_html_e( 'Entire site (for sticky table of contents)', 'wp-store-toolkit' ); ?></option>
		</select>
		<p class="description"><?php esc_html_e( 'Site-wide loading is only needed when a sticky table of contents must expand collapsed descriptions on pages and posts.', 'wp-store-toolkit' ); ?></p>
		<?php
	}

	/**
	 * Enqueues the module's front-end assets.
	 *
	 * With the default "product pages only" scope the assets load
	 * only where the filters can run; the site-wide scope keeps the
	 * table-of-contents behaviour working on pages and posts.
	 */
	public function enqueue_assets() {
		if ( self::SCOPE_SITE !== $this->get_asset_scope() && ! is_product() ) {
			return;
		}

		wp_enqueue_style(
			'wpst-read-more',
			WPST_PLUGIN_URL . 'assets/css/read-more.css',
			array(),
			WPST_VERSION
		);

		wp_enqueue_script(
			'wpst-read-more',
			WPST_PLUGIN_URL . 'assets/js/read-more.js',
			array(),
			WPST_VERSION,
			true
		);
	}

	/**
	 * Wraps the long product description in a collapsible block.
	 *
	 * @param string $content The post content.
	 * @return string
	 */
	public function filter_long_description( $content ) {
		if ( is_product() && in_the_loop() && is_main_query() ) {
			return $this->render_read_more(
				$content,
				$this->get_height( 'long_height', self::DEFAULT_LONG_HEIGHT ),
				' read-more-long-description'
			);
		}

		return $content;
	}

	/**
	 * Wraps the short product description in a collapsible block.
	 *
	 * @param string $content The short description.
	 * @return string
	 */
	public function filter_short_description( $content ) {
		if ( is_product() ) {
			return $this->render_read_more(
				$content,
				$this->get_height( 'short_height', self::DEFAULT_SHORT_HEIGHT )
			);
		}

		return $content;
	}

	/**
	 * Renders the collapsible wrapper and its toggle.
	 *
	 * The toggle labels travel as data attributes so the script can
	 * use the configured labels without hardcoded strings.
	 *
	 * @param string $content     The description HTML.
	 * @param int    $height      Collapsed height in pixels.
	 * @param string $extra_class Extra class for the wrapper.
	 * @return string
	 */
	private function render_read_more( $content, $height, $extra_class = '' ) {
		$expand_label   = $this->get_label( 'expand_label', __( 'Read more ▼', 'wp-store-toolkit' ) );
		$collapse_label = $this->get_label( 'collapse_label', __( 'Show less ▲', 'wp-store-toolkit' ) );

		$html = '<div class="read-more-wrap' . esc_attr( $extra_class ) . '" data-initial-height="' . esc_attr( $height ) . '">' . $content . '</div>';
		$html .= sprintf(
			'<div class="read-more-toggle" data-expand-label="%1$s" data-collapse-label="%2$s">%3$s</div>',
			esc_attr( $expand_label ),
			esc_attr( $collapse_label ),
			esc_html( $expand_label )
		);

		return $html;
	}

	/**
	 * Returns a configured collapse height, falling back to the
	 * default when the stored value is missing or not positive.
	 *
	 * @param string $key     Setting key.
	 * @param int    $default Default height in pixels.
	 * @return int
	 */
	private function get_height( $key, $default ) {
		$height = absint( $this->get_module_setting( $key, $default ) );

		return $height > 0 ? $height : $default;
	}

	/**
	 * Returns a configured toggle label, falling back to the default
	 * when the stored value is empty.
	 *
	 * @param string $key     Setting key.
	 * @param string $default Default label.
	 * @return string
	 */
	private function get_label( $key, $default ) {
		$value = $this->get_module_setting( $key, $default );

		if ( ! is_string( $value ) ) {
			return $default;
		}

		$value = trim( $value );

		return '' !== $value ? $value : $default;
	}

	/**
	 * Returns the configured asset scope, whitelisted to the two
	 * known values.
	 *
	 * @return string
	 */
	private function get_asset_scope() {
		$scope = $this->get_module_setting( 'asset_scope', self::SCOPE_PRODUCT );

		return in_array( $scope, array( self::SCOPE_PRODUCT, self::SCOPE_SITE ), true ) ? $scope : self::SCOPE_PRODUCT;
	}
}
