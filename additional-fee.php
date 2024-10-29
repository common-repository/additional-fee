<?php
/*
Plugin Name: Additional Fee
Plugin URI: https://wordpress.org/plugins/additional-fee/
Description: Add additional fee/ Discount to your cart
Version: 1.2.1
Author: Mingocommerce
Author URI: http://www.mingocommerce.com
Text Domain: additional-fee
Domain Path: /i18n
*/
define('MGC_ADDITIONAL_FEE', 'addition_fee_basic');

class MGC_Additional_Fee{
	
	function __construct(){
		
		$this->id	=	MGC_ADDITIONAL_FEE;
		
		add_action( 'woocommerce_cart_calculate_fees', array( $this, 'add_fee' ) );
		add_action( 'woocommerce_settings_tabs_'.$this->id, array( $this, 'settings_tab_output') );
		add_action( 'woocommerce_update_options_'.$this->id, array( $this, 'update_settings') );
		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
		
		add_filter( 'woocommerce_settings_tabs_array', array( $this, 'add_settings_tab' ), 50 );
		add_filter( 'plugin_action_links_'.self::get_plugin_url(), array( $this, 'plugin_list_links' ) );
	}
	
	function load_textdomain() {
		load_plugin_textdomain( 'additional-fee', FALSE, basename( dirname( __FILE__ ) ) . '/i18n/' );
	}	
	
	public static function get_plugin_url(){
		return plugin_basename( __FILE__ );
	}
	
	function custom_field_html( $field ){
		if( is_callable( array( $this, 'generate_'.$field['id'].'_html' ) ) ){
			$callable_function	=	'generate_'.$field['id'].'_html';
			$this->$callable_function();
		}
	}
	
	function add_fee(){
		if ( is_admin() && ! defined( 'DOING_AJAX' ) )
			return;		
		
		$cart_subtotal	=	WC()->cart->cart_contents_total;
		
		$cart_min	=	get_option($this->id.'_cart_min',false);
		if( $cart_min !== false && !empty($cart_min) && $cart_subtotal < $cart_min ){
			return;
		}
		
		$cart_max	=	get_option($this->id.'_cart_max',false);
		if( $cart_max !== false && !empty($cart_max) && $cart_subtotal > $cart_max ){
			return;
		}
		
		$fee	=	get_option($this->id.'_amount',	$this->get_settings()['amount']['default']);
		WC()->cart->add_fee( get_option($this->id.'_title',	$this->get_settings()['title']['default']), $fee, true, 'standard' );
	}
	
	
	function add_settings_tab( $settings_tabs ){
		$settings_tabs[$this->id]	=	__('Additional Fee', 'additional-fee');
		return $settings_tabs;
	}
	
	function settings_tab_output(){
		woocommerce_admin_fields( $this->get_settings() );
	}
	
	function get_settings(){
		$settings = array(
			'section_title' => array(
				'name'     => __( 'Additional Fee', 'additional-fee' ),
				'type'     => 'title',
				'desc'     => '',
				'id'       => $this->id.'_section_title'
			),
			'title' => array(
				'name' => __( 'Title', 'additional-fee' ),
				'type' => 'text',
				'default'=> __( 'Additional Fee', 'additional-fee' ),
				'id'   => $this->id.'_title'
			),
			'amount' => array(
				'name' => __( 'Amount', 'additional-fee' ),
				'type' => 'text',
				'default'=> 5,
				'desc' => __( 'Enter fee, put negative value for discount.', 'additional-fee' ),
				'id'   => $this->id.'_amount'
			),
			'cart_min' => array(
				'name' => __( 'Min Cart Total', 'additional-fee' ),
				'type' => 'text',
				'desc' => __( 'Apply fee only if cart total exceeds this value.', 'additional-fee' ),
				'id'   => $this->id.'_cart_min'
			),
			'cart_max' => array(
				'name' => __( 'Max Cart Total', 'additional-fee' ),
				'type' => 'text',
				'desc' => __( 'Apply fee only if cart total below this value.', 'additional-fee' ),
				'id'   => $this->id.'_cart_max'
			),
			'section_end' => array(
				 'type' => 'sectionend',
				 'id' => $this->id.'_section_end'
			),
		);
		return apply_filters( $this->id.'_fields', $settings );
	}
	
	function update_settings(){
		woocommerce_update_options( $this->get_settings() );
	}
	
	function plugin_list_links( $links ){
		$settings_link = '<a href="admin.php?page=wc-settings&tab='.$this->id.'">' . __( 'Settings', 'additional-fee' ) . '</a>';
		array_push( $links, $settings_link );
		return $links;
	}
}

new MGC_Additional_Fee();
