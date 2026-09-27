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

    // 1. Force the WAPF group into a strict 2-column grid
    $group.css({
      display: "grid",
      "grid-template-columns": "1fr 1fr",
      gap: "15px",
      width: "100%",
      "box-sizing": "border-box",
    });

    // 2. Evaluate Conditional Logic
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

    // 3. Enforce 50/50 or 100% layout on visible rows based on your rules
    $group.find(".wapf-field-row:not(.wapf-hide)").each(function () {
      var $row = jQuery(this);
      var labelText = $row.find("label").text().toLowerCase();

      $row.css({ width: "100%", margin: "0", padding: "0" });

      if (labelText.indexOf("pin") > -1) {
        $row.css("grid-column", "1 / -1"); // PIN gets 100% width
      } else {
        $row.css("grid-column", "span 1"); // ID Type, Name, Address, Mobile get 50%
      }
    });

    // 4. Calculate Totals (Integrating Base Price + WAPF)
    var $totals = $group.next(".wapf-product-totals");
    if (!$totals.length) $totals = $wrap.find(".wapf-product-totals");
    if (!$totals.length) return;

    var basePrice = parseFloat($totals.attr("data-product-price")) || 0;
    if (basePrice === 0 && window.s94ModalDefaultPrice) {
      var defaultMatch = window.s94ModalDefaultPrice.match(/[\d\.]+/);
      if (defaultMatch) {
        basePrice = parseFloat(defaultMatch[0]);
      }
    }
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

    var currencySymbol = "£";
    var format = function (n) {
      return (
        '<span class="woocommerce-Price-amount amount"><bdi>' +
        currencySymbol +
        n.toFixed(2) +
        "</bdi></span>"
      );
    };

    var grandTotal = basePrice + optionsTotal;
    $totals.find(".wapf-product-total").html(format(basePrice));
    $totals.find(".wapf-options-total").html(format(optionsTotal));
    $totals.find(".wapf-grand-total").html(format(grandTotal));

    // 5. Update the UI Price (Handling Variable product Range vs Selected)
    var $qvPrice = jQuery("#qv-price");
    if ($qvPrice.length) {
      requestAnimationFrame(function () {
        var isVariable = $wrap.find(".variations_form").length > 0;
        var variationId = $wrap.find('input[name="variation_id"]').val();

        // If it's a variable product and NO variation is selected yet
        if (
          isVariable &&
          (!variationId || variationId === "0" || variationId === "")
        ) {
          if (optionsTotal > 0 && window.s94ModalCustomRange) {
            // Add WAPF options to the £15 - £20 range
            var matches = window.s94ModalCustomRange.match(/[\d\.]+/g);
            if (matches && matches.length >= 2) {
              var min = parseFloat(matches[0]) + optionsTotal;
              var max = parseFloat(matches[1]) + optionsTotal;
              $qvPrice.html(
                '<p class="price"><span class="woocommerce-Price-amount amount"><bdi>' +
                  currencySymbol +
                  min.toFixed(2) +
                  " - " +
                  currencySymbol +
                  max.toFixed(2) +
                  "</bdi></span></p>",
              );
            } else {
              $qvPrice.html(
                '<p class="price">' + window.s94ModalDefaultPrice + "</p>",
              );
            }
          } else {
            // Just show default range
            $qvPrice.html(
              '<p class="price">' + window.s94ModalDefaultPrice + "</p>",
            );
          }
        } else {
          // Standard product or Variation IS selected
          $qvPrice.html('<p class="price">' + format(grandTotal) + "</p>");
        }
      });
    }
  }

  if (typeof jQuery !== "undefined") {
    // Nuke all old buggy handlers to prevent conflict overlay closing
    jQuery(document).off("click.s94qvbg mousedown.s94qvbg");
    jQuery(document).off("click", "#qv-modal");
    jQuery(document).off("mousedown", "#qv-modal");

    // Open Modal Handler
    jQuery(document)
      .off("click.s94qv", ".quick-view-btn")
      .on("click.s94qv", ".quick-view-btn", function (e) {
        var $btn = jQuery(this);
        var $li = $btn.closest("li");
        var $data = $li.find(".qv-data");
        if (!$data.length) return;
        var url = $data.attr("data-url") || $data.data("url");

        if (window.innerWidth < 1024 || window.innerHeight < 720) {
          window.location.href = url;
          return;
        }
        e.preventDefault();

        var title = $data.attr("data-title") || $data.data("title") || "";
        var img = $data.attr("data-img") || $data.data("img") || "";
        var desc = $data.html() || "";
        var priceHtml = $li.find(".qv-price-data").html() || "";
        var ratingHtml = $li.find(".qv-rating-data").html() || "";
        var cartDataText = $li.find(".qv-cart-data").text() || "";
        var customRange = $li.find(".qv-custom-range-data").text() || "";

        jQuery("#qv-title").text(title);
        jQuery("#qv-img-src").attr("src", img);
        jQuery("#qv-desc").html(desc);

        // Delete the view product link so it never displays
        jQuery("#qv-link").remove();

        var $qvRating = jQuery("#qv-rating");
        if (ratingHtml.trim() !== "") {
          $qvRating.html(ratingHtml).css("display", "flex");
        } else {
          $qvRating.html("").css("display", "none");
        }

        window.s94ModalDefaultPrice = priceHtml;
        window.s94ModalCustomRange = customRange;
        jQuery("#qv-price").html(priceHtml);

        var $cartWrap = jQuery("#qv-cart-wrap");
        if (cartDataText.trim() !== "") {
          $cartWrap.html(cartDataText);
          if (typeof window.studio94InitQtyButtons === "function")
            window.studio94InitQtyButtons($cartWrap[0]);

          var $form = $cartWrap.find("form.cart");
          if ($form.length) {
            // Listen for WooCommerce variation changes to set correct base price
            $form.on("found_variation", function (e, variation) {
              $cartWrap
                .find(".wapf-product-totals")
                .attr("data-product-price", variation.display_price);
              evaluateModalWapf();
            });
            $form.on("reset_data", function () {
              $cartWrap
                .find(".wapf-product-totals")
                .attr("data-product-price", "0");
              evaluateModalWapf();
            });

            if (typeof $form.wc_variation_form === "function")
              $form.wc_variation_form();
            if (typeof initVariationDropdowns === "function")
              initVariationDropdowns();

            $cartWrap.find(".custom-dropdown-list li").on("click", function () {
              setTimeout(evaluateModalWapf, 10);
            });
            $cartWrap
              .find(".wapf-input, select")
              .on("change input", function () {
                setTimeout(evaluateModalWapf, 10);
              });

            setTimeout(evaluateModalWapf, 50);
          }
        } else {
          $cartWrap.empty();
        }

        jQuery("#qv-modal").addClass("active");
        jQuery("body").css("overflow", "hidden");
      });

    // Explicit close button handler
    jQuery(document)
      .off("click.s94qvclose")
      .on("click.s94qvclose", ".qv-close", function () {
        jQuery("#qv-modal").removeClass("active");
        jQuery("body").css("overflow", "auto");
      });

    // Vanilla JS bulletproof background click tracking
    var qvModalEl = document.getElementById("qv-modal");
    if (qvModalEl) {
      qvModalEl.onmousedown = function (e) {
        // ONLY triggers if clicking the exact overlay, never the content/dropdowns/buttons
        if (e.target === qvModalEl) {
          qvModalEl.classList.remove("active");
          document.body.style.overflow = "auto";
        }
      };
    }

    // Escape Key Handler
    jQuery(document)
      .off("keydown.s94qvkey")
      .on("keydown.s94qvkey", function (e) {
        if (e.key === "Escape" && jQuery("#qv-modal").hasClass("active")) {
          jQuery("#qv-modal").removeClass("active");
          jQuery("body").css("overflow", "auto");
        }
      });
  }

  // Resize Handler (Untouched per your instructions)
  jQuery(window)
    .off("resize.s94qvresize")
    .on("resize.s94qvresize", function () {
      if (jQuery("#qv-modal").hasClass("active")) {
        if (window.innerWidth < 1024 || window.innerHeight < 720) {
          jQuery("#qv-modal").removeClass("active");
          jQuery("body").css("overflow", "auto");
        }
      }
    });

  initQuickView();
})(window.jQuery || null);
