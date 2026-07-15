<?php

namespace SBP\Elementor\DynamicTags;

if ( ! defined( 'ABSPATH' ) ) exit;

class ItemPrice extends AbstractItemTextTag {
    public function get_name() {
        return 'sbp-item-price';
    }

    public function get_title() {
        return __( 'SBP Item Price', 'simple-budget-plugin-sbp' );
    }

    protected function get_context_key() {
        return 'price';
    }
}
