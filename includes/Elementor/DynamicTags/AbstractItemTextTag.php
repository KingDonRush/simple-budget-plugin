<?php

namespace SBP\Elementor\DynamicTags;

use Elementor\Core\DynamicTags\Tag;
use Elementor\Modules\DynamicTags\Module;
use SBP\Support\BudgetContext;

if ( ! defined( 'ABSPATH' ) ) exit;

abstract class AbstractItemTextTag extends Tag {

    public function get_group() {
        return 'sbp-budget-item';
    }

    public function get_categories() {
        return [ Module::TEXT_CATEGORY ];
    }

    public function render() {
        echo esc_html( BudgetContext::item_value( $this->get_context_key() ) );
    }

    abstract protected function get_context_key();
}
