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

    initQtyButtons(document);
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", runStudio94Scripts);
  } else {
    runStudio94Scripts();
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
