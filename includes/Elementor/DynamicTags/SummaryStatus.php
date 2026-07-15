<?php

namespace SBP\Elementor\DynamicTags;

if ( ! defined( 'ABSPATH' ) ) exit;

class SummaryStatus extends AbstractSummaryTextTag {
    public function get_name() {
        return 'sbp-summary-status';
    }

    public function get_title() {
        return __( 'SBP Estimate Status', 'simple-budget-plugin-sbp' );
    }

    protected function get_context_key() {
        return 'status';
    }
}
