<?php
/**
 * EIT field catalog bridge for Simple Budget pricing fields.
 */

namespace SBP\Support;

if ( ! defined( 'ABSPATH' ) ) exit;

class PricingEitCatalog {

    public static function add_catalog_entries( $entries ) {
        $entries = is_array( $entries ) ? $entries : [];

        foreach ( Pricing::supported_post_types() as $post_type ) {
            $post_type_object = get_post_type_object( $post_type );
            $post_type_label = $post_type_object && ! empty( $post_type_object->labels->singular_name )
                ? $post_type_object->labels->singular_name
                : $post_type;

            foreach ( BudgetValueFields::filterable_fields() as $key => $field ) {
                $entries[] = [
                    'key'       => $key,
                    'label'     => sprintf(
                        /* translators: 1: namespace label, 2: field label, 3: post type label, 4: meta key. */
                        __( '%1$s / %2$s / %3$s (%4$s)', 'simple-budget-plugin-sbp' ),
                        BudgetValueFields::NAMESPACE_LABEL,
                        $field['label'],
                        $post_type_label,
                        $key
                    ),
                    'source'    => 'meta',
                    'type'      => 'number' === $field['type'] ? 'number' : 'string',
                    'post_type' => $post_type,
                ];
            }
        }

        return $entries;
    }

    public static function add_public_meta_fields( $fields, $post_type ) {
        $fields = is_array( $fields ) ? $fields : [];
        $post_type = sanitize_key( $post_type );

        if ( ! in_array( $post_type, Pricing::supported_post_types(), true ) ) {
            return $fields;
        }

        foreach ( BudgetValueFields::filterable_fields() as $key => $field ) {
            $fields[ $key ] = [
                'key'          => $key,
                'label'        => $field['label'],
                'type'         => 'number' === $field['type'] ? 'number' : 'string',
                'default'      => '',
                'show_in_rest' => true,
            ];
        }

        return $fields;
    }
}
