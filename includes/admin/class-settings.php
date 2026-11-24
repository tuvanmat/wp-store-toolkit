<?php
/**
 * Settings page.
 *
 * @package WPStoreToolkit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Renders the toolkit Settings page and registers its options.
 *
 * The page lists every available module with an enable/disable
 * checkbox. Each module then adds its own settings section through
 * WPST_Module::register_settings(). All saving goes through the
 * WordPress Settings API, which handles the nonce and the capability
 * check; input is additionally sanitised in sanitize_settings()
 * before it reaches the database. The capability required to see and
 * save the page defaults to 'manage_options' and can be changed with
 * the 'wpst_settings_capability' filter.
 */
class WPST_Settings {

	/**
	 * Option name storing all plugin settings.
	 *
	 * @var string
	 */
	const OPTION_NAME = 'wpst_settings';

	/**
	 * Settings page slug.
	 *
	 * @var string
	 */
	const PAGE_SLUG = 'wp-store-toolkit';

	/**
	 * Hooks the settings page into the admin.
	 */
	public function register_hooks() {
		add_action( 'admin_menu', array( $this, 'add_menu_page' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	/**
	 * Adds the page under the WooCommerce menu when WooCommerce is
	 * active, and under the Settings menu otherwise, so the page is
	 * always reachable.
	 */
	public function add_menu_page() {
		$capability = $this->get_capability();

		if ( class_exists( 'WooCommerce' ) ) {
			add_submenu_page(
				'woocommerce',
				__( 'WP Store Toolkit', 'wp-store-toolkit' ),
				__( 'Store Toolkit', 'wp-store-toolkit' ),
				$capability,
				self::PAGE_SLUG,
				array( $this, 'render_page' )
			);
			return;
		}

		add_options_page(
			__( 'WP Store Toolkit', 'wp-store-toolkit' ),
			__( 'Store Toolkit', 'wp-store-toolkit' ),
			$capability,
			self::PAGE_SLUG,
			array( $this, 'render_page' )
		);
	}

	/**
	 * Returns the capability required to view and save the settings
	 * page. Defaults to 'manage_options'; use the filter to let a
	 * different role (for example a shop manager) manage the toolkit.
	 *
	 * @return string
	 */
	public function get_capability() {
		return apply_filters( 'wpst_settings_capability', 'manage_options' );
	}

	/**
	 * Registers the option, the Modules section and every module's
	 * own settings.
	 */
	public function register_settings() {
		register_setting(
			'wpst_settings_group',
			self::OPTION_NAME,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( $this, 'sanitize_settings' ),
				'default'           => array(
					'enabled_modules' => array(),
					'module_settings' => array(),
				),
			)
		);

		add_settings_section(
			'wpst_modules_section',
			__( 'Modules', 'wp-store-toolkit' ),
			array( $this, 'render_modules_section' ),
			self::PAGE_SLUG
		);

		add_settings_field(
			'wpst_enabled_modules',
			__( 'Enabled modules', 'wp-store-toolkit' ),
			array( $this, 'render_enabled_modules_field' ),
			self::PAGE_SLUG,
			'wpst_modules_section'
		);

		foreach ( $this->get_modules() as $module ) {
			$module->register_settings();
		}
	}

	/**
	 * Sanitises the submitted settings before they are saved.
	 *
	 * The list of enabled modules is whitelisted against the module
	 * registry, so a crafted request cannot enable a module that
	 * does not exist. Module settings are sanitised as plain text
	 * for now; modules with richer settings will register their own
	 * sanitisation when they land.
	 *
	 * @param mixed $input Raw submitted value.
	 * @return array Sanitised settings.
	 */
	public function sanitize_settings( $input ) {
		$clean = array(
			'enabled_modules' => array(),
			'module_settings' => array(),
		);

		if ( ! is_array( $input ) ) {
			return $clean;
		}

		if ( isset( $input['enabled_modules'] ) && is_array( $input['enabled_modules'] ) ) {
			$known_ids = array_keys( $this->get_modules() );

			foreach ( $input['enabled_modules'] as $module_id ) {
				$module_id = sanitize_key( $module_id );
				if ( in_array( $module_id, $known_ids, true ) ) {
					$clean['enabled_modules'][] = $module_id;
				}
			}
		}

		if ( isset( $input['module_settings'] ) && is_array( $input['module_settings'] ) ) {
			$clean['module_settings'] = $this->sanitize_module_settings( $input['module_settings'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Sanitised recursively in sanitize_module_settings().
		}

		return $clean;
	}

	/**
	 * Recursively sanitises module settings values as text.
	 *
	 * @param array $values Raw module settings, keyed by module id.
	 * @return array
	 */
	private function sanitize_module_settings( $values ) {
		$clean = array();

		foreach ( $values as $key => $value ) {
			$key = sanitize_key( $key );

			if ( is_array( $value ) ) {
				$clean[ $key ] = $this->sanitize_module_settings( $value );
			} else {
				$clean[ $key ] = sanitize_text_field( $value );
			}
		}

		return $clean;
	}

	/**
	 * Prints the Modules section intro.
	 */
	public function render_modules_section() {
		echo '<p>' . esc_html__( 'Enable only the utilities you need. Disabled modules are not loaded at all.', 'wp-store-toolkit' ) . '</p>';
	}

	/**
	 * Prints one checkbox per registered module.
	 */
	public function render_enabled_modules_field() {
		$modules = $this->get_modules();
		$enabled = wpst()->get_setting( 'enabled_modules', array() );

		if ( empty( $modules ) ) {
			echo '<p>' . esc_html__( 'No modules are available yet.', 'wp-store-toolkit' ) . '</p>';
			return;
		}

		foreach ( $modules as $module ) {
			$module_id = $module->get_id();
			printf(
				'<label style="display:block;margin-bottom:8px;"><input type="checkbox" name="%1$s[enabled_modules][]" value="%2$s" %3$s /> <strong>%4$s</strong> — %5$s</label>',
				esc_attr( self::OPTION_NAME ),
				esc_attr( $module_id ),
				checked( is_array( $enabled ) && in_array( $module_id, $enabled, true ), true, false ),
				esc_html( $module->get_title() ),
				esc_html( $module->get_description() )
			);
		}
	}

	/**
	 * Renders the full settings page.
	 */
	public function render_page() {
		if ( ! current_user_can( $this->get_capability() ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'wp-store-toolkit' ) );
		}
		?>
		<div class="wrap">
			<h1><?php echo esc_html__( 'WP Store Toolkit', 'wp-store-toolkit' ); ?></h1>
			<form method="post" action="options.php">
				<?php
				settings_fields( 'wpst_settings_group' );
				do_settings_sections( self::PAGE_SLUG );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * Returns the registered modules without booting them.
	 *
	 * @return WPST_Module[]
	 */
	private function get_modules() {
		$manager = new WPST_Module_Manager();

		return $manager->get_modules();
	}
}
