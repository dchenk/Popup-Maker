<?php
// Exit if accessed directly

/*******************************************************************************
 * Copyright (c) 2017, WP Popup Maker
 ******************************************************************************/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class PUM_Cookies
 */
class PUM_Cookies {

	/**
	 * @var PUM_Cookies
	 */
	public static $instance;

	/**
	 * @var array
	 */
	public $cookies;


	/**
	 *
	 */
	public static function init() {
		self::instance();
	}

	/**
	 * @return PUM_Cookies
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * @param null $cookie
	 *
	 * @return mixed|null
	 */
	public function get_cookie( $cookie = null ) {
		$cookies = $this->get_cookies();

		return isset( $cookies[ $cookie ] ) ? $cookies[ $cookie ] : null;
	}

	/**
	 * @return array
	 */
	public function get_cookies() {
		if ( ! isset( $this->cookies ) ) {
			$this->register_cookies();
		}

		return $this->cookies;
	}

	/**
	 * Registers all known cookies when called.
	 */
	public function register_cookies() {
		$cookies = apply_filters( 'pum_registered_cookies', array(
			'on_popup_open'  => array(
				'name'            => __( 'On Popup Open', 'popup-maker' ),
				'modal_title'     => __( 'Cookie Settings', 'popup-maker' ),
				'settings_column' => sprintf( '%s%s%s', '<# if (typeof data.session === "undefined" || data.session !== "1") { print(data.time); } else { print("', __( 'Sessions', 'popup-maker' ), '"); } #>' ),
				'fields'          => $this->cookie_fields(),
			),
			'on_popup_close' => array(
				'name'            => __( 'On Popup Close', 'popup-maker' ),
				'modal_title'     => __( 'Cookie Settings', 'popup-maker' ),
				'settings_column' => sprintf( '%s%s%s', '<# if (typeof data.session === "undefined" || data.session !== "1") { print(data.time); } else { print("', __( 'Sessions', 'popup-maker' ), '"); } #>' ),
				'fields'          => $this->cookie_fields(),
			),
			'manual'         => array(
				'name'            => __( 'Manual JavaScript', 'popup-maker' ),
				'modal_title'     => __( 'Cookie Settings', 'popup-maker' ),
				'settings_column' => sprintf( '%s%s%s', '<# if (typeof data.session === "undefined" || data.session !== "1") { print(data.time); } else { print("', __( 'Sessions', 'popup-maker' ), '"); } #>' ),
				'fields'          => $this->cookie_fields(),
			),
		) );

		// @deprecated filter.
		$cookies = apply_filters( 'pum_get_cookies', $cookies );

		$this->add_cookies( $cookies );
	}

	/**
	 * @param array $cookies
	 */
	public function add_cookies( $cookies = array() ) {
		foreach ( $cookies as $key => $cookie ) {
			if ( empty( $cookie['id'] ) && ! is_numeric( $key ) ) {
				$cookie['id'] = $key;
			}

			$this->add_cookie( $cookie );
		}
	}

	/**
	 * @param null $cookie
	 */
	public function add_cookie( $cookie = null ) {
		if ( ! empty( $cookie['id'] ) && ! isset ( $this->cookies[ $cookie['id'] ] ) ) {
			$cookie = wp_parse_args( $cookie, array(
				'id'              => '',
				'name'            => '',
				'modal_title'     => '',
				'settings_column' => '',
				'priority'        => 10,
				'tabs'            => $this->get_tabs(),
				'fields'          => array(),
			) );

			// Here for backward compatibility to merge in labels properly.
			$labels        = $this->get_labels();
			$cookie_labels = isset( $labels[ $cookie['id'] ] ) ? $labels[ $cookie['id'] ] : array();

			if ( ! empty( $cookie_labels ) ) {
				foreach ( $cookie_labels as $key => $value ) {
					if ( empty( $cookie[ $key ] ) ) {
						$cookie[ $key ] = $value;
					}
				}
			}

			// Add cookie fields for all cookies automatically.
			$cookie['fields'] = array_merge( $cookie['fields'], $this->cookie_fields() );

			$this->cookies[ $cookie['id'] ] = $cookie;
		}

		return;

	}

	/**
	 * Returns an array of section labels for all triggers.
	 *
	 * Use the filter pum_get_trigger_section_labels to add or modify labels.
	 *
	 * @return array
	 */
	public function get_tabs() {
		/**
		 * Filter the array of trigger section labels.
		 *
		 * @param array $to_do The list of trigger section labels.
		 */
		return apply_filters( 'pum_get_trigger_tabs', array(
			'general' => __( 'General', 'popup-maker' ),
			'advanced' => __( 'Advanced', 'popup-maker' ),
		) );
	}

	/**
	 * @return array
	 */
	public function get_labels() {
		static $labels;

		if ( ! isset( $labels ) ) {
			/**
			 * Filter the array of cookie labels.
			 *
			 * @param array $to_do The list of cookie labels.
			 */
			$labels = apply_filters( 'pum_get_cookie_labels', array() );
		}

		return $labels;
	}

	/**
	 * Returns the cookie fields used for cookie options.
	 *
	 * @uses filter pum_get_cookie_fields
	 *
	 * @return array
	 *
	 */
	public function cookie_fields() {
		return apply_filters( 'pum_get_cookie_fields', array(
			'general' => array(
				'name'    => array(
					'label'       => __( 'Cookie Name', 'popup-maker' ),
					'placeholder' => __( 'Cookie Name ex. popmaker-123', 'popup-maker' ),
					'desc'        => __( 'The name that will be used when checking for or saving this cookie.', 'popup-maker' ),
					'std'         => '',
					'priority'    => 1,
				),
				'time'    => array(
					'label'       => __( 'Cookie Time', 'popup-maker' ),
					'placeholder' => __( '364 days 23 hours 59 minutes 59 seconds', 'popup-maker' ),
					'desc'        => __( 'Enter a plain english time before cookie expires.', 'popup-maker' ),
					'std'         => '1 month',
					'priority'    => 2,
				),
			),
			'advanced' => array(
				'session' => array(
					'label'    => __( 'Use Session Cookie?', 'popup-maker' ),
					'desc'     => __( 'Session cookies expire when the user closes their browser.', 'popup-maker' ) . ' ' . sprintf( __( '%sNote%s: Modern browsers that reopen your last browser session\'s tabs do not properly clear session cookies', 'popup-maker' ), '<strong>', '</strong>' ),
					'type'     => 'checkbox',
					'std'      => false,
					'priority' => 1,
				),
				'path'    => array(
					'label'    => __( 'Sitewide Cookie', 'popup-maker' ),
					'desc'     => __( 'This will prevent the popup from triggering on all pages until the cookie expires.', 'popup-maker' ),
					'type'     => 'checkbox',
					'std'      => true,
					'priority' => 2,
				),
				'key'     => array(
					'label'    => __( 'Cookie Key', 'popup-maker' ),
					'desc'     => __( 'Changing this will cause all existing cookies to be invalid.', 'popup-maker' ),
					'type'     => 'cookie_key',
					'std'      => '',
					'priority' => 3,
				),
			),
		) );
	}

	/**
	 * @deprecated
	 *
	 * @param null $cookie
	 * @param array $settings
	 *
	 * @return array
	 */
	public function validate_cookie( $cookie = null, $settings = array() ) {
		return $settings;
	}
}
