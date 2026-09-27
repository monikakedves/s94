(function ($) {
  function runStudio94ScriptsPart2() {
    const tabContainer = document.querySelector(".product-tabs");
    const tabBtns = document.querySelectorAll(".tab-btn");
    const tabContents = document.querySelectorAll(".tab-content");
    if (tabContainer && tabBtns.length > 0) {
      let slider = document.createElement("div");
      slider.className = "tab-slider";
      tabContainer.appendChild(slider);
      function moveSlider(btn) {
        slider.style.width = btn.offsetWidth + "px";
        slider.style.left = btn.offsetLeft + "px";
      }
      const activeBtn = document.querySelector(".tab-btn.active") || tabBtns[0];
      moveSlider(activeBtn);
      window.addEventListener("resize", () => {
        const current = document.querySelector(".tab-btn.active");
        if (current) moveSlider(current);
      });
      tabBtns.forEach((btn) => {
        btn.addEventListener("click", function () {
          tabBtns.forEach((b) => b.classList.remove("active"));
          tabContents.forEach((c) => c.classList.remove("active"));
          this.classList.add("active");
          moveSlider(this);
          const target = document.getElementById(this.dataset.target);
          if (target) target.classList.add("active");
        });
      });
    }

    const mainImageContainer = document.querySelector(
      ".custom-product-gallery__image",
    );
    const mainImg = mainImageContainer
      ? mainImageContainer.querySelector("img")
      : null;
    const thumbnails = document.querySelectorAll(".flex-control-thumbs img");
    let lightboxImages = [],
      currentLbIndex = 0;
    if (mainImg) {
      if (thumbnails.length === 0)
        lightboxImages.push(mainImg.getAttribute("data-full"));
      else {
        thumbnails.forEach((thumb, index) => {
          lightboxImages.push(thumb.getAttribute("data-full"));
          thumb.addEventListener("click", function () {
            thumbnails.forEach((t) => t.classList.remove("active-thumb"));
            this.classList.add("active-thumb");
            const fullSizeUrl = this.getAttribute("data-full");
            mainImg.src = fullSizeUrl;
            mainImg.setAttribute("data-full", fullSizeUrl);
            currentLbIndex = index;
          });
        });
      }
      mainImageContainer.style.overflow = "hidden";
      mainImageContainer.style.position = "relative";
      mainImageContainer.addEventListener("mousemove", function (e) {
        const rect = mainImageContainer.getBoundingClientRect();
        const x = e.clientX - rect.left,
          y = e.clientY - rect.top;
        const xPercent = (x / rect.width) * 100,
          yPercent = (y / rect.height) * 100;
        let scaleCalc = mainImg.naturalWidth / rect.width;
        if (scaleCalc < 1) scaleCalc = 1;
        mainImg.style.transformOrigin = `${xPercent}% ${yPercent}%`;
        mainImg.style.transform = `scale(${scaleCalc})`;
      });
      mainImageContainer.addEventListener("mouseleave", function () {
        mainImg.style.transform = "scale(1)";
        mainImg.style.transformOrigin = "center center";
      });
    }

    const lightbox = document.getElementById("custom-lightbox");
    const lbImgElement = lightbox
      ? lightbox.querySelector(".lightbox-main-img")
      : null;
    const lbWrapper = lightbox
      ? lightbox.querySelector(".lightbox-content-wrapper")
      : null;
    const btnPrev = lightbox ? lightbox.querySelector(".lb-prev") : null;
    const btnNext = lightbox ? lightbox.querySelector(".lb-next") : null;
    const closeBtn = lightbox ? lightbox.querySelector(".close-btn") : null;

    function showLightboxImage(index) {
      if (index < 0) index = lightboxImages.length - 1;
      if (index >= lightboxImages.length) index = 0;
      currentLbIndex = index;
      if (lbImgElement) lbImgElement.src = lightboxImages[currentLbIndex];
    }
    function closeLightbox() {
      if (lightbox) lightbox.classList.remove("active");
      document.body.style.overflow = "auto";
    }

    if (mainImageContainer && lightbox) {
      mainImageContainer.addEventListener("click", function (e) {
        e.preventDefault();
        e.stopPropagation();
        showLightboxImage(currentLbIndex);
        lightbox.classList.add("active");
        document.body.style.overflow = "hidden";
      });
    }

    if (lightbox) {
      if (closeBtn) closeBtn.addEventListener("click", closeLightbox);
      if (lbWrapper)
        lbWrapper.addEventListener("click", function (e) {
          if (e.target === lbWrapper) closeLightbox();
        });
      if (btnPrev)
        btnPrev.addEventListener("click", () =>
          showLightboxImage(currentLbIndex - 1),
        );
      if (btnNext)
        btnNext.addEventListener("click", () =>
          showLightboxImage(currentLbIndex + 1),
        );
      document.addEventListener("keydown", function (e) {
        if (!lightbox.classList.contains("active")) return;
        if (e.key === "Escape") closeLightbox();
        if (e.key === "ArrowLeft") showLightboxImage(currentLbIndex - 1);
        if (e.key === "ArrowRight") showLightboxImage(currentLbIndex + 1);
      });
    }
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", runStudio94ScriptsPart2);
  } else {
    runStudio94ScriptsPart2();
  }

  if (typeof jQuery !== "undefined") {
    jQuery(document).ready(function ($) {
      $(".single_add_to_cart_button").attr("type", "button");

      $("form.cart").on("submit", function (e) {
        e.preventDefault();
        e.stopImmediatePropagation();
        return false;
      });

      $("form.cart").on("keypress", "input.qty", function (e) {
        if (e.which === 13) {
          e.preventDefault();
          $(this).closest("form").find(".single_add_to_cart_button").click();
        }
      });

      $(document)
        .on("mouseenter", ".single_add_to_cart_button.added", function () {
          $(this).data("orig-text", $(this).text());
          $(this).html("✕&nbsp;&nbsp;Remove from cart");
          $(this)[0].style.setProperty(
            "background-color",
            "#d9534f",
            "important",
          );
          $(this)[0].style.setProperty("border-color", "#d9534f", "important");
          $(this)[0].style.setProperty("color", "#ffffff", "important");
        })
        .on("mouseleave", ".single_add_to_cart_button.added", function () {
          $(this).text($(this).data("orig-text") || "✓ Added to cart");
          $(this)[0].style.removeProperty("background-color");
          $(this)[0].style.removeProperty("border-color");
          $(this)[0].style.removeProperty("color");
        });

      var $btn = $(".single_add_to_cart_button");
      if (
        $btn.text().trim() === "Added to cart" ||
        $btn.text().trim() === "Cart updated" ||
        $btn.text().trim() === "Added to cart"
      ) {
        $btn.addClass("added").text("Added to cart");
      }

      $(document).on(
        "change input",
        "form.cart .qty, form.cart .variations select, form.cart .wapf-input",
        function () {
          var $button = $(this)
            .closest("form.cart")
            .find(".single_add_to_cart_button");
          if ($button.hasClass("added")) {
            $button
              .removeClass("added")
              .addClass("is-update")
              .text("Update cart");
            $button[0].style.removeProperty("background-color");
            $button[0].style.removeProperty("border-color");
            $button[0].style.removeProperty("color");
          }
        },
      );

      $(document).on(
        "show_variation reset_data",
        "form.variations_form",
        function () {
          var $button = $(this).find(".single_add_to_cart_button");
          if ($button.hasClass("added")) {
            $button
              .removeClass("added")
              .addClass("is-update")
              .text("Update cart");
            $button[0].style.removeProperty("background-color");
            $button[0].style.removeProperty("border-color");
            $button[0].style.removeProperty("color");
          }
        },
      );

      $(document)
        .off("click", ".single_add_to_cart_button")
        .on("click", ".single_add_to_cart_button", function (e) {
          e.preventDefault();
          e.stopImmediatePropagation();

          var $btn = $(this);
          var $form = $btn.closest("form.cart");

          if ($btn.hasClass("loading")) return false;
          if ($btn.hasClass("ajax_add_to_cart")) return true;
          if (
            $form.hasClass("variations_form") &&
            !$form.find('input[name="variation_id"]').val()
          )
            return true;

          var productId =
            $form.find('input[name="product_id"]').val() ||
            $form.find('button[name="add-to-cart"]').val() ||
            $btn.val();
          var variationId = $form.find('input[name="variation_id"]').val() || 0;
          var removeId = parseInt(variationId) > 0 ? variationId : productId;

          var cfg = typeof studio94Cart !== "undefined" ? studio94Cart : {};
          var ajaxUrl = cfg.ajaxUrl || "/wp-admin/admin-ajax.php";
          var wcAjaxUrl = cfg.wcAjaxUrl
            ? cfg.wcAjaxUrl.replace("%%endpoint%%", "add_to_cart")
            : "/?wc-ajax=add_to_cart";

          if ($btn.hasClass("added")) {
            $btn.removeClass("added").addClass("loading").text("Removing...");
            $btn[0].style.removeProperty("background-color");
            $btn[0].style.removeProperty("border-color");
            $btn[0].style.removeProperty("color");

            $.ajax({
              type: "POST",
              url: ajaxUrl,
              data: {
                action: "studio94_remove_from_cart",
                nonce: cfg.removeNonce,
                product_id: removeId,
              },
              dataType: "json",
              success: function (res) {
                $btn.removeClass("loading");
                if (res && res.success) {
                  $btn.text("Add to cart");
                  $(document.body).trigger("removed_from_cart", [
                    res.data.fragments,
                    res.data.cart_hash,
                    $btn,
                  ]);
                } else {
                  window.location.reload();
                }
              },
              error: function () {
                window.location.reload();
              },
            });
            return false;
          }

          var isUpdate = $btn.hasClass("is-update");
          $btn
            .removeClass("is-update")
            .addClass("loading")
            .text(isUpdate ? "Updating..." : "Adding...");

          var doAddToCart = function () {
            var data = $form.serialize();

            if (data.indexOf("product_id=") === -1) {
              data += "&product_id=" + encodeURIComponent(productId);
            }

            $.ajax({
              type: "POST",
              url: wcAjaxUrl,
              data: data,
              success: function (response) {
                if (!response) {
                  window.location.reload();
                  return;
                }
                if (response.error && response.product_url) {
                  window.location.href = response.product_url;
                  return;
                }

                $btn.removeClass("loading").addClass("added");
                if (isUpdate) {
                  $btn.text("Cart updated");
                  setTimeout(function () {
                    if ($btn.hasClass("added")) $btn.text("Added to cart");
                  }, 2000);
                } else {
                  $btn.text("Added to cart");
                }
                $(document.body).trigger("added_to_cart", [
                  response.fragments,
                  response.cart_hash,
                  $btn,
                ]);
              },
              error: function () {
                window.location.reload();
              },
            });
          };

          if (isUpdate) {
            $.ajax({
              type: "POST",
              url: ajaxUrl,
              data: {
                action: "studio94_remove_from_cart",
                nonce: cfg.removeNonce,
                product_id: removeId,
              },
              dataType: "json",
              complete: function () {
                doAddToCart();
              },
            });
          } else {
            doAddToCart();
          }

          return false;
        });

      $(document)
        .off("submit", "form.cart")
        .on("submit", "form.cart", function (e) {
          e.preventDefault();
          return false;
        });
    });

    jQuery(document.body).on(
      "added_to_cart",
      function (e, fragments, hash, $btn) {
        if (!$btn || $btn.length === 0 || $btn.is(".s94-cart-btn")) return;
      },
    );
  }
})(window.jQuery || null);

jQuery(document).ready(function ($) {
  window.updateTopPrice = function () {
    var $priceWrap = $(".price-wrap");
    var customRange = $priceWrap.attr("data-custom-range");
    var $wapfTotal = $(".wapf-grand-total");

    var isIdSelected = $('select[name^="wapf[field_"]').val() !== "";

    if (isIdSelected && $wapfTotal.length) {
      $priceWrap.find(".price").html($wapfTotal.html());
    } else if (customRange) {
      $priceWrap
        .find(".price")
        .html(
          '<span class="woocommerce-Price-amount amount"><bdi>' +
            customRange +
            "</bdi></span>",
        );
    }
  };

  function refreshTopPrice() {
    if (typeof updateTopPrice === "function") updateTopPrice();
  }

  $(document.body).on(
    "added_to_cart",
    function (e, fragments, cart_hash, $btn) {
      if ($btn && $btn.hasClass("single_add_to_cart_button")) {
        if ($(".s94-add-another").length === 0) {
          $btn.after(
            '<a href="#" class="s94-add-another" style="display:block; text-align:center; margin-top:15px; font-weight:700; color:var(--brand-pink); text-decoration:none;">+ Add another pet</a>',
          );
        } else {
          $(".s94-add-another").show();
        }
      }
    },
  );

  $(document).on("click", ".s94-add-another", function (e) {
    e.preventDefault();

    $('.wapf-input[type="text"]').val("");

    var $btn = $(".single_add_to_cart_button");
    $btn.removeClass("added is-update").text("Add to cart");
    $btn[0].style.removeProperty("background-color");
    $btn[0].style.removeProperty("border-color");
    $btn[0].style.removeProperty("color");

    $(this).hide();

    if (typeof updateTopPrice === "function") updateTopPrice();
  });

  $(document).on(
    "wapf/totals_calculated found_variation hide_variation reset_data",
    function () {
      setTimeout(refreshTopPrice, 30);
    },
  );

  $(document).on(
    "change input",
    ".wapf-input, .variations select",
    function () {
      setTimeout(refreshTopPrice, 30);
      setTimeout(refreshTopPrice, 150);
    },
  );
});
