<?php

namespace SBP\Elementor\DynamicTags;

if ( ! defined( 'ABSPATH' ) ) exit;

class ItemDescription extends AbstractItemTextTag {
    public function get_name() {
        return 'sbp-item-description';
    }

    public function get_title() {
        return __( 'SBP Item Description', 'simple-budget-plugin-sbp' );
    }

    protected function get_context_key() {
        return 'description';
    }
}
