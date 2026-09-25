/* ==========================================================================
   MPW · Configurador — lógica de interfaz v1.5
   ========================================================================== */

(function () {
	'use strict';

	var DATA = window.MPWCFG_DATA || { ajax_url: '', nonce: '', i18n: {} };

	function clp(n) {
		try {
			return '$ ' + new Intl.NumberFormat('es-CL').format(Math.round(n));
		} catch (e) {
			return '$ ' + Math.round(n);
		}
	}

	function round10(n) {
		return Math.round(n / 10) * 10;
	}

	function buildOrderSummary(root) {
		var lines = [];
		var productName = root.dataset.productName || '';

		lines.push(
			productName
				? 'Hola! Quiero cotizar/pedir: ' + productName
				: 'Hola! Quiero hacer un pedido.'
		);

		root.querySelectorAll('.mpwcfg-step-front[data-type]').forEach(function (section) {
			var labelEl = section.querySelector('.mpwcfg-step-label');
			var valueEl = section.querySelector('.mpwcfg-step-value');

			var label = labelEl ? labelEl.textContent.trim() : '';
			var value = valueEl ? valueEl.textContent.trim() : '';

			if (label && value) {
				lines.push('• ' + label + ': ' + value);
			}
		});

		var configSummary = root.querySelector('.mpwcfg-summary__config');
		if (configSummary) {
			configSummary.innerHTML = '';
			root.querySelectorAll('.mpwcfg-step-front[data-type]').forEach(function (section) {
				var label = section.querySelector('.mpwcfg-step-label');
				var value = section.querySelector('.mpwcfg-step-value');
				if (!label || !value || !value.textContent.trim()) return;
				var row = document.createElement('div');
				row.className = 'mpwcfg-summary__config-row';
				row.dataset.summaryStep = section.dataset.step || '';
				row.innerHTML = '<div class=\"mpwcfg-summary__config-main\"><span>' + escapeHtml(label.textContent.trim()) + '</span><b>' + escapeHtml(value.textContent.trim()) + '</b></div><button type=\"button\" class=\"mpwcfg-summary__edit\" data-summary-edit=\"' + escapeHtml(section.dataset.step || '') + '\">Cambiar</button>';
				configSummary.appendChild(row);
			});
		}

		var summaryFile = root.querySelector('.mpwcfg-summary__file');
		var fileNameField = root.querySelector('.mpwcfg-file-name');
		if (summaryFile) {
			summaryFile.hidden = false;
			summaryFile.innerHTML = '<span>Diseño</span><b>' + escapeHtml(fileNameField && fileNameField.value ? fileNameField.value : 'Sin archivo') + '</b>';
		}

		var totalEl = root.querySelector('.mpwcfg-total');

		if (totalEl && totalEl.textContent) {
			lines.push('Total estimado: ' + totalEl.textContent.trim());
		}

		lines.push('(Enviado desde la web)');

		return lines.join('\n');
	}

	function updateContactLink(root) {
		var link = root.querySelector('.mpwcfg-contact');

		if (!link) return;

		var href = link.getAttribute('href');

		if (!href) return;

		try {
			var url = new URL(href, window.location.href);

			url.searchParams.set('text', buildOrderSummary(root));

			link.setAttribute('href', url.toString());
		} catch (e) {}
	}

	function initDropdowns(root) {
		var openPanel = null;
		var openBtn = null;

		function closeDD() {
			if (openPanel) {
				openPanel.classList.remove('is-open');
				openPanel = null;
			}

			if (openBtn) {
				openBtn.classList.remove('is-open');
				openBtn.setAttribute('aria-expanded', 'false');
				openBtn = null;
			}
		}

		root.querySelectorAll('.mpwcfg-dd').forEach(function (dd) {
			var btn = dd.querySelector('.mpwcfg-dd__btn');
			var panel = dd.querySelector('.mpwcfg-dd__panel');

			if (!btn || !panel) return;

			btn.addEventListener('click', function (e) {
				e.stopPropagation();

				if (panel === openPanel) {
					closeDD();
					return;
				}

				closeDD();

				panel.classList.add('is-open');
				btn.classList.add('is-open');
				btn.setAttribute('aria-expanded', 'true');

				openPanel = panel;
				openBtn = btn;
			});

			panel.addEventListener('click', function (e) {
				e.stopPropagation();
			});
		});

		root.addEventListener('click', closeDD);

		root.querySelectorAll('.mpwcfg-dd__opt').forEach(function (opt) {
			opt.addEventListener('click', function () {
				var dd = opt.closest('.mpwcfg-dd');
				var section = opt.closest('.mpwcfg-step-front');

				if (!dd || !section) return;

				dd.querySelectorAll('.mpwcfg-dd__opt').forEach(function (o) {
					o.classList.remove('is-sel');
				});

				opt.classList.add('is-sel');

				section.dataset.price = opt.dataset.price || 0;
				section.dataset.qty = opt.dataset.qty || 0;
				section.dataset.mult = opt.dataset.mult || 1;
				section.dataset.badge = opt.dataset.badge || '';
				section.dataset.label = opt.dataset.label || '';
				section.dataset.index = opt.dataset.index || 0;

				var valEl = section.querySelector('.mpwcfg-step-value');

				if (valEl) {
					valEl.textContent = opt.dataset.label;
				}

				updateDropdownFace(dd, opt);
				closeDD();

				if (
					mpwcfgIsSpecialQtyProduct(root) &&
					section.dataset.step === 'formato'
				) {
					mpwcfgRefreshQuantitySlider(root);
				} else {
					recompute(root);
				}
			});
		});
	}

	function updateDropdownFace(dd, opt) {
		var face = dd.querySelector('.mpwcfg-dd__face');

		if (!face) return;

		var label = opt.dataset.label || '';
		var sub = opt.dataset.sub || '';

		face.innerHTML =
			'<span class="mpwcfg-opt-main"><b>' +
			label +
			'</b>' +
			(sub ? '<span>' + sub + '</span>' : '') +
			'</span>';
	}

	function refreshServerPrice(root) {
		if (!root || !DATA.ajax_url || !DATA.nonce) return;

		var fd = new FormData();
		fd.append('action', 'mpwcfg_price');
		fd.append('nonce', DATA.nonce);
		fd.append('product_id', root.dataset.product || '0');

		root.querySelectorAll('.mpwcfg-step-front[data-type]').forEach(function (section) {
			var key = section.dataset.step;
			if (!key) return;
			if (section.dataset.isCustom === 'yes') {
				fd.append('selections[' + key + ']', 'custom');
				fd.append('selections[' + key + '_custom]', section.dataset.qty || '');
			} else {
				fd.append('selections[' + key + ']', section.dataset.index || '0');
			}
		});

		fetch(DATA.ajax_url, { method: 'POST', body: fd })
			.then(function (res) { return res.json(); })
			.then(function (json) {
				if (!json || !json.success || !json.data) return;
				var totalEl = root.querySelector('.mpwcfg-total');
				if (totalEl && json.data.total_formatted) totalEl.textContent = json.data.total_formatted;
				var unitEl = root.querySelector('.mpwcfg-unit');
				if (unitEl && json.data.unit_formatted) unitEl.textContent = json.data.unit_formatted;
				var taxQty = root.querySelector('.mpwcfg-summary__tax-qty');
				if (taxQty && json.data.qty_units) taxQty.textContent = json.data.qty_units;
			});
	}

	function initSlider(section) {
		var slider = section.querySelector('.mpwcfg-slider__range');
		var labelsEl = section.querySelector('.mpwcfg-slider__labels');
		var thumbEl = section.querySelector('.mpwcfg-slider__thumb');
		var customBox = section.querySelector('.mpwcfg-custom-qty');
		var customInput = section.querySelector('.mpwcfg-custom-qty__input');
		var customVal = section.querySelector('.mpwcfg-custom-qty-val');

		if (!slider) return;

		var opts = [];

		try {
			opts = JSON.parse(
				slider.getAttribute('data-slider-opts') || '[]'
			);
		} catch (e) {}

		if (!opts.length) return;

		var lastTramo = null;

		function rebuildLastTramo() {
			lastTramo = null;

			opts.forEach(function (o) {
				if (!o.custom) lastTramo = o;
			});
		}

		function renderLabels() {
			if (!labelsEl) return;

			labelsEl.innerHTML = '';

			opts.forEach(function (o) {
				var sp = document.createElement('span');

				sp.textContent = o.label;

				if (o.custom) {
					sp.style.color = 'var(--mpwcfg-mint)';
				}

				labelsEl.appendChild(sp);
			});
		}

		function applyIndex(idx) {
			var opt = opts[idx];

			if (!opt) return;

			var isCustom = !!opt.custom;
			var totalOpts = opts.length;

			var pct =
				totalOpts > 1
					? (idx / (totalOpts - 1)) * 100
					: 0;

			slider.value = idx;
			slider.style.setProperty('--mpwcfg-track-pct', pct + '%');

			if (thumbEl) {
				thumbEl.style.left = pct + '%';

				if (isCustom) {
					thumbEl.innerHTML = '<b>Cantidad personalizada</b>';
				} else {
					thumbEl.innerHTML =
						'<b>' + (opt.qty || opt.label) + ' uds</b>';
				}
			}

			if (customBox) {
				customBox.hidden = !isCustom;
			}

			if (isCustom) {
				section.dataset.isCustom = 'yes';
				section.dataset.mult = lastTramo ? lastTramo.mult : 1;

				var qtyVal =
					customInput && customInput.value
						? parseInt(customInput.value, 10)
						: 0;

				section.dataset.qty =
					qtyVal || (lastTramo ? lastTramo.qty : 1);

				section.dataset.label =
					qtyVal
						? qtyVal + ' uds (personalizado)'
						: 'Personalizado';

				section.dataset.index = 'custom';

				if (customVal) {
					customVal.value = qtyVal || '';
				}

			} else {
				section.dataset.isCustom = 'no';
				section.dataset.price = 0;
				section.dataset.qty = opt.qty || 1;
				section.dataset.mult = opt.mult || 1;
				section.dataset.badge = opt.badge || '';
				section.dataset.label = (opt.qty || opt.label) + ' uds';
				section.dataset.index = idx;

				if (customVal) {
					customVal.value = '';
				}
			}

			var valEl = section.querySelector('.mpwcfg-step-value');

			if (valEl) {
				valEl.textContent = section.dataset.label;
			}
		}

		rebuildLastTramo();
		renderLabels();

		section._mpwcfgSetSliderOptions = function (newOpts) {
			if (!Array.isArray(newOpts) || !newOpts.length) {
				return;
			}

			opts = newOpts;

			slider.setAttribute(
				'data-slider-opts',
				JSON.stringify(opts)
			);

			slider.min = 0;
			slider.max = opts.length - 1;
			slider.step = 1;
			slider.value = 0;

			rebuildLastTramo();
			renderLabels();
			applyIndex(0);

			recompute(slider.closest('.mpwcfg-configurator'));
		};

		if (customInput) {
			customInput.addEventListener('input', function () {
				var qty = Math.max(
					1,
					parseInt(customInput.value, 10) || 0
				);

				section.dataset.qty = qty;
				section.dataset.label = qty + ' uds (personalizado)';

				if (customVal) {
					customVal.value = qty;
				}

				var valEl = section.querySelector('.mpwcfg-step-value');

				if (valEl) {
					valEl.textContent = section.dataset.label;
				}

				recompute(slider.closest('.mpwcfg-configurator'));
			});
		}

		slider.addEventListener('input', function () {
			var root = slider.closest('.mpwcfg-configurator');
			applyIndex(parseInt(slider.value, 10));
			recompute(root);
			refreshServerPrice(root);
		});

		applyIndex(parseInt(slider.value, 10));
	}

	function initSwatches(root) {
		root.querySelectorAll('.mpwcfg-step-front[data-type] .mpwcfg-swatch')
			.forEach(function (btn) {

				btn.addEventListener('click', function () {
					var section = btn.closest('.mpwcfg-step-front');

					section.querySelectorAll('.mpwcfg-swatch')
						.forEach(function (b) {
							b.classList.remove('is-selected');
							b.setAttribute('aria-pressed', 'false');
						});

					btn.classList.add('is-selected');
					btn.setAttribute('aria-pressed', 'true');

					section.dataset.price = btn.dataset.price || 0;
					section.dataset.qty = btn.dataset.qty || 0;
					section.dataset.mult = btn.dataset.mult || 1;
					section.dataset.badge = btn.dataset.badge || '';
					section.dataset.label = btn.dataset.label || '';
					section.dataset.index = btn.dataset.index || 0;

					var valEl = section.querySelector('.mpwcfg-step-value');

					if (valEl) {
						valEl.textContent =
							section.dataset.type === 'quantity'
								? btn.dataset.label + ' uds'
								: btn.dataset.label;
					}

					if (
						mpwcfgIsSpecialQtyProduct(root) &&
						section.dataset.step === 'formato'
					) {
						mpwcfgRefreshQuantitySlider(root);
					} else {
						recompute(root);
					}
				});
			});
	}

	function escapeHtml(value) {
		return String(value || '').replace(/[&<>"']/g, function (ch) {
			return ({ '&':'&amp;', '<':'&lt;', '>':'&gt;', '"':'&quot;', "'":'&#039;' })[ch];
		});
	}

	function recompute(root) {
		if (!root) return;

		var base = parseFloat(root.dataset.base) || 0;
		var surcharge = 0;
		var mult = 1;
		var qty = 1;
		var badge = '';
		var isCustom = false;
		var customQty = 0;

		root.querySelectorAll('.mpwcfg-step-front[data-type]')
			.forEach(function (section) {

				if (section.dataset.type === 'quantity') {
					isCustom =
						section.dataset.isCustom === 'yes';

					mult =
						parseFloat(section.dataset.mult) || 1;

					qty =
						parseInt(section.dataset.qty, 10) || 1;

					badge =
						section.dataset.badge || '';

					customQty =
						isCustom ? qty : 0;

				} else {
					surcharge +=
						parseFloat(section.dataset.price) || 0;
				}
			});

		var total;
		var unit;

		if (isCustom && customQty > 0) {
			var unitAtBest =
				round10(
					(base + surcharge) * mult / qty
				);

			total =
				round10(
					unitAtBest * customQty
				);

			unit = unitAtBest;

		} else {
			total =
				round10(
					(base + surcharge) * mult
				);

			unit =
				qty > 0
					? round10(total / qty)
					: total;
		}

		var totalEl = root.querySelector('.mpwcfg-total');

		if (totalEl) {
			totalEl.textContent = clp(total);
		}

		var unitEl = root.querySelector('.mpwcfg-unit');

		if (unitEl) {
			unitEl.textContent = clp(unit);
		}

		updateContactLink(root);
	}

	/**
	 * Extensiones aceptadas para el diseño del cliente (deben calzar con
	 * MPWCFG_Ajax::upload() en el servidor, que es quien valida de verdad).
	 */
	var UPLOAD_ALLOWED_EXT = ['pdf', 'png', 'jpg', 'jpeg', 'ai', 'eps'];
	var UPLOAD_MAX_BYTES = 25 * 1024 * 1024; // 25 MB, igual que en el servidor.

	function uploadFileExt(name) {
		var parts = String(name || '').split('.');
		return parts.length > 1 ? parts.pop().toLowerCase() : '';
	}

	function initUpload(section) {
		var dropzone = section.querySelector('.mpwcfg-dropzone');
		var input = section.querySelector('.mpwcfg-upload-input');
		var nameEl = section.querySelector('.mpwcfg-upload-name');
		var urlField = section.querySelector('.mpwcfg-file-url');
		var nameField = section.querySelector('.mpwcfg-file-name');
		var fileBox = section.querySelector('.mpwcfg-upload-file');
		var fileBoxName = section.querySelector('.mpwcfg-upload-file__name');
		var fileRemove = section.querySelector('.mpwcfg-upload-file__remove');

		if (!dropzone || !input) return;

		function setState(state) {
			dropzone.classList.remove('is-loading', 'is-done', 'is-dragover');
			if (state) dropzone.classList.add(state);
		}

		function showName(text) {
			if (nameEl) nameEl.textContent = text || '';
		}

		function reset(message) {
			setState(null);
			if (urlField) urlField.value = '';
			if (nameField) nameField.value = '';
			if (input) input.value = '';
			if (fileBox) fileBox.hidden = true;
			if (fileBoxName) fileBoxName.textContent = '';
			showName(message || '');
		}

		function upload(file) {
			if (!file) return;

			var ext = uploadFileExt(file.name);

			if (UPLOAD_ALLOWED_EXT.indexOf(ext) === -1) {
				reset(DATA.i18n.file_error || 'Archivo no válido.');
				return;
			}

			if (file.size > UPLOAD_MAX_BYTES) {
				reset(DATA.i18n.file_error || 'Archivo no válido.');
				return;
			}

			setState('is-loading');
			showName(DATA.i18n.uploading || 'Subiendo…');

			var fd = new FormData();
			fd.append('action', 'mpwcfg_upload');
			fd.append('nonce', DATA.nonce);
			fd.append('file', file);

			fetch(DATA.ajax_url, { method: 'POST', body: fd })
				.then(function (res) { return res.json(); })
				.then(function (json) {
					if (!json || !json.success) {
						reset((json && json.data && json.data.message) || DATA.i18n.file_error || 'Archivo no válido.');
						return;
					}

					if (urlField) urlField.value = json.data.url || '';
					if (nameField) nameField.value = json.data.name || file.name;
					if (fileBoxName) fileBoxName.textContent = json.data.name || file.name;
					if (fileBox) fileBox.hidden = false;

					setState('is-done');
					showName('Diseño cargado');
				})
				.catch(function () {
					reset(DATA.i18n.file_error || 'Archivo no válido.');
				});
		}

		dropzone.addEventListener('click', function () {
			input.click();
		});

		dropzone.addEventListener('keydown', function (e) {
			if (e.key === 'Enter' || e.key === ' ') {
				e.preventDefault();
				input.click();
			}
		});

		input.addEventListener('change', function () {
			if (input.files && input.files[0]) {
				upload(input.files[0]);
			}
		});

		['dragenter', 'dragover'].forEach(function (evt) {
			dropzone.addEventListener(evt, function (e) {
				e.preventDefault();
				e.stopPropagation();
				dropzone.classList.add('is-dragover');
			});
		});

		['dragleave', 'dragend'].forEach(function (evt) {
			dropzone.addEventListener(evt, function (e) {
				e.preventDefault();
				e.stopPropagation();
				dropzone.classList.remove('is-dragover');
			});
		});


		if (fileRemove) {
			fileRemove.addEventListener('click', function (e) {
				e.preventDefault();
				e.stopPropagation();
				reset('');
				var root = section.closest('.mpwcfg-configurator');
				if (root) recompute(root);
			});
		}
		dropzone.addEventListener('drop', function (e) {
			e.preventDefault();
			e.stopPropagation();
			dropzone.classList.remove('is-dragover');

			var file = e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files[0];

			if (file) upload(file);
		});
	}

	function initCart(root) {
		var addBtn = root.querySelector('.mpwcfg-add');
		var addText = addBtn ? addBtn.querySelector('.mpwcfg-add__text') : null;
		var feedbackEl = root.querySelector('.mpwcfg-feedback');

		if (!addBtn) return;

		var idleLabel = addText ? addText.textContent : '';
		var busy = false;
		var completedDestination = null;
		var cartLink = root.querySelector('.mpwcfg-cart-link');

		function setFeedback(text, isError) {
			if (!feedbackEl) return;
			feedbackEl.textContent = text || '';
			feedbackEl.classList.toggle('is-error', !!isError);
		}

		function setBusy(isBusy) {
			busy = isBusy;
		}

		addBtn.addEventListener('click', function () {
			if (completedDestination) return;
			if (busy) return;

			var selections = {};
			var fileUrl = '';
			var fileName = '';

			root.querySelectorAll('.mpwcfg-step-front[data-type]')
				.forEach(function (section) {

					var key = section.dataset.step;

					if (section.dataset.isCustom === 'yes') {
						selections[key] = 'custom';
						selections[key + '_custom'] =
							section.dataset.qty || '';
					} else {
						selections[key] =
							section.dataset.index || '0';
					}
				});

			var urlField = root.querySelector('.mpwcfg-file-url');
			var nameField = root.querySelector('.mpwcfg-file-name');

			if (urlField && urlField.value) fileUrl = urlField.value;
			if (nameField && nameField.value) fileName = nameField.value;

			var fd = new FormData();

			fd.append('action', 'mpwcfg_add_to_cart');
			fd.append('nonce', DATA.nonce);
			fd.append('product_id', root.dataset.product);
			var variationIdField = root.querySelector('input[name=variation_id]');
			if (variationIdField && variationIdField.value) {
				fd.append('variation_id', variationIdField.value);
				root.querySelectorAll('select[name^=\"attribute_\"], input[name^=\"attribute_\"]:checked').forEach(function (field) {
					if (field.value) fd.append('variation[' + field.name + ']', field.value);
				});
			}

			if (fileUrl) fd.append('file_url', fileUrl);
			if (fileName) fd.append('file_name', fileName);

			Object.keys(selections).forEach(function (k) {
				fd.append(
					'selections[' + k + ']',
					selections[k]
				);
			});

			setBusy(true);
			setFeedback('');

			fetch(DATA.ajax_url, {
				method: 'POST',
				body: fd
			})
				.then(function (res) { return res.json(); })
				.then(function (json) {
					if (!json || !json.success) {
						var msg = (json && json.data && json.data.message) || DATA.i18n.error || 'No se pudo agregar. Inténtalo de nuevo.';
						setBusy(false);
						setFeedback(msg, true);
						return;
					}


					var destination = json.data.cart_url || DATA.cart_url || '';
					completedDestination = destination || null;
										if (cartLink && destination) {
						cartLink.href = destination;
						cartLink.hidden = false;
					}
					setBusy(false);
					setFeedback('', false);
				})
				.catch(function () {
					setBusy(false);
					setFeedback(DATA.i18n.error || 'No se pudo agregar. Inténtalo de nuevo.', true);
				});
		});
	}

	function initSummaryEditing(root) {
		var summary = root.querySelector('.mpwcfg-summary__config');
		if (!summary) return;
		summary.addEventListener('click', function (e) {
			var btn = e.target.closest('.mpwcfg-summary__edit');
			if (!btn) return;
			var step = root.querySelector('.mpwcfg-step-front[data-step=\"' + CSS.escape(btn.getAttribute('data-summary-edit') || '') + '\"]');
			if (step) step.scrollIntoView({ behavior: 'smooth', block: 'center' });
		});
	}

	function initConfigurator(root) {
		initDropdowns(root);
		initSwatches(root);

		root.querySelectorAll(
			'.mpwcfg-step-front[data-display="slider"]'
		).forEach(function (s) {
			initSlider(s);
		});

		root.querySelectorAll(
			'.mpwcfg-step-front--upload'
		).forEach(function (s) {
			initUpload(s);
		});

		initCart(root);
		initSummaryEditing(root);

		recompute(root);
	}

	function boot() {
		document.querySelectorAll(
			'.mpwcfg-configurator[data-product]'
		).forEach(initConfigurator);
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', boot);
	} else {
		boot();
	}

})();