<?php

namespace SBP\Elementor\DynamicTags;

if ( ! defined( 'ABSPATH' ) ) exit;

class SummarySubtotal extends AbstractSummaryTextTag {
    public function get_name() {
        return 'sbp-summary-subtotal';
    }

    public function get_title() {
        return __( 'SBP Subtotal', 'simple-budget-plugin-sbp' );
    }

    protected function get_context_key() {
        return 'subtotal';
    }
}
