<?php
/*
 * Plugin Name: Toolbar Publish Button
 * Plugin URI: https://wpUXsolutions.com
 * Description: Save time by scrolling less in WP admin! A small UX improvement that keeps Publish button within reach and retains the scrollbar position after saving in WordPress admin.
 * Version: 1.8.1
 * Author: wpUXsolutions
 * Author URI: https://wpUXsolutions.com
 * License: GPL2+ - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * Text Domain: toolbar-publish-button
 * Domain Path: /languages/
 *
 * Copyright 2013-2025  wpUXsolutions
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

if ( ! class_exists( 'tpb' ) ) :

class tpb {

    /**
     * TPB version.
     *
     * @var string
     */
    public $version = '1.8.1';

    /**
     * Plugin options.
     *
     * @var array
     */
    public $options = [];

    /**
     *  Main TPB Instance.
     *
     *  @return tpb
     */
    public static function instance() {
        static $instance = null;

        if ( null === $instance ) {
            $instance = new tpb();
            $instance->initialize();
        }

        return $instance;
    }

    /**
     * Constructor.
     */
    public function __construct() {}

    /**
     *  Initializes TPB.
     */
    public function initialize() {
        // options
        $this->options = [
            'name'     => __( 'Toolbar Publish Button', 'toolbar-publish-button' ),
            'dir'      => plugin_dir_url( __FILE__ ),
            'basename' => plugin_basename( __FILE__ ),
            'settings' => get_option( 'wpuxss_tpb_settings', [] ),
        ];

        // on update
        $version = get_option( 'wpuxss_tpb_version', null );

        if ( ! is_null( $version ) && version_compare( $version, $this->version, '<>' ) ) {
            $this->on_update();
        }

        // init actions
        add_action( 'init', [ $this, 'load_plugin_textdomain' ] );
        add_action( 'init', [ $this, 'register_admin_assets'] );

        add_action( 'admin_init', [ $this, 'register_setting' ] );
        add_action( 'admin_menu', [ $this, 'admin_menu' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_assets'] );
        add_action( 'admin_print_scripts-settings_page_tpb-settings', [ $this, 'print_tpb_settings_page_scripts' ] );

        // activation hook
        add_action( 'activate_' . $this->get_option( 'basename' ), [ $this, 'on_activation' ], 20 );

        // filters
        add_filter( 'plugin_action_links_' . $this->get_option( 'basename' ), [ $this, 'tpb_settings_links' ] );
    }

    /**
     * Returns a value from the options.
     */
    public function get_option( $name, $value = null ) {
        if ( isset( $this->options[ $name ] ) ) {
            $value = $this->options[ $name ];
        }
        return $value;
    }

    /**
     * Updates a value into the options.
     */
    public function update_option( $name, $value ) {
        $this->options[ $name ] = $value;
    }

    /**
     * Loads plugin text domain.
     */
    public function load_plugin_textdomain() {
        load_plugin_textdomain(
            'toolbar-publish-button',
            false,
            dirname( plugin_basename( __FILE__ ) ) . '/languages'
        );
    }

    /**
     * Register plugin settings.
     */
    public function register_setting() {
        register_setting(
            'wpuxss_tpb_settings',
            'wpuxss_tpb_settings',
            [ $this, 'sanitize_tpb_settings' ]
        );
    }

    /**
     * Sanitizes plugin settings before saving.
     */
    public function sanitize_tpb_settings( $input ) {
        $defaults = $this->get_default_settings();

        foreach ( $defaults as $option => $value ) {
            switch ( $option ) {
                case 'button_bg_color':
                    $tpb_settings    = $this->get_option( 'settings' );
                    $button_bg_color = sanitize_text_field( $input['button_bg_color'] );

                    if ( ! empty( $button_bg_color ) && ! sanitize_hex_color( $button_bg_color ) ) {
                        add_settings_error(
                            'wpuxss_tpb_settings',
                            'wpuxss_tpb_settings_color_error',
                            __( 'Please choose a valid color for background', 'toolbar-publish-button' ),
                            'error'
                        );
                        $input['button_bg_color'] = $tpb_settings['button_bg_color'];
                    } else {
                        $input['button_bg_color'] = $button_bg_color;
                    }
                    break;

                default:
                    $input[ $option ] = isset( $input[ $option ] ) && $input[ $option ] ? 1 : 0;
                    break;
            }
        }
        return $input;
    }

    /**
     * Registers scripts and styles for admin.
     */
    public function register_admin_assets() {
        $version = $this->version;
        $dir     = $this->get_option( 'dir' );

        wp_register_script( 'tpb-admin', $dir . 'js/tpb.js', [ 'jquery' ], $version, true );
        wp_register_script( 'tpb-options', $dir . 'js/tpb-options.js', [ 'jquery' ], $version, true );
        wp_register_script( 'tpb-scrollbar', $dir . 'js/tpb-scrollbar.js', [ 'jquery', 'tpb-admin' ], $version, true );
        wp_register_script( 'tpb-color-picker', $dir . 'js/tpb-color-picker.js', [ 'wp-color-picker' ], $version, true );

        wp_register_style( 'tpb-admin', $dir . 'css/tpb-admin.css', [], $version, 'all' );
    }

    /**
     * Enqueues scripts and styles for admin.
     */
    public function enqueue_admin_assets( $hook ) {
        global $hook_suffix;

        if ( 'index.php' === $hook_suffix ) {
            return;
        }

        $screen   = get_current_screen();
        $settings = $this->get_option( 'settings' );

        wp_enqueue_script( 'tpb-admin' );
        wp_localize_script( 'tpb-admin', 'tpb_l10n', [
            'button_bg'           => $settings['button_bg_color'],
            'draft_button'        => (bool) $settings['draft_button'],
            'preview_button'      => (bool) $settings['preview_button'],
            'buttons_to_the_right'=> (bool) $settings['buttons_to_the_right'],
        ] );

        if ( (bool) $settings['scrollbar_return'] ) {
            if ( ( 'acf-field-group' === $screen->post_type && (bool) $settings['expand_acf'] )
                 || 'acf-field-group' !== $screen->post_type ) {
                wp_enqueue_script( 'tpb-scrollbar' );
            }
        }
    }

    /**
     * Enqueues scripts for plugin's settings page.
     */
    public function print_tpb_settings_page_scripts() {
        wp_enqueue_script( 'tpb-color-picker' );
        wp_enqueue_script( 'tpb-options' );

        wp_enqueue_style( 'tpb-admin' );
        wp_enqueue_style( 'wp-color-picker' );
    }

    /**
     * Adds plugin options admin page.
     */
    public function admin_menu() {
        add_options_page(
            __( 'Settings','toolbar-publish-button' ) . ' :: ' . __( 'Toolbar Publish Button', 'toolbar-publish-button' ),
            __( 'Toolbar Publish Button','toolbar-publish-button' ),
            'manage_options',
            'tpb-settings',
            [ $this, 'print_tpb_settings_page' ]
        );
    }

    /**
     * Displays TPB settings page.
     */
    public function print_tpb_settings_page() {
        $version  = $this->version;
        $settings = $this->get_option( 'settings' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( __( 'You do not have sufficient permissions to access this page.','toolbar-publish-button' ) );
        }

        include __DIR__ . '/views/settings-page.php';
    }

    /**
     * Adds settings link to the plugin action links.
     */
    public function tpb_settings_links( $links ) {
        return array_merge(
            [ 'settings' => '<a href="' . esc_url( admin_url( 'options-general.php?page=tpb-settings' ) ) . '">' . __( 'Settings','toolbar-publish-button' ) . '</a>' ],
            $links
        );
    }

    /**
     * Sets plugin default settings.
     */
    public function get_default_settings() {
        return [
            'scrollbar_return'     => 1,
            'button_bg_color'      => '#0073AA',
            'draft_button'         => 1,
            'preview_button'       => 1,
            'buttons_to_the_right' => 0,
            'expand_acf'           => 1,
        ];
    }

    /**
     * Sets initial plugin settings.
     */
    public function on_activation() {
        if ( ! is_null( get_option( 'wpuxss_tpb_version', null ) ) ) {
            return;
        }

        update_option( 'wpuxss_tpb_version', $this->version );

        $defaults = $this->get_default_settings();

        update_option( 'wpuxss_tpb_settings', $defaults );
        $this->update_option( 'settings', $defaults );
    }

    /**
     * Makes changes to plugin options on update.
     */
    public function on_update() {
        update_option( 'wpuxss_tpb_version', $this->version );

        $tpb_settings = $this->get_option( 'settings' );
        $defaults     = $this->get_default_settings();

        $defaults['scrollbar_return'] = isset( $tpb_settings['wpuxss_tpb_scrollbar_return'] ) ? $tpb_settings['wpuxss_tpb_scrollbar_return'] : 1;
        $defaults['button_bg_color']  = isset( $tpb_settings['wpuxss_tpb_background'] ) ? $tpb_settings['wpuxss_tpb_background'] : '';

        $tpb_settings = array_intersect_key( $tpb_settings, $defaults );
        $tpb_settings = array_merge( $defaults, $tpb_settings );

        update_option( 'wpuxss_tpb_settings', $tpb_settings );
        $this->update_option( 'settings', $tpb_settings );
    }

} // class tpb

/**
 *  The main function.
 */
function tpb() {
    return tpb::instance();
}

// initialize
tpb();

endif; // class_exists
