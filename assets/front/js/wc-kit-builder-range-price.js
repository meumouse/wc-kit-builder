/**
 * Replace range price
 * 
 * @since 1.1.0
 */
jQuery(document).ready( function($) {
	var get_price = get_original_price();

	function get_original_price() {
		var container_price = $('.original-price').parent('.price');

		container_price.closest('div').addClass('range-price');
		original_price = container_price.html();

        return original_price;
    }

	function prevent_duplicate_container() {
		if ( $('.original-price').siblings('.woo-custom-installments-group').length > 0 ) {
			$('.summary .price, .range-price .price').siblings('.woo-custom-installments-group.deprecated').addClass('not-selected-variation');
			$('.summary .price, .range-price .price').siblings('.woo-custom-installments-group.deprecated').hide();
		}
	}
  
	$(document).on('found_variation', 'form.variations_form', function(event, variation) {
	  var variation_price = $('.woocommerce-variation-price .price').html();

	  $('.summary .price, .range-price .price').html(variation_price);
	  $('.summary .price, .range-price .price').find('.woo-custom-installments-group').removeClass('deprecated');
	});

	$('form.variations_form').on('change', 'select', function() {
		if ($(this).val() === '') {
		  $('.summary .price, .range-price .price').html(get_price);
		}

		prevent_duplicate_container();
	});
  
	$('a.reset_variations').click( function(event) {
	  event.preventDefault();
	  $('.summary .price, .range-price .price').html(get_price);

	  $('.summary .price, .range-price .price').siblings('.woo-custom-installments-group.deprecated').show();
	});
});