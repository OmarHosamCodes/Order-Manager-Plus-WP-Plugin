<?php 
namespace OltewOrderListTableEle;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

final class Plugin {

	const VERSION = '1.0.1';
	const MINIMUM_ELEMENTOR_VERSION = '3.7.0';
	const MINIMUM_PHP_VERSION = '7.3';
	private static $_instance = null;

	public static function instance() {

		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;

	}

	public function __construct() {

		if ( $this->is_compatible() ) {
			add_action( 'elementor/init', [ $this, 'init' ] );
		}

	}

	public function is_compatible() {

		if ( ! did_action( 'elementor/loaded' ) ) {
			add_action( 'admin_notices', [ $this, 'admin_notice_missing_main_plugin' ] );
			return false;
		}

        if ( !in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
            add_action( 'admin_notices',[$this, 'admin_notice_missing_woo_plugin'] );
            return false;
        }
        
		if ( ! version_compare( ELEMENTOR_VERSION, self::MINIMUM_ELEMENTOR_VERSION, '>=' ) ) {
			add_action( 'admin_notices', [ $this, 'admin_notice_minimum_elementor_version' ] );
			return false;
		}

		if ( version_compare( PHP_VERSION, self::MINIMUM_PHP_VERSION, '<' ) ) {
			add_action( 'admin_notices', [ $this, 'admin_notice_minimum_php_version' ] );
			return false;
		}

		return true;

	}

	public function admin_notice_missing_main_plugin() {

		if ( isset( $_GET['activate'] ) ) unset( $_GET['activate'] );

		$message = sprintf(
			esc_html__( '"%1$s" requires "%2$s" to be installed and activated.', 'oltew-order-list-table-ele' ),
			'<strong>' . esc_html__( 'Woocommerce Order List Table', 'oltew-order-list-table-ele' ) . '</strong>',
			'<strong>' . esc_html__( 'Elementor', 'oltew-order-list-table-ele' ) . '</strong>'
		);

		printf( '<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message );

	}

    public function admin_notice_missing_woo_plugin() {

        if ( isset( $_GET['activate'] ) ) unset( $_GET['activate'] );

        $message = sprintf(
            esc_html__( '"%1$s" requires "%2$s" to be installed and activated.', 'oltew-order-list-table-ele' ),
            '<strong>' . esc_html__( 'Woocommerce Order List Table', 'oltew-order-list-table-ele' ) . '</strong>',
            '<strong>' . esc_html__( 'WooCommerce', 'oltew-order-list-table-ele' ) . '</strong>'
        );

        printf( '<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message );

    }

	public function admin_notice_minimum_elementor_version() {

		if ( isset( $_GET['activate'] ) ) unset( $_GET['activate'] );

		$message = sprintf(
			esc_html__( '"%1$s" requires "%2$s" version %3$s or greater.', 'oltew-order-list-table-ele' ),
			'<strong>' . esc_html__( 'Woocommerce Order List Table', 'oltew-order-list-table-ele' ) . '</strong>',
			'<strong>' . esc_html__( 'Elementor', 'oltew-order-list-table-ele' ) . '</strong>',
			 self::MINIMUM_ELEMENTOR_VERSION
		);

		printf( '<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message );

	}

	public function admin_notice_minimum_php_version() {

		if ( isset( $_GET['activate'] ) ) unset( $_GET['activate'] );

		$message = sprintf(
			esc_html__( '"%1$s" requires "%2$s" version %3$s or greater.', 'oltew-order-list-table-ele' ),
			'<strong>' . esc_html__( 'Woocommerce Order List Table', 'oltew-order-list-table-ele' ) . '</strong>',
			'<strong>' . esc_html__( 'PHP', 'oltew-order-list-table-ele' ) . '</strong>',
			 self::MINIMUM_PHP_VERSION
		);

		printf( '<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message );

	}

	public function init() {

		add_action( 'elementor/widgets/register', [ $this, 'register_widgets' ] );

	}

	public function register_widgets( $widgets_manager ) {

		require_once( __DIR__ . '/woo-table-widgets-ele.php' );

		$widgets_manager->register( new \Oltew_Order_List_table_Ele_Widget() );

	}

}
