<?php

namespace SBP\Elementor\DynamicTags;

use Elementor\Core\DynamicTags\Data_Tag;
use Elementor\Modules\DynamicTags\Module;
use SBP\Support\BudgetContext;

if ( ! defined( 'ABSPATH' ) ) exit;

class ItemImage extends Data_Tag {
    public function get_name() {
        return 'sbp-item-image';
    }

    public function get_title() {
        return __( 'SBP Item Image', 'simple-budget-plugin-sbp' );
    }

    public function get_group() {
        return 'sbp-budget-item';
    }

    public function get_categories() {
        return [ Module::IMAGE_CATEGORY ];
    }

    public function get_value( array $options = [] ) {
        return BudgetContext::item_image();
    }
}
