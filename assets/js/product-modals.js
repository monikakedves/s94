/**
 * Studio94 – Product Quick View modal
 *
 * Everything related to the quick-view pop-up:
 *  - modal open / close handlers
 *  - injecting cart form + price / rating into the modal
 *  - WAPF (Advanced Product Fields) conditional logic + totals inside the modal
 *
 * Depends on (defined in main.js, loaded first):
 *  - window.studio94InitQtyButtons
 *  - initVariationDropdowns()   (optional, guarded)
 */
(function ($) {
  function initQuickView() {
    window.studio94InitQuickView = initQuickView;
  }

  function evaluateModalWapf() {
    var $wrap = jQuery("#qv-cart-wrap");
    if (!$wrap.length) return;
    var $group = $wrap.find(".wapf-field-group");
    if (!$group.length) return;

    $group.find("[data-dependencies]").each(function () {
      var $field = jQuery(this);
      var deps;
      try {
        deps = JSON.parse($field.attr("data-dependencies"));
      } catch (err) {
        return;
      }
      var $row = $field.closest(".wapf-field-row");
      var $container = $field.closest(".wapf-field-container");
      var visible = deps.some(function (group) {
        return group.rules.every(function (rule) {
          var $depInput = $wrap.find('[data-field-id="' + rule.field + '"]');
          var val = $depInput.val();
          switch (rule.condition) {
            case "==":
              return val === rule.value;
            case "!=":
              return val !== rule.value;
            default:
              return false;
          }
        });
      });
      if ($row.length) $row.toggleClass("wapf-hide", !visible);
      if ($container.length) $container.toggleClass("wapf-hide", !visible);
      if (!visible) {
        $field.removeAttr("required");
      } else if ($field.data("is-required")) {
        $field.attr("required", "required");
      }
    });

    var $totals = $group.next(".wapf-product-totals");
    if (!$totals.length) $totals = $wrap.find(".wapf-product-totals");
    if (!$totals.length) return;

    var basePrice = parseFloat($totals.attr("data-product-price")) || 0;
    var optionsTotal = 0;
    $group.find(".wapf-input").each(function () {
      var $input = jQuery(this);
      var $row = $input.closest(".wapf-field-row");
      if ($row.length && $row.is(":hidden")) return;
      if ($input.is("select")) {
        var $opt = $input.find("option:selected");
        optionsTotal += parseFloat($opt.attr("data-wapf-price")) || 0;
      } else if (
        $input.is('input[type="checkbox"]') ||
        $input.is('input[type="radio"]')
      ) {
        if ($input.is(":checked"))
          optionsTotal += parseFloat($input.attr("data-wapf-price")) || 0;
      }
    });

    var grandTotal = basePrice + optionsTotal;
    var currencySymbol = "£";
    var format = function (n) {
      return (
        '<span class="woocommerce-Price-amount amount"><bdi>' +
        currencySymbol +
        n.toFixed(2) +
        "</bdi></span>"
      );
    };

    $totals.find(".wapf-product-total").html(format(basePrice));
    $totals.find(".wapf-options-total").html(format(optionsTotal));
    $totals.find(".wapf-grand-total").html(format(grandTotal));

    var $qvPrice = jQuery("#qv-price");
    if ($qvPrice.length) {
      requestAnimationFrame(function () {
        $qvPrice.html('<p class="price">' + format(grandTotal) + "</p>");
      });
    }
  }

  if (typeof jQuery !== "undefined") {
    jQuery(document)
      .off("click.s94qv", ".quick-view-btn")
      .on("click.s94qv", ".quick-view-btn", function (e) {
        e.preventDefault();
        var $btn = jQuery(this);
        var $li = $btn.closest("li");
        var $data = $li.find(".qv-data");
        if (!$data.length) return;
        var url = $data.attr("data-url") || $data.data("url");
        if (window.innerWidth <= 768) {
          window.location.href = url;
          return;
        }

        var title = $data.attr("data-title") || $data.data("title") || "";
        var img = $data.attr("data-img") || $data.data("img") || "";
        var desc = $data.html() || "";
        var priceHtml = $li.find(".qv-price-data").html() || "";
        var ratingHtml = $li.find(".qv-rating-data").html() || "";
        var cartDataText = $li.find(".qv-cart-data").text() || "";

        jQuery("#qv-title").text(title);
        jQuery("#qv-img-src").attr("src", img);
        jQuery("#qv-link").attr("href", url);
        jQuery("#qv-desc").html(desc);

        var $qvRating = jQuery("#qv-rating");
        if (ratingHtml.trim() !== "") {
          $qvRating.html(ratingHtml).css("display", "flex");
        } else {
          $qvRating.html("").css("display", "none");
        }

        window.s94ModalDefaultPrice = priceHtml;
        jQuery("#qv-price").html(priceHtml);

        var $cartWrap = jQuery("#qv-cart-wrap");
        if (cartDataText.trim() !== "") {
          $cartWrap.html(cartDataText);
          if (typeof window.studio94InitQtyButtons === "function")
            window.studio94InitQtyButtons($cartWrap[0]);

          var $form = $cartWrap.find("form.cart");
          if ($form.length) {
            if (typeof $form.wc_variation_form === "function")
              $form.wc_variation_form();
            if (typeof initVariationDropdowns === "function")
              initVariationDropdowns();
            $cartWrap.find(".custom-dropdown-list li").on("click", function () {
              setTimeout(evaluateModalWapf, 10);
              setTimeout(evaluateModalWapf, 100);
            });
            $cartWrap
              .find(".wapf-input, select")
              .on("change input", function () {
                setTimeout(evaluateModalWapf, 10);
                setTimeout(evaluateModalWapf, 100);
              });
            $form.on("found_variation reset_data", function () {
              setTimeout(evaluateModalWapf, 10);
              setTimeout(evaluateModalWapf, 100);
            });
            setTimeout(evaluateModalWapf, 50);
            setTimeout(evaluateModalWapf, 200);
          }
        } else {
          $cartWrap.empty();
        }
        jQuery("#qv-modal").addClass("active");
        jQuery("body").css("overflow", "hidden");
      });

    jQuery(document)
      .off("click.s94qvclose")
      .on("click.s94qvclose", ".qv-close", function () {
        jQuery("#qv-modal").removeClass("active");
        jQuery("body").css("overflow", "auto");
      });
    jQuery(document)
      .off("click.s94qvbg")
      .on("click.s94qvbg", "#qv-modal", function (e) {
        if (e.target === this) {
          jQuery("#qv-modal").removeClass("active");
          jQuery("body").css("overflow", "auto");
        }
      });
    jQuery(document)
      .off("keydown.s94qvkey")
      .on("keydown.s94qvkey", function (e) {
        if (e.key === "Escape" && jQuery("#qv-modal").hasClass("active")) {
          jQuery("#qv-modal").removeClass("active");
          jQuery("body").css("overflow", "auto");
        }
      });
  }

  initQuickView();
})(window.jQuery || null);
