<?php

namespace SBP\Elementor\DynamicTags;

if ( ! defined( 'ABSPATH' ) ) exit;

class SummaryAdjustment extends AbstractSummaryTextTag {
    public function get_name() {
        return 'sbp-summary-adjustment';
    }

    public function get_title() {
        return __( 'SBP Adjustment', 'simple-budget-plugin-sbp' );
    }

    protected function get_context_key() {
        return 'adjustment';
    }
}
