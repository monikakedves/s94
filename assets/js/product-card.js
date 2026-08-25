/**
 * STUDIO 94 — Product Card
 * Handles the shop grid: category/price/in-stock filters, sort dropdown,
 * AJAX re-fetching of the product grid, the "in stock only" toggle, and the
 * card add/remove-from-cart button.
 */
(function () {
	function runProductCardScripts() {
		const shopForm = document.querySelector('form.s94-custom-filters');
		const gridContainer = document.querySelector('ul.custom-related');
		if (!shopForm || !gridContainer) return; // not on a shop/archive page

		const orderSelect = document.querySelector('form.woocommerce-ordering select.orderby');
		const catSelect = document.querySelector('.s94-filter-category select');
		const minPriceInput = document.querySelector('input[name="min_price"]');
		const maxPriceInput = document.querySelector('input[name="max_price"]');
		const instockToggle = document.querySelector('input[name="instock_post"]');
		const instockPill = document.querySelector('.s94-filter-instock-toggle');

		let filterTimeout = null;

		function createCustomDropdown(selectEl, wrapperClass) {
			if (!selectEl) return;
			const existingWrap = selectEl.parentNode.querySelector('.' + wrapperClass);
			if (existingWrap) existingWrap.remove();

			const customDropdown = document.createElement('div');
			customDropdown.className = wrapperClass + ' custom-dropdown-wrap';

			const selectedDisplay = document.createElement('div');
			selectedDisplay.className = 'custom-dropdown-selected';
			const activeOption = selectEl.options[selectEl.selectedIndex] || selectEl.options[0];
			selectedDisplay.innerHTML = `<span>${activeOption.innerHTML}</span><span class="chevron"></span>`;

			const optionsList = document.createElement('ul');
			optionsList.className = 'custom-dropdown-list';

			Array.from(selectEl.options).forEach(option => {
				const li = document.createElement('li');
				li.innerHTML = option.innerHTML;
				li.dataset.value = option.value;
				if (option.selected) li.classList.add('active');

				li.addEventListener('click', function (e) {
					e.stopPropagation();
					selectedDisplay.querySelector('span').innerHTML = this.innerHTML;
					selectEl.value = this.dataset.value;

					optionsList.querySelectorAll('li').forEach(el => el.classList.remove('active'));
					this.classList.add('active');
					customDropdown.classList.remove('open');

					performAjaxFilter();
				});

				optionsList.appendChild(li);
			});

			customDropdown.appendChild(selectedDisplay);
			customDropdown.appendChild(optionsList);
			selectEl.parentNode.appendChild(customDropdown);
			selectEl.style.display = 'none';

			selectedDisplay.addEventListener('click', function (e) {
				e.stopPropagation();
				document.querySelectorAll('.custom-dropdown-wrap').forEach(d => {
					if (d !== customDropdown) d.classList.remove('open');
				});
				customDropdown.classList.toggle('open');
			});
		}

		document.addEventListener('click', function () {
			document.querySelectorAll('.custom-dropdown-wrap').forEach(d => d.classList.remove('open'));
		});

		if (orderSelect) createCustomDropdown(orderSelect, 'custom-orderby-dropdown');
		if (catSelect) createCustomDropdown(catSelect, 'custom-category-dropdown');

		function debounceAjaxFilter() {
			clearTimeout(filterTimeout);
			filterTimeout = setTimeout(() => performAjaxFilter(), 600);
		}

		if (minPriceInput) minPriceInput.addEventListener('input', debounceAjaxFilter);
		if (maxPriceInput) maxPriceInput.addEventListener('input', debounceAjaxFilter);

		if (instockToggle) {
			instockToggle.addEventListener('change', function () {
				if (instockPill) instockPill.classList.toggle('is-on', instockToggle.checked);
				performAjaxFilter();
			});
		}

		function performAjaxFilter(reset = false) {
			if (!gridContainer) return;
			gridContainer.style.opacity = '0.4';
			gridContainer.style.pointerEvents = 'none';

			let url = new URL(window.location.href.split('?')[0]);

			if (!reset) {
				const cat = catSelect ? catSelect.value : '';
				const minP = minPriceInput ? minPriceInput.value : '';
				const maxP = maxPriceInput ? maxPriceInput.value : '';
				const inStock = instockToggle && instockToggle.checked ? '1' : '';
				const orderby = orderSelect ? orderSelect.value : '';

				if (cat) url.searchParams.set('product_cat', cat);
				if (minP) url.searchParams.set('min_price', minP);
				if (maxP) url.searchParams.set('max_price', maxP);
				if (inStock) url.searchParams.set('instock_post', inStock);
				if (orderby) url.searchParams.set('orderby', orderby);
			} else {
				if (catSelect) {
					catSelect.value = '';
					const catWrap = document.querySelector('.custom-category-dropdown');
					if (catWrap) {
						catWrap.querySelector('.custom-dropdown-selected span').innerHTML = catSelect.options[0].innerHTML;
						catWrap.querySelectorAll('li').forEach(li => li.classList.remove('active'));
						catWrap.querySelector('li').classList.add('active');
					}
				}
				if (orderSelect) {
					orderSelect.value = 'menu_order';
					const ordWrap = document.querySelector('.custom-orderby-dropdown');
					if (ordWrap) {
						ordWrap.querySelector('.custom-dropdown-selected span').innerHTML = orderSelect.options[0].innerHTML;
						ordWrap.querySelectorAll('li').forEach(li => li.classList.remove('active'));
						ordWrap.querySelector('li').classList.add('active');
					}
				}
				if (minPriceInput) minPriceInput.value = '';
				if (maxPriceInput) maxPriceInput.value = '';
				if (instockToggle) {
					instockToggle.checked = false;
					if (instockPill) instockPill.classList.remove('is-on');
				}
			}

			fetch(url.toString())
				.then(response => response.text())
				.then(html => {
					const parser = new DOMParser();
					const doc = parser.parseFromString(html, 'text/html');

					const newGrid = doc.querySelector('ul.custom-related');
					if (newGrid && newGrid.innerHTML.trim() !== '') {
						gridContainer.innerHTML = newGrid.innerHTML;
						if (typeof window.studio94InitQuickView === 'function') {
							window.studio94InitQuickView();
						}
						initCartButtons(gridContainer);
					} else {
						gridContainer.innerHTML = '<li class="s94-no-products">No products found matching your criteria.</li>';
					}

					const currentCount = document.querySelector('.woocommerce-result-count');
					const newCount = doc.querySelector('.woocommerce-result-count');
					if (currentCount && newCount) currentCount.innerHTML = newCount.innerHTML;
					else if (currentCount) currentCount.innerHTML = '';

					const currentPagination = document.querySelector('.pagination');
					const newPagination = doc.querySelector('.pagination');
					if (currentPagination && newPagination) currentPagination.innerHTML = newPagination.innerHTML;
					else if (currentPagination) currentPagination.innerHTML = '';

					gridContainer.style.opacity = '1';
					gridContainer.style.pointerEvents = 'auto';
					window.history.pushState({ path: url.toString() }, '', url.toString());
				})
				.catch(() => { window.location.href = url.toString(); });
		}

		const clearBtn = document.querySelector('.s94-clear-btn');
		if (clearBtn) {
			clearBtn.addEventListener('click', function (e) {
				e.preventDefault();
				performAjaxFilter(true);
			});
		}

		initCartButtons(gridContainer);
	}

	/**
	 * Card add/remove-from-cart button.
	 * States, driven purely by the "in-cart" class + "is-loading" class:
	 *   default            -> icon-add (cart_add.svg)
	 *   .in-cart            -> icon-added (cart_added.svg)
	 *   .in-cart:hover       -> icon-remove (cart_remove.svg), CSS turns it red
	 * Click behaviour depends on the current state: add if not in cart,
	 * remove if already in cart.
	 */
	function ensureCartIcons(btn) {
		if (btn.querySelector('.icon-add') && btn.querySelector('.icon-added') && btn.querySelector('.icon-remove')) {
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
		const cfg = window.studio94Cart || {};

		root.querySelectorAll('.s94-cart-btn').forEach(btn => {
			ensureCartIcons(btn);
			if (btn.dataset.s94Bound === '1') return;
			btn.dataset.s94Bound = '1';

			btn.addEventListener('click', function (e) {
				e.preventDefault();
				if (btn.classList.contains('is-loading')) return;

				if (btn.classList.contains('in-cart')) {
					removeFromCart(btn);
				} else {
					addToCart(btn);
				}
			});
		});

		function addToCart(btn) {
			if (typeof jQuery === 'undefined' || !cfg.wcAjaxUrl) {
				return;
			}

			const productId = btn.dataset.product_id;
			const quantity = btn.dataset.quantity || 1;
			btn.classList.add('is-loading');

			jQuery.ajax({
				type: 'POST',
				url: cfg.wcAjaxUrl.replace('%%endpoint%%', 'add_to_cart'),
				data: {
					product_id: productId,
					quantity: quantity,
				},
				success: function (response) {
					btn.classList.remove('is-loading');
					if (!response || response.error) {
						if (response && response.product_url) {
							window.location = response.product_url;
						}
						return;
					}
					ensureCartIcons(btn);
					btn.classList.add('in-cart');
					btn.setAttribute('aria-label', 'Remove from cart');
					jQuery(document.body).trigger('added_to_cart', [response.fragments, response.cart_hash, jQuery(btn)]);
				},
				error: function () {
					btn.classList.remove('is-loading');
				}
			});
		}

		function removeFromCart(btn) {
			if (!cfg.ajaxUrl || !cfg.removeNonce) return;

			const productId = btn.dataset.product_id;
			btn.classList.add('is-loading');

			fetch(cfg.ajaxUrl, {
				method: 'POST',
				headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
				body: new URLSearchParams({
					action: 'studio94_remove_from_cart',
					nonce: cfg.removeNonce,
					product_id: productId,
				}),
			})
				.then(response => response.json())
				.then(data => {
					btn.classList.remove('is-loading');
					if (data && data.success) {
						ensureCartIcons(btn);
						btn.classList.remove('in-cart');
						btn.setAttribute('aria-label', 'Add to cart');
						if (typeof jQuery !== 'undefined') {
							jQuery(document.body).trigger('removed_from_cart', [data.data.fragments, data.data.cart_hash, jQuery(btn)]);
						}
					}
				})
				.catch(() => {
					btn.classList.remove('is-loading');
				});
		}
	}

	window.studio94InitCartButtons = initCartButtons;

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', runProductCardScripts);
	} else {
		runProductCardScripts();
	}
})();
