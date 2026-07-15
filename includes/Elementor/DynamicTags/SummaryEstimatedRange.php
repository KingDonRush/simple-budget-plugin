<?php

namespace SBP\Elementor\DynamicTags;

if ( ! defined( 'ABSPATH' ) ) exit;

class SummaryEstimatedRange extends AbstractSummaryTextTag {
    public function get_name() {
        return 'sbp-summary-estimated-range';
    }

    public function get_title() {
        return __( 'SBP Estimated Range', 'simple-budget-plugin-sbp' );
    }

    protected function get_context_key() {
        return 'estimated_range';
    }
}
