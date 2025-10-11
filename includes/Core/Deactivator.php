<?php
/**
 * Executado na desativação do plugin.
 */

namespace SBP\Core;

if ( ! defined( 'ABSPATH' ) ) exit;

class Deactivator {

    public static function deactivate() {
        wp_clear_scheduled_hook( 'sbp_cron_event' );
        flush_rewrite_rules();
    }
}
