<?php

namespace SBP\Elementor\DynamicTags;

if ( ! defined( 'ABSPATH' ) ) exit;

class ItemTitle extends AbstractItemTextTag {
    public function get_name() {
        return 'sbp-item-title';
    }

    public function get_title() {
        return __( 'SBP Item Title', 'simple-budget-plugin-sbp' );
    }

    protected function get_context_key() {
        return 'title';
    }
}
