<?php

/**
 * Plugin Name: 			WC Kit Builder
 * Description: 			Extensão que permite criação de kits em produtos variáveis para lojas WooCommerce.
 * Author: 					MeuMouse.com
 * Author URI: 				https://meumouse.com/
 * Version: 				1.1.0
 * WC requires at least: 	6.0.0
 * WC tested up to: 		8.6.0
 * Requires PHP: 			7.2
 * Tested up to:      		6.4.3
 * Text Domain: 			wc-kit-builder
 * Domain Path: 			/languages
 * License: 				GPL2
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Wc_Kit_Builder
 */
class Wc_Kit_Builder {

	/**
	 * Plugin slug.
	 *
	 * @var string
	 */
	public static $slug = 'wc-kit-builder';

	/**
	 * Plugin version.
	 *
	 * @var string
	 */
	public static $version = '1.1.0';

	/**
	 * Settings array.
	 *
	 * @var array
	 */
	public $settings = array();

	/**
	 * Plugin initiated.
	 *
	 * @var bool
	 */
	public $initiated = false;

	/**
	 * Construct the plugin
     * 
     * @since 1.0.0
     * @return void
	 */
	public function __construct() {
		$this->define_constants();

		add_action( 'plugins_loaded', array( $this, 'wc_kit_builder_load_checker' ), 5 );
		load_plugin_textdomain( 'wc-kit-builder', false, dirname( WC_KIT_BUILDER_BASENAME ) . '/languages/' );
	}


	/**
	 * Define constants
	 * 
	 * @since 1.0.0
     * @return void
	 */
	private function define_constants() {
		$this->define( 'WC_KIT_BUILDER_FILE', __FILE__ );
		$this->define( 'WC_KIT_BUILDER_PATH', plugin_dir_path( __FILE__ ) );
		$this->define( 'WC_KIT_BUILDER_URL', plugin_dir_url( __FILE__ ) );
		$this->define( 'WC_KIT_BUILDER_ASSETS', WC_KIT_BUILDER_URL . 'assets/' );
		$this->define( 'WC_KIT_BUILDER_INC_PATH', WC_KIT_BUILDER_PATH . 'inc/' );
		$this->define( 'WC_KIT_BUILDER_BASENAME', plugin_basename( __FILE__ ) );
		$this->define( 'WC_KIT_BUILDER_VERSION', self::$version );
		$this->define( 'WC_KIT_BUILDER_SLUG', self::$slug );
	}


	/**
	 * Checker dependencies before activate plugin
	 * 
	 * @since 1.0.0
     * @return void
	 */
	public function wc_kit_builder_load_checker() {
		if ( !function_exists( 'is_plugin_active' ) ) {
			include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		}
	
		// check if WooCommerce is active
		if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
			add_action( 'before_woocommerce_init', array( __CLASS__, 'setup_hpos_compatibility' ) );

			$this->setup_includes();
			$this->initiated = true;
		} else {
			deactivate_plugins( 'wc-kit-builder/wc-kit-builder.php' );
			add_action( 'admin_notices', array( $this, 'wc_kit_builder_wc_deactivate_notice' ) );
		}

		// display notice if WooCommerce version is bottom 6.0
		if ( is_plugin_active( 'woocommerce/woocommerce.php' ) && version_compare( WC_VERSION, '6.0', '<' ) ) {
			add_action( 'admin_notices', array( $this, 'wc_kit_builder_wc_version_notice' ) );
			return;
		}

		// Display notice if PHP version is bottom 7.2
		if ( version_compare( phpversion(), '7.2', '<' ) ) {
			add_action( 'admin_notices', array( $this, 'wc_kit_builder_php_version_notice' ) );
			return;
		}
	}


	/**
	 * Run on activation
	 * 
	 * @since 1.0.0
     * @return void
	 */
	public static function activate() {
		self::clear_wc_template_cache();
	}


	/**
	 * Deactivate plugin
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function deactivate() {
		self::clear_wc_template_cache();
	}


	/**
	 * Clear WooCommerce template cache
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function clear_wc_template_cache() {
		if ( function_exists( 'wc_clear_template_cache' ) ) {
			wc_clear_template_cache();
		}
	}


	/**
	 * Define constant if not already set
	 *
	 * @since 1.0.0
	 * @param string $name | Name of constant
	 * @param string|bool $value | Value for constant
	 */
	private function define( $name, $value ) {
		if ( ! defined( $name ) ) {
			define( $name, $value );
		}
	}


	/**
	 * Load classes
	 * 
	 * @since 1.0.0
     * @return void
	 */
	private function setup_includes() {
		/**
		 * Class init plugin
		 * 
		 * @since 1.0.0
		 */
		include_once WC_KIT_BUILDER_INC_PATH . 'classes/class-wc-kit-builder-init.php';

		/**
		 * Front styles
		 * 
		 * @since 1.0.0
		 */
		include_once WC_KIT_BUILDER_INC_PATH . 'classes/class-wc-kit-builder-front.php';

        /**
		 * Plugin functions
		 * 
		 * @since 1.0.0
		 */
		include_once WC_KIT_BUILDER_INC_PATH . 'wc-kit-builder-functions.php';
	}

	
	/**
	 * WooCommerce version notice
	 * 
	 * @since 1.0.0
     * @return void
	 */
	public function wc_kit_builder_wc_version_notice() {
		echo '<div class="notice is-dismissible error">
				<p>' . __( '<strong>WC Kit Builder</strong> requer a versão do WooCommerce 6.0 ou maior. Faça a atualização do plugin WooCommerce.', 'wc-kit-builder' ) . '</p>
			</div>';
	}


	/**
	 * Notice if WooCommerce is deactivate
	 * 
	 * @since 1.0.0
     * @return void
	 */
	public function wc_kit_builder_wc_deactivate_notice() {
		if ( !current_user_can('install_plugins') ) {
			return;
		}

		echo '<div class="notice is-dismissible error">
				<p>' . __( '<strong>WC Kit Builder</strong> requer que <strong>WooCommerce</strong> esteja instalado e ativado.', 'wc-kit-builder' ) . '</p>
			</div>';
	}


	/**
	 * PHP version notice
	 * 
	 * @since 1.0.0
     * @return void
	 */
	public function wc_kit_builder_php_version_notice() {
		echo '<div class="notice is-dismissible error">
				<p>' . __( '<strong>WC Kit Builder</strong> requer a versão do PHP 7.2 ou maior. Contate o suporte da sua hospedagem para realizar a atualização.', 'wc-kit-builder' ) . '</p>
			</div>';
	}


	/**
	 * Setp compatibility with HPOS/Custom order table feature of WooCommerce.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function setup_hpos_compatibility() {
		if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', WC_KIT_BUILDER_FILE, true );
		}
	}


	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.0.0
     * @return void
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, esc_html__( 'Trapaceando?', 'wc-kit-builder' ), '1.0.0' );
	}


	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.0.0
     * @return void
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, esc_html__( 'Trapaceando?', 'wc-kit-builder' ), '1.0.0' );
	}
}

$wc_kit_builder = new Wc_Kit_Builder();

if ( $wc_kit_builder->initiated ) {
	register_activation_hook( __FILE__, array( $wc_kit_builder, 'activate' ) );
	register_deactivation_hook( __FILE__, array( $wc_kit_builder, 'deactivate' ) );
}