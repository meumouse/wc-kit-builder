<?php

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Initialize plugin class
 * 
 * @since 1.0.0
 * @version 1.0.0
 * @package MeuMouse.com
 */
class Wc_Kit_Builder_Init {

    /**
     * Construct function
     * 
     * @since 1.0.0
     * @return void
     */
    public function __construct() {
        add_action( 'woocommerce_variation_options', array( $this, 'add_product_kit_checkbox' ), 10, 3 );
        add_action( 'woocommerce_save_product_variation', array( $this, 'save_product_kit_checkbox' ), 10, 2 );
        add_action( 'woocommerce_variation_options_pricing', array( $this, 'display_kit_quantity_input' ), 10, 3 );
        add_action( 'woocommerce_save_product_variation', array( $this, 'save_kit_quantity_input' ), 10, 2 );
    }


    /**
     * Add checkbox "Kit" on product variation options
     * 
     * @since 1.0.0
     * @param $loop | Position in the loop
     * @param array $variation_data | Variation data
     * @param $variation | Post data
     * @return void
     */
    public function add_product_kit_checkbox( $loop, $variation_data, $variation ) {
        $kit_checked = get_post_meta( $variation->ID, '_kit_variation', true ); ?>

        <label>
        <?php echo esc_html( 'Kit', 'wc-kit-builder' ); ?>
        <input type="checkbox" class="checkbox variable_product_kit" name="_kit_variation[<?php echo $loop ?>]" <?php checked( $kit_checked === 'yes'); ?> >
            </label>
        <?php
    }


    /**
     * Save state of product kit checkbox on variation options
     * 
     * @since 1.0.0
     * @param $variation_id | Variation ID
     * @param $loop | Position in the loop
     * @return void
     */
    public function save_product_kit_checkbox( $variation_id, $loop ) {
        $checkbox = isset( $_POST['_kit_variation'][$loop] ) ? 'yes' : 'no';
        update_post_meta( $variation_id, '_kit_variation', $checkbox );
    }


    /**
     * Display quantity input set for product kit
     * 
     * @since 1.0.0
     * @param $loop | Position in the loop
     * @param $variation_data | Variation data
     * @param $variation | Post data
     * @return void
     */
    public function display_kit_quantity_input( $loop, $variation_data, $variation ) {
        $kit_checked = get_post_meta( $variation->ID, '_kit_variation', true );

        if ( $kit_checked === 'yes' ) {
            $quantidade_kit = get_post_meta( $variation->ID, '_quantidade_kit', true );
            $unit_of_measure = get_post_meta( $variation->ID, '_unit_of_measure', true );
            $kit_description = get_post_meta( $variation->ID, '_kit_description', true ); ?>

            <div class="options_group form-row form-row-full">
                <p class="form-field">
                    <label for="quantidade_kit"><?php _e( 'Quantidade de unidades no kit', 'wc-kit-builder' ); ?></label>
                    <input type="number" class="short" name="quantidade_kit[<?php echo $loop; ?>]" id="quantidade_kit" value="<?php echo esc_attr( $quantidade_kit ); ?>" step="1" min="1" placeholder="<?php _e( 'Por exemplo: 2', 'wc-kit-builder' ); ?>" />
                </p>
                <p class="form-field">
                    <label for="unit_of_measure"><?php _e( 'Unidade de medida', 'wc-kit-builder' ); ?></label>
                    <input type="text" class="short" name="unit_of_measure[<?php echo $loop; ?>]" id="unit_of_measure" value="<?php echo esc_attr( $unit_of_measure ); ?>" placeholder="<?php _e( 'Por exemplo: UN', 'wc-kit-builder' ); ?>" />
                </p>
                <p class="form-field">
                    <label for="kit_description"><?php _e( 'Descrição do kit', 'wc-kit-builder' ); ?></label>
                    <textarea name="kit_description[<?php echo $loop; ?>]" id="kit_description" placeholder="<?php _e( 'Por exemplo: Kit com %s UN', 'wc-kit-builder' ); ?>"><?php echo esc_textarea( $kit_description ); ?></textarea>
                </p>
            </div>
            <?php
        }
    }


    /**
     * Save quantity of product kit input
     * 
     * @since 1.0.0
     * @param $variation_id | Variation ID
     * @param $loop | Position in the loop
     * @return void
     */
    public function save_kit_quantity_input( $variation_id, $loop ) {
        $quantidade_kit = $_POST['quantidade_kit'][$loop];
        $unit_of_measure = $_POST['unit_of_measure'][$loop];
        $kit_description = $_POST['kit_description'][$loop];

        update_post_meta( $variation_id, '_quantidade_kit', esc_attr( $quantidade_kit ) );
        update_post_meta( $variation_id, '_unit_of_measure', esc_attr( $unit_of_measure ) );
        update_post_meta( $variation_id, '_kit_description', esc_textarea( $kit_description ) );
    }
}

new Wc_Kit_Builder_Init();