(function($) {
    function runStudio94Scripts() {
        // 1. Tab Switching
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

            tabBtns.forEach(btn => {
                btn.addEventListener("click", function() {
                    tabBtns.forEach(b => b.classList.remove("active"));
                    tabContents.forEach(c => c.classList.remove("active"));
                    this.classList.add("active");
                    moveSlider(this);
                    const target = document.getElementById(this.dataset.target);
                    if (target) target.classList.add("active");
                });
            });
        }

        // 2. Qty Buttons
        function initQtyButtons(container) {
            const qtyInputs = container.querySelectorAll(".quantity input.qty");
            qtyInputs.forEach(input => {
                if (input.parentNode.querySelector(".qty-btn")) return;
                
                const minusBtn = document.createElement("button");
                minusBtn.type = "button"; minusBtn.className = "qty-btn minus"; minusBtn.innerText = "−";
                const plusBtn = document.createElement("button");
                plusBtn.type = "button"; plusBtn.className = "qty-btn plus"; plusBtn.innerText = "+";
                
                input.parentNode.insertBefore(minusBtn, input);
                input.parentNode.insertBefore(plusBtn, input.nextSibling);
                
                minusBtn.addEventListener("click", () => {
                    let val = parseFloat(input.value) || 1, min = parseFloat(input.min) || 1;
                    if (val > min) {
                        input.value = val - 1;
                        input.dispatchEvent(new Event("change", { bubbles: true }));
                    }
                });
                plusBtn.addEventListener("click", () => {
                    let val = parseFloat(input.value) || 1, max = parseFloat(input.max) || 9999;
                    if (val < max) {
                        input.value = val + 1;
                        input.dispatchEvent(new Event("change", { bubbles: true }));
                    }
                });
            });
        }
        initQtyButtons(document);

        // 3. Lightbox
        const mainImageContainer = document.querySelector(".custom-product-gallery__image");
        const mainImg = mainImageContainer ? mainImageContainer.querySelector("img") : null;
        const thumbnails = document.querySelectorAll(".flex-control-thumbs img");
        let lightboxImages = [], currentLbIndex = 0;

        if (mainImg) {
            if (thumbnails.length === 0) lightboxImages.push(mainImg.getAttribute("data-full"));
            else {
                thumbnails.forEach((thumb, index) => {
                    lightboxImages.push(thumb.getAttribute("data-full"));
                    thumb.addEventListener("click", function() {
                        thumbnails.forEach(t => t.classList.remove("active-thumb"));
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
            mainImageContainer.addEventListener("mousemove", function(e) {
                const rect = mainImageContainer.getBoundingClientRect();
                const x = e.clientX - rect.left, y = e.clientY - rect.top;
                const xPercent = (x / rect.width) * 100, yPercent = (y / rect.height) * 100;
                let scaleCalc = mainImg.naturalWidth / rect.width;
                if (scaleCalc < 1) scaleCalc = 1;
                mainImg.style.transformOrigin = `${xPercent}% ${yPercent}%`;
                mainImg.style.transform = `scale(${scaleCalc})`;
            });
            mainImageContainer.addEventListener("mouseleave", function() {
                mainImg.style.transform = "scale(1)";
                mainImg.style.transformOrigin = "center center";
            });
        }

        const lightbox = document.getElementById("custom-lightbox");
        const lbImgElement = lightbox ? lightbox.querySelector(".lightbox-main-img") : null;
        const lbWrapper = lightbox ? lightbox.querySelector(".lightbox-content-wrapper") : null;
        const btnPrev = lightbox ? lightbox.querySelector(".lb-prev") : null;
        const btnNext = lightbox ? lightbox.querySelector(".lb-next") : null;
        const closeBtn = lightbox ? lightbox.querySelector(".close-btn") : null;

        function showLightboxImage(index) {
            if (index < 0) index = lightboxImages.length - 1;
            if (index >= lightboxImages.length) index = 0;
            currentLbIndex = index;
            if(lbImgElement) lbImgElement.src = lightboxImages[currentLbIndex];
        }
        function closeLightbox() {
            if(lightbox) lightbox.classList.remove("active");
            document.body.style.overflow = "auto";
        }

        if (mainImageContainer && lightbox) {
            mainImageContainer.addEventListener("click", function(e) {
                e.preventDefault(); e.stopPropagation();
                showLightboxImage(currentLbIndex);
                lightbox.classList.add("active");
                document.body.style.overflow = "hidden";
            });
        }

        if (lightbox) {
            if(closeBtn) closeBtn.addEventListener("click", closeLightbox);
            if(lbWrapper) lbWrapper.addEventListener("click", function(e) { if (e.target === lbWrapper) closeLightbox(); });
            if(btnPrev) btnPrev.addEventListener("click", () => showLightboxImage(currentLbIndex - 1));
            if(btnNext) btnNext.addEventListener("click", () => showLightboxImage(currentLbIndex + 1));
            document.addEventListener("keydown", function(e) {
                if (!lightbox.classList.contains("active")) return;
                if (e.key === "Escape") closeLightbox();
                if (e.key === "ArrowLeft") showLightboxImage(currentLbIndex - 1);
                if (e.key === "ArrowRight") showLightboxImage(currentLbIndex + 1);
            });
            let touchStartX = 0, touchEndX = 0;
            lightbox.addEventListener("touchstart", e => { touchStartX = e.changedTouches[0].screenX; }, {passive: true});
            lightbox.addEventListener("touchend", e => {
                touchEndX = e.changedTouches[0].screenX;
                if (touchEndX < touchStartX - 50) showLightboxImage(currentLbIndex + 1);
                if (touchEndX > touchStartX + 50) showLightboxImage(currentLbIndex - 1);
            }, {passive: true});
        }

        // 4. Quick View (Modified to disable on mobile)
        const qvModal = document.getElementById("qv-modal");
        const qvClose = document.querySelector(".qv-close");

        if (qvModal) {
            document.querySelectorAll(".quick-view-btn").forEach(btn => {
                btn.addEventListener("click", function(e) {
                    const li = this.closest("li");
                    const data = li.querySelector(".qv-data");
                    
                    // If on mobile (<=768px), redirect to product page instead of opening modal
                    if (window.innerWidth <= 768) {
                        e.preventDefault();
                        window.location.href = data.dataset.url;
                        return;
                    }

                    const pData = li.querySelector(".qv-price-data");
                    const cartData = li.querySelector(".qv-cart-data");

                    document.getElementById("qv-title").innerText = data.dataset.title;
                    document.getElementById("qv-img-src").src = data.dataset.img;
                    document.getElementById("qv-link").href = data.dataset.url;
                    document.getElementById("qv-desc").innerHTML = data.innerHTML;
                    document.getElementById("qv-price").innerHTML = pData.innerHTML;

                    const cartWrap = document.getElementById("qv-cart-wrap");
                    if (cartData && cartWrap) {
                        cartWrap.innerHTML = cartData.textContent;
                        initQtyButtons(cartWrap);
                    }
                    qvModal.classList.add("active");
                    document.body.style.overflow = "hidden";
                });
            });
            
            if(qvClose) {
                qvClose.addEventListener("click", () => {
                    qvModal.classList.remove("active");
                    document.body.style.overflow = "auto";
                });
            }
            
            qvModal.addEventListener("click", function(e) {
                if (e.target === qvModal) {
                    qvModal.classList.remove("active");
                    document.body.style.overflow = "auto";
                }
            });
            
            document.addEventListener("keydown", function(e) {
                if (qvModal.classList.contains("active") && e.key === "Escape") {
                    qvModal.classList.remove("active");
                    document.body.style.overflow = "auto";
                }
            });
        }
    }

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", runStudio94Scripts);
    } else {
        runStudio94Scripts();
    }

    // 5. BRUTAL AJAX SINGLE ADD TO CART INTERCEPT (HIJACKS THE BUTTON CLICK)
    if (typeof jQuery !== "undefined" && typeof wc_add_to_cart_params !== "undefined") {
        jQuery(document).on("click", ".single_add_to_cart_button", function(e) {
            var $btn = jQuery(this);
            
            // Do not intercept if it's the related products loop button, native woo handles that
            if ($btn.hasClass("ajax_add_to_cart")) return true; 
            
            var $form = $btn.closest("form.cart");
            if (!$form.length) return true; // Let default behavior happen if no form
            
            // Allow native validation for variations if needed
            if ($form.hasClass("variations_form") && !$form.find('input[name="variation_id"]').val()) {
                return true;
            }
            
            e.preventDefault(); // Stop the form submission
            
            // Get necessary IDs and Quantities
            var productId = $btn.val() || $btn.attr("value") || $form.find('input[name="add-to-cart"]').val();
            var variationId = $form.find('input[name="variation_id"]').val() || 0;
            var quantity = $form.find('input[name="quantity"]').val() || 1;
            
            // Serialize form and explicitly add product_id (Required for Woo Native AJAX)
            var data = $form.serialize();
            data += "&product_id=" + encodeURIComponent(productId) + "&quantity=" + encodeURIComponent(quantity);
            if (variationId) {
                data += "&variation_id=" + encodeURIComponent(variationId);
            }
            
            $btn.removeClass("added").addClass("loading");
            
            jQuery.ajax({
                type: "POST",
                url: wc_add_to_cart_params.wc_ajax_url.replace("%%endpoint%%", "add_to_cart"),
                data: data,
                success: function(response) {
                    if (!response) return window.location.reload();
                    
                    // If WooCommerce throws an error, fallback to page reload
                    if (response.error && response.product_url) { 
                        window.location = response.product_url; 
                        return; 
                    }
                    
                    // Trigger the event that updates your sidebar cart counter!
                    jQuery(document.body).trigger("added_to_cart", [response.fragments, response.cart_hash, $btn]);
                    $btn.removeClass("loading").addClass("added");
                },
                error: function() {
                    window.location.reload(); // Fallback
                }
            });
        });
    }

})(window.jQuery || null);

jQuery(document).ready(function($) {
    $(document).on('added_to_cart', function(event, fragments, cart_hash, $button) {
        // Target specifically the button inside the quick view modal
        if ($button && $button.closest('.quick-view-modal').length) {
            
            // Change button text and add checkmark
            $button.html('&#10003; Added');
            $button.removeClass('loading');
            
            // Hide the dynamically appended "View Cart" link
            $button.siblings('.added_to_cart').hide();
        }
    });
});