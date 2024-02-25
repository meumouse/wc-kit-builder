<?php

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Front styles class
 * 
 * @since 1.0.0
 * @version 1.0.0
 * @package MeuMouse.com
 */
class Wc_Kit_Builder_Front {

    /**
     * Construct function
     * 
     * @since 1.0.0
     * @return void
     */
    public function __construct() {
        add_action( 'woocommerce_before_add_to_cart_button', array( $this, 'display_kit_selector_on_product_page' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts_for_product_kits' ) );

        $options = get_option('woo-custom-installments-setting');

        if ( isset( $options['remove_price_range'] ) && $options['remove_price_range'] !== 'yes' && !is_admin() ) {
            add_filter( 'woocommerce_variable_price_html', array( $this, 'starting_from_variable_product_price' ), 10, 2 );
            add_filter( 'woocommerce_variable_sale_price_html', array( $this, 'starting_from_variable_product_price' ), 10, 2 );
            add_action( 'wp_enqueue_scripts', array( $this, 'update_price_on_select_variation' ), 10 );
        }
    }


    /**
     * Display kit selector on product page
     * 
     * @since 1.0.0
     * @return void
     */
    public function display_kit_selector_on_product_page() {
        global $product;
    
        // check if product is variable
        if ( $product->is_type( 'variable' ) ) {
            // get all product variations
            $variations = $product->get_available_variations();
    
            // storage attibute names on array
            $attribute_names = array();
    
            // itarate about variations
            foreach ( $variations as $variation ) {
                $variation_id = $variation['variation_id'];
                $kit_variation = get_post_meta( $variation_id, '_kit_variation', true );
    
                //  check if kit option is enabled
                if ( $kit_variation === 'yes' ) {
                    // get variation attribute
                    $variation_attributes = $variation['attributes'];
    
                    // add attribute names on array
                    foreach ( $variation_attributes as $attribute_name => $attribute_value ) {
                        // remove attribute prefix
                        $clean_attribute_name = str_replace( 'attribute_', '', $attribute_name );
    
                        // add attribute name on array if not is present
                        if ( ! in_array( $clean_attribute_name, $attribute_names ) ) {
                            $attribute_names[] = $clean_attribute_name;
                        }
                    }
                }
            }
    
            // display kit selector if has attributes
            if ( ! empty( $attribute_names ) ) {
                ?>
                <div id="wc_kit_builder_select_variation" name="<?php echo $clean_attribute_name ?>">
                <div class="wc-kit-builder-variation-swatches">
                    <?php
    
                    // iterate about names of attributes for display title
                    foreach ( $attribute_names as $attribute_name ) {
                        ?>
                        <h4 class="variation-title"><?php echo wc_attribute_label( $attribute_name ); ?>: <p class="selected-kit"></p></h4>
                        <?php
                    }
                    ?>
                    <div class="kit-options">
                    <?php
    
                    // iterate about variations for display kit options
                    foreach ( $variations as $variation ) {
                        $variation_id = $variation['variation_id'];
                        $kit_variation = get_post_meta( $variation_id, '_kit_variation', true );
                        $kit_quantity = get_post_meta( $variation_id, '_quantidade_kit', true );
                        $unit_of_measure = get_post_meta( $variation_id, '_unit_of_measure', true );
                        $kit_description = get_post_meta( $variation_id, '_kit_description', true );
    
                        // check if kit is enabled for this variation
                        if ( $kit_variation === 'yes' ) {
                            // get attribute variation
                            $variation_attributes = $variation['attributes'];
    
                            // get price variation
                            $variation_price = $variation['display_price'];
    
                            $unit_price = $variation_price / $kit_quantity;
    
                            // display kit item
                            foreach ( $variation_attributes as $attribute_name => $attribute_value ) {
                                ?>
                                <label class="kit-item" data-attribute-name="<?php echo $attribute_name ?>">
                                    <div class="kit-item-info">
                                        <span class="unit-price"><?php echo sprintf( __( '%s/%s', 'wc-kit-builder' ), wc_price( $unit_price ), $unit_of_measure ) ?></span>
                                        <span class="kit-quantity"><?php echo sprintf( $kit_description, $kit_quantity ) ?></span>
                                    </div>
                                    <input type="radio" class="hidden" name="<?php echo $attribute_name ?>_<?php echo $attribute_value ?>" value="<?php echo esc_attr( $attribute_value ) ?>"/>
                                </label>
                                <?php
                            }
                        }
                    }
                    ?>
                    </div>
                </div>
                </div>
                <?php
            }
        }
    }
    

    /**
     * Add scripts for product kits
     * 
     * @since 1.0.0
     * @return void
     */
    public function enqueue_scripts_for_product_kits() {
        wp_enqueue_style( 'wc-kit-builder-front-styles', WC_KIT_BUILDER_ASSETS . 'front/css/wc-kit-builder-front-styles.css', array(), WC_KIT_BUILDER_VERSION );
        wp_enqueue_script( 'wc-kit-builder-front-scripts', WC_KIT_BUILDER_ASSETS . 'front/js/wc-kit-builder-front-scripts.js', array('jquery'), WC_KIT_BUILDER_VERSION );
    }


    /**
     * Replace range price for "A partir de"
     * 
     * @return string
     * @since 1.1.0
     * @version 1.2.0
     * @package MeuMouse.com
     */
    public function starting_from_variable_product_price( $price, $product ) {
        if ( $product->is_type( 'variable' ) ) {
            $variations = $product->get_available_variations();
            $max_kit_price = 0;
    
            // iterate over the variations to find the largest kit variation
            foreach ( $variations as $variation ) {
                $variation_id = $variation['variation_id'];
                $kit_variation = get_post_meta( $variation_id, '_kit_variation', true );
                $kit_quantity = get_post_meta( $variation_id, '_quantidade_kit', true );
    
                if ( $kit_variation === 'yes' ) {
                    $variation_price = $variation['display_price'];
                    $max_kit_price = max( $max_kit_price, $variation_price );
                    $range_price_unit = $max_kit_price / $kit_quantity;
                }
            }
    
            // If there is at least one kit variation, calculate the price based on the largest kit variation
            if ( $max_kit_price > 0 ) {
                $price = '<span class="wc-kit-builder-range-price-text">'. sprintf( esc_html( 'A partir de %s', 'wc-kit-builder' ), wc_price( $range_price_unit ) ) .'</span>';
            }
        }
    
        return $price;
    }    


    /**
     * Enqueue script for update price on select variation
     * 
     * @since 1.1.0
     * @return void
     */
    public function update_price_on_select_variation() {
        $product_id = get_the_ID();
        $product = wc_get_product( $product_id );

        if ( $product && is_a( $product, 'WC_Product' ) ) {
            if ( $product->is_type( 'variable' ) && $product->get_variation_price( 'min' ) !== $product->get_variation_price( 'max' ) ) {
                wp_enqueue_script( 'wc-kit-builder-range-price', WC_KIT_BUILDER_ASSETS . 'front/js/wc-kit-builder-range-price.js', array('jquery'), WC_KIT_BUILDER_VERSION );
            }
        }
    }
}

new Wc_Kit_Builder_Front();