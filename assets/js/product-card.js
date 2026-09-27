(function () {
  function runProductCardScripts() {
    const shopForm = document.querySelector("form.s94-custom-filters");
    const gridContainer = document.querySelector("ul.custom-related");
    if (!shopForm || !gridContainer) return;

    const orderSelect = document.querySelector(
      "form.woocommerce-ordering select.orderby",
    );
    const catSelect = document.querySelector(".s94-filter-category select");
    const minPriceInput = document.querySelector('input[name="min_price"]');
    const maxPriceInput = document.querySelector('input[name="max_price"]');
    const instockToggle = document.querySelector('input[name="instock_post"]');
    const instockPill = document.querySelector(".s94-filter-instock-toggle");

    let filterTimeout = null;

    function createCustomDropdown(selectEl, wrapperClass) {
      if (!selectEl) return;
      const existingWrap = selectEl.parentNode.querySelector(
        "." + wrapperClass,
      );
      if (existingWrap) existingWrap.remove();

      const customDropdown = document.createElement("div");
      customDropdown.className = wrapperClass + " custom-dropdown-wrap";

      const selectedDisplay = document.createElement("div");
      selectedDisplay.className = "custom-dropdown-selected";
      const activeOption =
        selectEl.options[selectEl.selectedIndex] || selectEl.options[0];
      selectedDisplay.innerHTML = `<span>${activeOption.innerHTML}</span><span class="chevron"></span>`;

      const optionsList = document.createElement("ul");
      optionsList.className = "custom-dropdown-list";

      Array.from(selectEl.options).forEach((option) => {
        const li = document.createElement("li");
        li.innerHTML = option.innerHTML;
        li.dataset.value = option.value;
        if (option.selected) li.classList.add("active");

        li.addEventListener("click", function (e) {
          e.stopPropagation();
          selectedDisplay.querySelector("span").innerHTML = this.innerHTML;
          selectEl.value = this.dataset.value;

          optionsList
            .querySelectorAll("li")
            .forEach((el) => el.classList.remove("active"));
          this.classList.add("active");
          customDropdown.classList.remove("open");

          performAjaxFilter();
        });

        optionsList.appendChild(li);
      });

      customDropdown.appendChild(selectedDisplay);
      customDropdown.appendChild(optionsList);
      selectEl.parentNode.appendChild(customDropdown);
      selectEl.style.display = "none";

      selectedDisplay.addEventListener("click", function (e) {
        e.stopPropagation();
        document.querySelectorAll(".custom-dropdown-wrap").forEach((d) => {
          if (d !== customDropdown) d.classList.remove("open");
        });
        customDropdown.classList.toggle("open");
      });
    }

    document.addEventListener("click", function () {
      document
        .querySelectorAll(".custom-dropdown-wrap")
        .forEach((d) => d.classList.remove("open"));
    });

    if (orderSelect)
      createCustomDropdown(orderSelect, "custom-orderby-dropdown");
    if (catSelect) createCustomDropdown(catSelect, "custom-category-dropdown");

    function debounceAjaxFilter() {
      clearTimeout(filterTimeout);
      filterTimeout = setTimeout(() => performAjaxFilter(), 600);
    }

    if (minPriceInput)
      minPriceInput.addEventListener("input", debounceAjaxFilter);
    if (maxPriceInput)
      maxPriceInput.addEventListener("input", debounceAjaxFilter);

    if (instockToggle) {
      instockToggle.addEventListener("change", function () {
        if (instockPill)
          instockPill.classList.toggle("is-on", instockToggle.checked);
        performAjaxFilter();
      });
    }

    function performAjaxFilter(reset = false) {
      if (!gridContainer) return;
      gridContainer.style.opacity = "0.4";
      gridContainer.style.pointerEvents = "none";

      let url = new URL(window.location.href.split("?")[0]);

      if (!reset) {
        const cat = catSelect ? catSelect.value : "";
        const minP = minPriceInput ? minPriceInput.value : "";
        const maxP = maxPriceInput ? maxPriceInput.value : "";
        const inStock = instockToggle && instockToggle.checked ? "1" : "";
        const orderby = orderSelect ? orderSelect.value : "";

        if (cat) url.searchParams.set("product_cat", cat);
        if (minP) url.searchParams.set("min_price", minP);
        if (maxP) url.searchParams.set("max_price", maxP);
        if (inStock) url.searchParams.set("instock_post", inStock);
        if (orderby) url.searchParams.set("orderby", orderby);
      } else {
        if (catSelect) {
          catSelect.value = "";
          const catWrap = document.querySelector(".custom-category-dropdown");
          if (catWrap) {
            catWrap.querySelector(".custom-dropdown-selected span").innerHTML =
              catSelect.options[0].innerHTML;
            catWrap
              .querySelectorAll("li")
              .forEach((li) => li.classList.remove("active"));
            catWrap.querySelector("li").classList.add("active");
          }
        }
        if (orderSelect) {
          orderSelect.value = "menu_order";
          const ordWrap = document.querySelector(".custom-orderby-dropdown");
          if (ordWrap) {
            ordWrap.querySelector(".custom-dropdown-selected span").innerHTML =
              orderSelect.options[0].innerHTML;
            ordWrap
              .querySelectorAll("li")
              .forEach((li) => li.classList.remove("active"));
            ordWrap.querySelector("li").classList.add("active");
          }
        }
        if (minPriceInput) minPriceInput.value = "";
        if (maxPriceInput) maxPriceInput.value = "";
        if (instockToggle) {
          instockToggle.checked = false;
          if (instockPill) instockPill.classList.remove("is-on");
        }
      }

      fetch(url.toString())
        .then((response) => response.text())
        .then((html) => {
          const parser = new DOMParser();
          const doc = parser.parseFromString(html, "text/html");

          const newGrid = doc.querySelector("ul.custom-related");
          if (newGrid && newGrid.innerHTML.trim() !== "") {
            gridContainer.innerHTML = newGrid.innerHTML;
            if (typeof window.studio94InitProductSliders === "function") {
              window.studio94InitProductSliders();
            }
            if (typeof window.studio94InitCartButtons === "function") {
              window.studio94InitCartButtons(gridContainer);
            }
          } else {
            gridContainer.innerHTML =
              '<li class="s94-no-products">No products found matching your criteria.</li>';
          }

          const currentCount = document.querySelector(
            ".woocommerce-result-count",
          );
          const newCount = doc.querySelector(".woocommerce-result-count");
          if (currentCount && newCount)
            currentCount.innerHTML = newCount.innerHTML;
          else if (currentCount) currentCount.innerHTML = "";

          const currentPagination = document.querySelector(".pagination");
          const newPagination = doc.querySelector(".pagination");
          if (currentPagination && newPagination)
            currentPagination.innerHTML = newPagination.innerHTML;
          else if (currentPagination) currentPagination.innerHTML = "";

          gridContainer.style.opacity = "1";
          gridContainer.style.pointerEvents = "auto";
          window.history.pushState(
            { path: url.toString() },
            "",
            url.toString(),
          );
        })
        .catch(() => {
          window.location.href = url.toString();
        });
    }

    const clearBtn = document.querySelector(".s94-clear-btn");
    if (clearBtn) {
      clearBtn.addEventListener("click", function (e) {
        e.preventDefault();
        performAjaxFilter(true);
      });
    }
  }

  function ensureCartIcons(btn) {
    if (btn.tagName.toLowerCase() === "a") return;
    if (
      btn.querySelector(".icon-add") &&
      btn.querySelector(".icon-added") &&
      btn.querySelector(".icon-remove")
    ) {
      return;
    }
    btn.innerHTML =
      '<span class="cart-icon icon-add" aria-hidden="true"></span>' +
      '<span class="cart-icon icon-added" aria-hidden="true"></span>' +
      '<span class="cart-icon icon-remove" aria-hidden="true"></span>' +
      '<span class="screen-reader-text">Add to cart</span>';
  }

  function initCartButtons(scope) {
    const root = scope || document;
    const cfg = typeof studio94Cart !== "undefined" ? studio94Cart : {};

    root.querySelectorAll(".s94-cart-btn").forEach((btn) => {
      if (btn.tagName.toLowerCase() === "a") return;

      ensureCartIcons(btn);
      if (btn.getAttribute("data-s94-bound") === "1") return;
      btn.setAttribute("data-s94-bound", "1");

      btn.addEventListener("click", function (e) {
        e.preventDefault();
        e.stopImmediatePropagation();
        if (btn.classList.contains("is-loading")) return;

        if (btn.classList.contains("in-cart")) {
          removeFromCart(btn);
        } else {
          addToCart(btn);
        }
      });
    });

    function addToCart(btn) {
      if (typeof jQuery === "undefined" || !cfg.wcAjaxUrl) return;

      const productId = btn.getAttribute("data-product_id");
      const quantity = btn.getAttribute("data-quantity") || 1;

      if (!productId) return;

      btn.classList.add("is-loading");

      jQuery.ajax({
        type: "POST",
        url: cfg.wcAjaxUrl.replace("%%endpoint%%", "add_to_cart"),
        data: {
          product_id: productId,
          quantity: quantity,
        },
        dataType: "json",
        success: function (response) {
          btn.classList.remove("is-loading");
          if (!response || response.error) {
            if (response && response.product_url) {
              window.location.href = response.product_url;
            }
            return;
          }
          ensureCartIcons(btn);
          btn.classList.add("in-cart");
          btn.setAttribute("aria-label", "Remove from cart");
          jQuery(document.body).trigger("added_to_cart", [
            response.fragments,
            response.cart_hash,
            jQuery(btn),
          ]);
        },
        error: function () {
          btn.classList.remove("is-loading");
        },
      });
    }

    function removeFromCart(btn) {
      if (typeof jQuery === "undefined" || !cfg.ajaxUrl || !cfg.removeNonce)
        return;

      const productId = btn.getAttribute("data-product_id");
      if (!productId) return;

      btn.classList.add("is-loading");

      jQuery.ajax({
        type: "POST",
        url: cfg.ajaxUrl,
        data: {
          action: "studio94_remove_from_cart",
          nonce: cfg.removeNonce,
          product_id: productId,
        },
        dataType: "json",
        success: function (response) {
          btn.classList.remove("is-loading");
          if (response && response.success) {
            ensureCartIcons(btn);
            btn.classList.remove("in-cart");
            btn.setAttribute("aria-label", "Add to cart");
            jQuery(document.body).trigger("removed_from_cart", [
              response.data.fragments,
              response.data.cart_hash,
              jQuery(btn),
            ]);
          }
        },
        error: function () {
          btn.classList.remove("is-loading");
        },
      });
    }
  }

  function initProductSliders() {
    document.querySelectorAll(".s94-slider-wrap").forEach((wrap) => {
      if (wrap.dataset.sliderBound) return;
      wrap.dataset.sliderBound = "1";

      const track = wrap.querySelector(".s94-slider-track");
      const slides = wrap.querySelectorAll(".s94-slide");
      const prev = wrap.querySelector(".s94-prev");
      const next = wrap.querySelector(".s94-next");

      if (!track || slides.length <= 1) return;

      let currentIndex = 0,
        startX = 0,
        currentTranslate = 0,
        prevTranslate = 0;
      let isDragging = false,
        animationID,
        dragged = false;

      const setPositionByIndex = () => {
        currentTranslate = currentIndex * -100;
        prevTranslate = currentTranslate;
        track.style.transform = `translateX(${currentTranslate}%)`;
      };

      // Hover logic
      wrap.addEventListener("mouseenter", () => {
        if (currentIndex === 0 && !isDragging) {
          currentIndex = 1;
          setPositionByIndex();
        }
      });
      wrap.addEventListener("mouseleave", () => {
        if (isDragging) dragEnd();
        if (currentIndex === 1) {
          currentIndex = 0;
          setPositionByIndex();
        }
      });

      // Arrow logic
      if (prev)
        prev.addEventListener("click", (e) => {
          e.preventDefault();
          e.stopPropagation();
          currentIndex =
            currentIndex > 0 ? currentIndex - 1 : slides.length - 1;
          setPositionByIndex();
        });
      if (next)
        next.addEventListener("click", (e) => {
          e.preventDefault();
          e.stopPropagation();
          currentIndex =
            currentIndex < slides.length - 1 ? currentIndex + 1 : 0;
          setPositionByIndex();
        });

      // Drag logic
      wrap.addEventListener("mousedown", dragStart);
      wrap.addEventListener("touchstart", dragStart, { passive: true });
      wrap.addEventListener("mouseup", dragEnd);
      wrap.addEventListener("touchend", dragEnd);
      wrap.addEventListener("mousemove", dragAction);
      wrap.addEventListener("touchmove", dragAction, { passive: true });
      wrap.addEventListener("click", (e) => {
        if (dragged) {
          e.preventDefault();
          e.stopPropagation();
        }
      });

      function dragStart(e) {
        if (e.target.closest(".s94-slider-arrow")) return;
        if (e.type === "mousedown") e.preventDefault();
        isDragging = true;
        dragged = false;
        startX = e.type.includes("mouse") ? e.pageX : e.touches[0].clientX;
        animationID = requestAnimationFrame(animation);
        track.style.transition = "none";
      }

      function dragAction(e) {
        if (isDragging) {
          const currentX = e.type.includes("mouse")
            ? e.pageX
            : e.touches[0].clientX;
          const diff = ((currentX - startX) / wrap.offsetWidth) * 100;
          if (Math.abs(diff) > 2) dragged = true;
          currentTranslate = prevTranslate + diff;
        }
      }

      function dragEnd() {
        if (!isDragging) return;
        isDragging = false;
        cancelAnimationFrame(animationID);
        track.style.transition = "transform 0.4s cubic-bezier(0.25, 1, 0.5, 1)";

        const movedBy = currentTranslate - prevTranslate;
        if (movedBy < -15 && currentIndex < slides.length - 1)
          currentIndex += 1;
        else if (movedBy > 15 && currentIndex > 0) currentIndex -= 1;

        setPositionByIndex();
        setTimeout(() => {
          dragged = false;
        }, 50);
      }

      function animation() {
        track.style.transform = `translateX(${currentTranslate}%)`;
        if (isDragging) requestAnimationFrame(animation);
      }
    });
  }

  window.studio94InitCartButtons = initCartButtons;
  window.studio94InitProductSliders = initProductSliders;

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", function () {
      initCartButtons(document);
      runProductCardScripts();
      initProductSliders();
    });
  } else {
    initCartButtons(document);
    runProductCardScripts();
    initProductSliders();
  }
})();
