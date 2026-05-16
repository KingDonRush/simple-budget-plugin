<?php
/**
 * Runtime asset flags set during widget rendering.
 */

namespace SBP\Support;

if ( ! defined( 'ABSPATH' ) ) exit;

class RuntimeAssets {

    private static $popup_required = false;

    public static function require_popup() {
        self::$popup_required = true;
    }

    public static function is_popup_required() {
        return self::$popup_required;
    }
}
