<?php
/**
 * Executado na ativação do plugin.
 */

namespace SBP\Core;

if ( ! defined( 'ABSPATH' ) ) exit;

class Activator {

    public static function activate() {
        if ( get_option( 'sbp_whatsapp_number' ) === false ) {
            add_option( 'sbp_whatsapp_number', '' );
        }
        if ( get_option( 'sbp_product_post_types' ) === false ) {
            add_option( 'sbp_product_post_types', [] );
        }
        if ( get_option( 'sbp_budget_value_fields' ) === false ) {
            add_option( 'sbp_budget_value_fields', [] );
        }

        update_option( 'sbp_version', \SBP_VERSION );
        flush_rewrite_rules();
    }
}
