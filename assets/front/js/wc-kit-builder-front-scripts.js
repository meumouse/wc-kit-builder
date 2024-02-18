jQuery(document).ready( function($) {
    var select_name = $('#wc_kit_builder_select_variation').attr('name');
    var select_variation = $('select#'+ select_name).val();
    var target_kit = $('input[value="'+ select_variation +'"]');

    if (select_variation !== "") {
        $('.kit-item').children(target_kit).attr('checked', true);
        $(target_kit).parent('.kit-item').addClass('active');
        $('.woo-custom-installments-group.deprecated').hide();
    }

    $('.kit-item').click( function() {
        $('.kit-item').removeClass('active');
        $(this).addClass('active');
        
        var value = $(this).children('input.hidden').val();
        var kit_title = $(this).children('.kit-item-info').children('.kit-quantity').text();
        var attribute_name = $(this).data('attribute-name');

        $('select[name="' + attribute_name + '"]').val(value).change();
        $('h4.variation-title p').text(kit_title);
        $('#hubgo-shipping-calc-button').click();
    });
});