
/*
 Author: LoginRadius Team
 Version: 1.0
 Author URI: http://www.LoginRadius.com
 */

jQuery(function ($) {

    function hideAndShowElement(element, inputBoxName) {
        if (element.is(':checked')) {
            jQuery(inputBoxName).show();
        } else {
            jQuery(inputBoxName).hide();
        }
    }

    if ($('#lr-bluehornet-enable').is(':checked')) {
        $(".lr-option-disabled-hr.lr-bluehornet").hide();
    } else {
        $(".lr-option-disabled-hr.lr-bluehornet").show();
    }

    $('#lr-bluehornet-enable').change(function () {
        if ($(this).is(':checked')) {
            $(".lr-option-disabled-hr.lr-bluehornet").hide();
        } else {
            $(".lr-option-disabled-hr.lr-bluehornet").show();
        }
    });

});