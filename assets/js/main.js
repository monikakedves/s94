/**
 * Studio94 – Main theme script
 *
 * Everything that is NOT the quick-view modal:
 *  - qty +/- buttons
 *  - logo flip, product tabs, gallery / zoom / lightbox
 *  - single-product AJAX add / update / remove cart
 *  - custom variation dropdowns
 *  - top price refresh
 *
 * The quick-view modal lives in product-modals.js.
 */
(function ($) {
  function initQtyButtons(container) {
    if (!container) return;
    const qtyInputs = container.querySelectorAll(".quantity input.qty");
    qtyInputs.forEach((input) => {
      if (input.parentNode.querySelector(".qty-btn")) return;
      const minusBtn = document.createElement("button");
      minusBtn.type = "button";
      minusBtn.className = "qty-btn minus";
      minusBtn.innerText = "−";
      const plusBtn = document.createElement("button");
      plusBtn.type = "button";
      plusBtn.className = "qty-btn plus";
      plusBtn.innerText = "+";
      input.parentNode.insertBefore(minusBtn, input);
      input.parentNode.insertBefore(plusBtn, input.nextSibling);
      minusBtn.addEventListener("click", () => {
        let val = parseFloat(input.value) || 1,
          min = parseFloat(input.min) || 1;
        if (val > min) {
          input.value = val - 1;
          input.dispatchEvent(new Event("change", { bubbles: true }));
        }
      });
      plusBtn.addEventListener("click", () => {
        let val = parseFloat(input.value) || 1,
          max = parseFloat(input.max) || 9999;
        if (val < max) {
          input.value = val + 1;
          input.dispatchEvent(new Event("change", { bubbles: true }));
        }
      });
    });
  }

  // Shared with product-modals.js (quick view builds its own cart form)
  window.studio94InitQtyButtons = initQtyButtons;

  function runStudio94Scripts() {
    const logoImg = document.getElementById("dynamic-logo");
    const logoWrapper = document.getElementById("logo-link");
    if (logoImg && logoWrapper) {
      logoWrapper.style.perspective = "1000px";
      const flipContainer = document.createElement("div");
      flipContainer.className = "logo-flip-container";
      logoImg.parentNode.insertBefore(flipContainer, logoImg);
      flipContainer.appendChild(logoImg);

      const basePath =
        "https://studio94.uk/uploads/themes/studio94/assets/images/";
      const logos = [
        "logo_v1.svg",
        "logo_v2.svg",
        "logo_v3.svg",
        "logo_v4.svg",
      ];
      const cookieMatch = document.cookie.match(
        /(^| )studio94_active_logo=([^;]+)/,
      );
      let currentLogoIndex = cookieMatch ? parseInt(cookieMatch[2]) : 0;
      if (
        isNaN(currentLogoIndex) ||
        currentLogoIndex < 0 ||
        currentLogoIndex > 3
      ) {
        currentLogoIndex = 0;
      }

      logoImg.src = basePath + logos[currentLogoIndex];

      const depthLayers = [];
      for (let i = 1; i <= 5; i++) {
        let layer = document.createElement("img");
        layer.src = logoImg.src;
        layer.className = "logo-depth-layer " + (logoImg.className || "");
        layer.style.transform = `translateZ(-${i}px)`;
        flipContainer.appendChild(layer);
        depthLayers.push(layer);
      }

      flipContainer.addEventListener("mouseenter", function () {
        if (window.innerWidth <= 768) return;
        if (flipContainer.classList.contains("is-animating")) return;
        flipContainer.classList.add("is-animating");
        currentLogoIndex = (currentLogoIndex + 1) % logos.length;
        document.cookie =
          "studio94_active_logo=" +
          currentLogoIndex +
          "; path=/; max-age=31536000";
        flipContainer.classList.add("flip-out");
        setTimeout(function () {
          const newSrc = basePath + logos[currentLogoIndex];
          logoImg.src = newSrc;
          depthLayers.forEach((layer) => (layer.src = newSrc));
          flipContainer.classList.remove("flip-out");
          flipContainer.classList.add("flip-in");
          void flipContainer.offsetWidth;
          flipContainer.classList.remove("flip-in");
          setTimeout(function () {
            flipContainer.classList.remove("is-animating");
          }, 250);
        }, 250);
      });
    }

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

    initQtyButtons(document);

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
    document.addEventListener("DOMContentLoaded", runStudio94Scripts);
  } else {
    runStudio94Scripts();
  }

  // --- NUCLEAR SINGLE PRODUCT AJAX BLOCK ---
  if (typeof jQuery !== "undefined") {
    jQuery(document).ready(function ($) {
      // 1. NEUTRALIZE NATIVE FORM SUBMISSION
      // Change type to "button" to prevent native HTML submit, but KEEP the 'value' attribute for data processing
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

      // 2. JS HOVER EFFECT FOR REMOVE FROM CART (Maintains perfect padding & forces red background)
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

      // 3. APPLY CORRECT INITIAL STATE
      var $btn = $(".single_add_to_cart_button");
      if (
        $btn.text().trim() === "Added to cart" ||
        $btn.text().trim() === "Cart updated" ||
        $btn.text().trim() === "Added to cart"
      ) {
        $btn.addClass("added").text("Added to cart");
      }

      // If inputs change, switch back to 'Update' state
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

      // Brutally intercept the button click directly and KILL the native form submission
      $(document)
        .off("click", ".single_add_to_cart_button")
        .on("click", ".single_add_to_cart_button", function (e) {
          e.preventDefault();
          e.stopImmediatePropagation();

          var $btn = $(this);
          var $form = $btn.closest("form.cart");

          if ($btn.hasClass("loading")) return false; // Stop multi-clicks
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

          // SCENARIO 1: REMOVE
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

          // SCENARIO 2: ADD OR UPDATE
          var isUpdate = $btn.hasClass("is-update");
          $btn
            .removeClass("is-update")
            .addClass("loading")
            .text(isUpdate ? "Updating..." : "Adding...");

          var doAddToCart = function () {
            var data = $form.serialize();

            // FIX: DO NOT append 'add-to-cart' parameter. Sending 'add-to-cart' forces the WooCommerce init hook
            // AND the AJAX endpoint to process the request, resulting in 2 items being added.
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

      // Completely unbind form submit to ensure WC doesn't trigger a secondary refresh
      $(document)
        .off("submit", "form.cart")
        .on("submit", "form.cart", function (e) {
          e.preventDefault();
          return false;
        });
    });

    // Suppress WC's native button text reset
    jQuery(document.body).on(
      "added_to_cart",
      function (e, fragments, hash, $btn) {
        if (!$btn || $btn.length === 0 || $btn.is(".s94-cart-btn")) return;
      },
    );
  }
})(window.jQuery || null);

function initVariationDropdowns() {
  const variationSelects = document.querySelectorAll(
    ".variations select, .wapf-field-select select",
  );
  variationSelects.forEach((selectEl) => {
    const existingWrap = selectEl.parentNode.querySelector(
      ".custom-variation-dropdown",
    );
    if (existingWrap) existingWrap.remove();

    const customDropdown = document.createElement("div");
    customDropdown.className = "custom-variation-dropdown custom-dropdown-wrap";

    const selectedDisplay = document.createElement("div");
    selectedDisplay.className = "custom-dropdown-selected";
    const activeOption =
      selectEl.options[selectEl.selectedIndex] || selectEl.options[0];
    selectedDisplay.innerHTML = `<span>${activeOption.innerHTML}</span><span class="chevron"></span>`;

    const optionsList = document.createElement("ul");
    optionsList.className = "custom-dropdown-list";

    Array.from(selectEl.options).forEach((option) => {
      if (option.value === "") return;
      const li = document.createElement("li");
      li.innerHTML = option.innerHTML;
      li.dataset.value = option.value;
      if (option.selected) li.classList.add("active");

      li.addEventListener("click", function (e) {
        e.stopPropagation();
        selectedDisplay.querySelector("span").innerHTML = this.innerHTML;
        selectEl.value = this.dataset.value;

        if (typeof jQuery !== "undefined") {
          jQuery(selectEl).trigger("change").trigger("input");
        } else {
          selectEl.dispatchEvent(new Event("change", { bubbles: true }));
        }

        optionsList
          .querySelectorAll("li")
          .forEach((el) => el.classList.remove("active"));
        this.classList.add("active");
        customDropdown.classList.remove("open");
      });
      optionsList.appendChild(li);
    });

    customDropdown.appendChild(selectedDisplay);
    customDropdown.appendChild(optionsList);
    selectEl.style.display = "none";
    selectEl.parentNode.appendChild(customDropdown);

    selectedDisplay.addEventListener("click", function (e) {
      e.stopPropagation();
      document.querySelectorAll(".custom-dropdown-wrap").forEach((d) => {
        if (d !== customDropdown) {
          d.classList.remove("open");
          let p = d.closest("td, .wapf-field-row");
          if (p) p.style.zIndex = "";
        }
      });
      customDropdown.classList.toggle("open");
      let parent = customDropdown.closest("td, .wapf-field-row");
      if (parent) {
        parent.style.zIndex = customDropdown.classList.contains("open")
          ? "9999"
          : "";
      }
    });
  });
}

document.addEventListener("DOMContentLoaded", initVariationDropdowns);

if (typeof jQuery !== "undefined") {
  jQuery(document).on("click touchstart", function (e) {
    if (!jQuery(e.target).closest(".custom-dropdown-wrap").length) {
      jQuery(".custom-dropdown-wrap.open").each(function () {
        jQuery(this).removeClass("open");
        let $parent = jQuery(this).closest("td, .wapf-field-row");
        if ($parent.length) $parent.css("z-index", "");
      });
    }
  });

  jQuery(document).on(
    "woocommerce_update_variation_values reset_data",
    ".variations_form",
    function () {
      setTimeout(initVariationDropdowns, 50);
    },
  );
}

jQuery(document).ready(function ($) {
  // Define the missing function
  window.updateTopPrice = function () {
    var $priceWrap = $(".price-wrap");
    var customRange = $priceWrap.attr("data-custom-range"); // Pulls "£15 - £20" from the HTML
    var $wapfTotal = $(".wapf-grand-total"); // Pulls the calculated total from Advanced Product Fields

    // Check if an ID type (or any WAPF select field) is actually chosen
    var isIdSelected = $('select[name^="wapf[field_"]').val() !== "";

    if (isIdSelected && $wapfTotal.length) {
      // Update top price with the exact calculated total
      $priceWrap.find(".price").html($wapfTotal.html());
    } else if (customRange) {
      // Revert to the default range if cleared
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

  // 1. Show the "Add Another" link after a successful addition
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

  // 2. Handle the "Add Another" click
  $(document).on("click", ".s94-add-another", function (e) {
    e.preventDefault();

    // Clear the custom text fields (leaves shape/color dropdowns alone)
    $('.wapf-input[type="text"]').val("");

    // Reset the Add to Cart button back to default
    var $btn = $(".single_add_to_cart_button");
    $btn.removeClass("added is-update").text("Add to cart");
    $btn[0].style.removeProperty("background-color");
    $btn[0].style.removeProperty("border-color");
    $btn[0].style.removeProperty("color");

    // Hide this link until the next successful addition
    $(this).hide();

    // Refresh the top price
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
