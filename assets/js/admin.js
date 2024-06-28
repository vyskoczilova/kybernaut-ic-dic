"use strict";

(function ($) {
  $(document).ready(function () {
    // enable active only if ares_check filled out
    var ares_check = $('#woolab_icdic_ares_check');
    var vies_check = $('#woolab_icdic_vies_check');

    if (ares_check.length) {
      enableAresActive(ares_check);
      enableIgnoreCheckFailActive(ares_check, vies_check);
      ares_check.change(function () {
        enableAresActive(ares_check);
        enableIgnoreCheckFailActive(ares_check, vies_check);
      });
    }

    if (vies_check.length) {
      enableIgnoreCheckFailActive(ares_check, vies_check);
      vies_check.change(function () {
        enableIgnoreCheckFailActive(ares_check, vies_check);
      });
    }

    if (vies_check.length) {
      if (!woolab.soap) {
        vies_check.prop("disabled", true).prop("checked", false);
      }
    }

    if ($('.woolab-icdic-notice').length) {
      $('.woolab-icdic-notice').on('click', '.notice-dismiss', function () {
        $.ajax({
          url: ajaxurl,
          data: {
            action: "woolab_icdic_notice_dismiss"
          }
        });
      });
    }
  });

  function enableAresActive(ares_check) {
    var active = $('#woolab_icdic_ares_fill');

    if (ares_check.prop("checked") == true) {
      active.prop("disabled", false);
    } else {
      active.prop("disabled", true).prop("checked", false);
    }
  }

  function enableIgnoreCheckFailActive(ares_check, vies_check) {
    var checkbox = $('#woolab_icdic_ignore_check_fail');

    if (ares_check.prop('checked') || vies_check.prop('checked')) {
      checkbox.prop('disabled', false);
    } else {
      checkbox.prop('disabled', true).prop('checked', false);
    }
  }
})(jQuery);