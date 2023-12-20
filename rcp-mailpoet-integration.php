<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Plugin Name: Restrict Content Pro - Advanced MailPoet Integration
 * Plugin URI: https://jacobmckinney.com/
 * Author: Jacob McKinney
 * Author URI: https://jacobmckinney.com/
 * Author Email: me@jacobmckinney.com
 * Description: Sends Restrict Content Pro member data to MailPoet
 * Version: 1.0.0
 * License: GPL2
 * Copyright 2023 Jacob McKinney
 */

use MailPoet\Models\Subscriber;

if ( is_plugin_active('restrict-content-pro/restrict-content-pro.php') && is_plugin_active('mailpoet/mailpoet.php') ) {
    class RCP_MailPoet_Integration {
        /**
         * The single plugin instance
         *
         * @var RCP_MailPoet_Integration
         */
        protected static $_instance = null;

        /**
         * The plugin constructor
         */
        public function __construct() {
            $this->constants();
            $this->hooks();
        }

        /**
         * Defines plugin constants
         */
        private function constants() {
            define( 'RCP_MAILPOET_INTEGRATION_FILE', __FILE__ );
            define( 'RCP_MAILPOET_INTEGRATION_DIR', dirname( RCP_MAILPOET_INTEGRATION_FILE ) );
            define( 'RCP_MAILPOET_INTEGRATION_URL', plugins_url( '', RCP_MAILPOET_INTEGRATION_FILE ) );
        }

        /**
         * Hooks into WordPress
         */
        public function hooks() {
            add_action( 'rcp_transition_membership_status', array( $this, 'send_membership_status_to_mailpoet' ), 10, 3 );
        }

        /**
         * Sends membership status to MailPoet
         *
         * @param $old_status
         * @param $new_status
         * @param $membership_id
         */
        public function send_membership_status_to_mailpoet( $old_status, $new_status, $membership_id ) {
            $membership = rcp_get_membership( $membership_id );
            $user_id    = $membership->get_user_id();
            $user       = get_userdata( $user_id );
            $email      = $user->user_email;
            $membership_level = $membership->get_membership_level_name();

            Subscriber::createOrUpdate([
                'email' => $email,
                'cf_1'  => $membership_level,
                'cf_2'  => $new_status
            ]);
        }

        /**
         * Main Plugin Instance.
         *
         * Ensures only one instance of plugin is loaded or can be loaded.
         *
         * @static
         * @see   rcp_mailpoet_integration()
         * @return RCP_MailPoet_Integration - Main instance.
         */
        public static function instance()
        {

            if (is_null(self::$_instance)) {
                self::$_instance = new self();
            }

            return self::$_instance;
        }
    }

    /**
     * Global function call for the plugin
     * @return RCP_MailPoet_Integration
     */
    function rcp_mailpoet_integration() {
        return RCP_MailPoet_Integration::instance();
    }

    rcp_mailpoet_integration();
}