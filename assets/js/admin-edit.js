"use strict";

(function ($) {
  $(document).ready(function () {
    // Country based
    var country = $('#_billing_country');
    based_on_country(country.val());
    country.change(function () {
      based_on_country(country.val());
    });
  });

  function based_on_country(country) {
    var dic_dph = $('._billing_billing_dic_dph_field ');

    switch (country) {
      case 'SK':
        dic_dph.show();
        break;

      case 'CZ':
        dic_dph.hide();
        break;

      default:
        dic_dph.hide();
    }
  }
})(jQuery);