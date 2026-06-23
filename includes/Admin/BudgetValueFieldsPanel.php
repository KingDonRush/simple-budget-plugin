<?php
/**
 * Settings table for option-backed __SBP budget value fields.
 */

namespace SBP\Admin;

use SBP\Support\BudgetValueFields;

if ( ! defined( 'ABSPATH' ) ) exit;

class BudgetValueFieldsPanel {

    public function render() {
        $fields = array_values( BudgetValueFields::custom_fields() );

        if ( empty( $fields ) ) {
            $fields = [ $this->blank_field() ];
        }
        ?>
        <table class="widefat striped" id="sbp-budget-value-fields">
            <thead>
                <tr>
                    <th><?php esc_html_e( 'Meta key', 'simple-budget-plugin-sbp' ); ?></th>
                    <th><?php esc_html_e( 'Label', 'simple-budget-plugin-sbp' ); ?></th>
                    <th><?php esc_html_e( 'Type', 'simple-budget-plugin-sbp' ); ?></th>
                    <th><?php esc_html_e( 'Unit', 'simple-budget-plugin-sbp' ); ?></th>
                    <th><?php esc_html_e( 'Display', 'simple-budget-plugin-sbp' ); ?></th>
                    <th><?php esc_html_e( 'Filter', 'simple-budget-plugin-sbp' ); ?></th>
                    <th><?php esc_html_e( 'Select options', 'simple-budget-plugin-sbp' ); ?></th>
                    <th></th>
                </tr>
            </thead>
            <tbody data-next-index="<?php echo esc_attr( count( $fields ) ); ?>">
                <?php foreach ( $fields as $index => $field ) : ?>
                    <?php $this->render_row( $index, $field ); ?>
                <?php endforeach; ?>
            </tbody>
        </table>
        <p>
            <button type="button" class="button" id="sbp-add-budget-value-field">
                <?php esc_html_e( 'Add value field', 'simple-budget-plugin-sbp' ); ?>
            </button>
        </p>
        <p class="description">
            <?php esc_html_e( 'These fields are stored as post meta, surfaced as __SBP fields for the Implementation Toolkit, and can also be managed with wp sbp value-fields.', 'simple-budget-plugin-sbp' ); ?>
        </p>
        <script>
        document.addEventListener('click', function(e) {
            if (e.target.id === 'sbp-add-budget-value-field') {
                const body = document.querySelector('#sbp-budget-value-fields tbody');
                const index = parseInt(body.dataset.nextIndex || '0', 10);
                body.dataset.nextIndex = String(index + 1);
                body.insertAdjacentHTML('beforeend', <?php echo wp_json_encode( $this->row_template() ); ?>.replaceAll('__INDEX__', index));
            }

            if (e.target.classList.contains('sbp-remove-budget-value-field')) {
                e.target.closest('tr').remove();
            }
        });
        </script>
        <?php
    }

    private function render_row( $index, array $field ) {
        ?>
        <tr>
            <td>
                <input type="text" name="<?php echo esc_attr( BudgetValueFields::OPTION ); ?>[<?php echo esc_attr( $index ); ?>][key]" value="<?php echo esc_attr( $field['key'] ); ?>" class="regular-text" placeholder="_sbp_material_cost" />
            </td>
            <td>
                <input type="text" name="<?php echo esc_attr( BudgetValueFields::OPTION ); ?>[<?php echo esc_attr( $index ); ?>][label]" value="<?php echo esc_attr( $field['label'] ); ?>" class="regular-text" />
            </td>
            <td>
                <select name="<?php echo esc_attr( BudgetValueFields::OPTION ); ?>[<?php echo esc_attr( $index ); ?>][type]">
                    <?php foreach ( BudgetValueFields::types() as $type => $label ) : ?>
                        <option value="<?php echo esc_attr( $type ); ?>" <?php selected( $field['type'], $type ); ?>><?php echo esc_html( $label ); ?></option>
                    <?php endforeach; ?>
                </select>
            </td>
            <td>
                <input type="text" name="<?php echo esc_attr( BudgetValueFields::OPTION ); ?>[<?php echo esc_attr( $index ); ?>][unit]" value="<?php echo esc_attr( $field['unit'] ); ?>" class="small-text" />
            </td>
            <td>
                <input type="hidden" name="<?php echo esc_attr( BudgetValueFields::OPTION ); ?>[<?php echo esc_attr( $index ); ?>][display]" value="0" />
                <input type="checkbox" name="<?php echo esc_attr( BudgetValueFields::OPTION ); ?>[<?php echo esc_attr( $index ); ?>][display]" value="1" <?php checked( ! empty( $field['display'] ) ); ?> />
            </td>
            <td>
                <input type="hidden" name="<?php echo esc_attr( BudgetValueFields::OPTION ); ?>[<?php echo esc_attr( $index ); ?>][filterable]" value="0" />
                <input type="checkbox" name="<?php echo esc_attr( BudgetValueFields::OPTION ); ?>[<?php echo esc_attr( $index ); ?>][filterable]" value="1" <?php checked( ! empty( $field['filterable'] ) ); ?> />
            </td>
            <td>
                <textarea name="<?php echo esc_attr( BudgetValueFields::OPTION ); ?>[<?php echo esc_attr( $index ); ?>][options]" rows="2" class="large-text" placeholder="slug|Label"><?php echo esc_textarea( $this->options_text( $field['options'] ) ); ?></textarea>
            </td>
            <td>
                <button type="button" class="button sbp-remove-budget-value-field"><?php esc_html_e( 'Remove', 'simple-budget-plugin-sbp' ); ?></button>
            </td>
        </tr>
        <?php
    }

    private function row_template() {
        ob_start();
        $this->render_row( '__INDEX__', $this->blank_field() );
        return ob_get_clean();
    }

    private function blank_field() {
        return [
            'key'        => '',
            'label'      => '',
            'type'       => 'number',
            'unit'       => '',
            'display'    => false,
            'filterable' => true,
            'options'    => [],
        ];
    }

    private function options_text( $options ) {
        $rows = [];

        foreach ( (array) $options as $option ) {
            if ( is_array( $option ) && ! empty( $option['value'] ) ) {
                $rows[] = $option['value'] . '|' . ( $option['label'] ?? $option['value'] );
            }
        }

        return implode( "\n", $rows );
    }
}
