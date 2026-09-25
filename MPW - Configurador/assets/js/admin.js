/* MPW · Configurador — builder del admin (producto) */
(function ($) {
	'use strict';

	function tpl(id) {
		return $('#' + id).html();
	}

	// Reescribe todos los names con los índices correctos de paso/opción.
	function reindex() {
		$('#mpwcfg-steps .mpwcfg-step').each(function (i) {
			var $step = $(this);

			$step.find('[name]').each(function () {
				var name = $(this).attr('name');
				name = name.replace(/mpwcfg\[steps\]\[[^\]]*\]/, 'mpwcfg[steps][' + i + ']');
				$(this).attr('name', name);
			});

			$step.find('.mpwcfg-option').each(function (j) {
				var $opt = $(this);
				$opt.find('[name]').each(function () {
					var name = $(this).attr('name');
					name = name.replace(/\[options\]\[[^\]]*\]/, '[options][' + j + ']');
					$(this).attr('name', name);
				});
				var $radio = $opt.find('.mpwcfg-option__default input[type=radio]');
				$radio.attr('name', 'mpwcfg[steps][' + i + '][_default]');
				$radio.attr('value', j);
			});
		});
	}

	function makeSortable($list, handle, items) {
		if (!$.fn.sortable) return;
		$list.sortable({
			handle: handle,
			items: items,
			axis: 'y',
			tolerance: 'pointer',
			update: reindex
		});
	}

	function initSortables() {
		makeSortable($('#mpwcfg-steps'), '.mpwcfg-step__drag', '.mpwcfg-step');
		$('.mpwcfg-options').each(function () {
			makeSortable($(this), '.mpwcfg-option__drag', '.mpwcfg-option');
		});
	}

	// Sincroniza el input[type=color] con el campo de texto #hex (y viceversa).
	function syncChipPickers($scope) {
		($scope && $scope.length ? $scope : $(document)).find('.mpwcfg-option__chip-picker').each(function () {
			var $picker = $(this);
			var $text = $picker.siblings('.mpwcfg-option__chip');
			if ($text.length && $text.val()) {
				$picker.val(normalizeHex($text.val()) || '#ffffff');
			}
		});
	}

	function normalizeHex(v) {
		v = (v || '').trim();
		if (/^#([0-9a-f]{6})$/i.test(v)) return v;
		if (/^#([0-9a-f]{3})$/i.test(v)) {
			return '#' + v.slice(1).split('').map(function (c) { return c + c; }).join('');
		}
		return '';
	}

	$(function () {
		initSortables();
		syncChipPickers();

		// Añadir paso.
		$(document).on('click', '.mpwcfg-add-step', function () {
			var type = $(this).data('type');
			var html = tpl('mpwcfg-tpl-step-' + type);
			var $step = $(html);
			$('#mpwcfg-steps').append($step);
			makeSortable($step.find('.mpwcfg-options'), '.mpwcfg-option__drag', '.mpwcfg-option');
			reindex();
			$step.find('.mpwcfg-step__label').trigger('focus');
		});

		// Duplicar paso (con todas sus opciones y valores ya cargados).
		$(document).on('click', '.mpwcfg-step__duplicate', function () {
			var $orig  = $(this).closest('.mpwcfg-step');
			var $clone = $orig.clone();

			// Vacía la key guardada: al guardar se genera una nueva a partir del rótulo,
			// para que el paso original y su copia no compitan por la misma key.
			$clone.find('.mpwcfg-step__key').val('');

			var $label = $clone.find('.mpwcfg-step__label');
			var current = $label.val();
			$label.val(current ? current + ' (copia)' : '');

			$orig.after($clone);
			makeSortable($clone.find('.mpwcfg-options'), '.mpwcfg-option__drag', '.mpwcfg-option');
			syncChipPickers($clone);
			reindex();
			$label.trigger('focus').trigger('select');
		});

		// Eliminar paso.
		$(document).on('click', '.mpwcfg-step__remove', function () {
			$(this).closest('.mpwcfg-step').remove();
			reindex();
		});

		// Añadir opción.
		$(document).on('click', '.mpwcfg-add-option', function () {
			var $step = $(this).closest('.mpwcfg-step');
			var type = $step.data('type') === 'quantity' ? 'quantity' : 'swatch';
			var $opt = $(tpl('mpwcfg-tpl-option-' + type));
			$step.find('.mpwcfg-options').append($opt);
			reindex();
			$opt.find('.mpwcfg-option__label').trigger('focus');
		});

		// Eliminar opción.
		$(document).on('click', '.mpwcfg-option__remove', function () {
			$(this).closest('.mpwcfg-option').remove();
			reindex();
		});

		// Selector visual de color: el picker manda sobre el campo de texto.
		$(document).on('input change', '.mpwcfg-option__chip-picker', function () {
			$(this).siblings('.mpwcfg-option__chip').val($(this).val());
		});

		// Si se escribe/pega un hex válido a mano, refleja el color en el picker.
		$(document).on('input change', '.mpwcfg-option__chip', function () {
			var hex = normalizeHex($(this).val());
			if (hex) $(this).siblings('.mpwcfg-option__chip-picker').val(hex);
		});

		// Imagen miniatura opcional (media uploader de WordPress).
		$(document).on('click', '.mpwcfg-option__thumb-btn', function (e) {
			e.preventDefault();
			if (!window.wp || !wp.media) return;

			var $btn   = $(this);
			var $wrap  = $btn.closest('.mpwcfg-option__thumbwrap');
			var $input = $wrap.find('.mpwcfg-option__image');

			var frame = wp.media({
				title: mpwcfg_admin_i18n.chooseImage,
				multiple: false,
				library: { type: 'image' }
			});

			frame.on('select', function () {
				var att = frame.state().get('selection').first().toJSON();
				var thumb = (att.sizes && att.sizes.thumbnail) ? att.sizes.thumbnail.url : att.url;
				$input.val(att.id);
				$btn.css('background-image', 'url(' + thumb + ')').addClass('has-image');
				$btn.find('.dashicons').hide();
				$wrap.find('.mpwcfg-option__thumb-remove').prop('hidden', false);
			});

			frame.open();
		});

		// Quitar la imagen miniatura.
		$(document).on('click', '.mpwcfg-option__thumb-remove', function (e) {
			e.preventDefault();
			var $wrap = $(this).closest('.mpwcfg-option__thumbwrap');
			$wrap.find('.mpwcfg-option__image').val('');
			$wrap.find('.mpwcfg-option__thumb-btn').css('background-image', '').removeClass('has-image').find('.dashicons').show();
			$(this).prop('hidden', true);
		});
	});
})(jQuery);

/* ==========================================================================
   v1.9.0 · Modo exclusivo por combinaciones
   ========================================================================== */
(function ($) {
	'use strict';

	function comboReindex() {
		$('#mpwcfg-combo-levels .mpwcfg-combo-level').each(function (i) {
			var $level = $(this);
			$level.attr('data-level-index', i);
			$level.find('.mpwcfg-combo-number').text(String(i + 1).padStart(2, '0'));
			$level.find('[name]').each(function () {
				var n = $(this).attr('name');
				n = n.replace(/\[levels\]\[[^\]]*\]/, '[levels][' + i + ']');
				n = n.replace(/\[options\]\[[^\]]*\]/, function (m) { return m; });
				$(this).attr('name', n);
			});
			$level.find('.mpwcfg-combo-option').each(function (j) {
				$(this).find('[name]').each(function () {
					var n = $(this).attr('name').replace(/\[options\]\[[^\]]*\]/, '[options][' + j + ']');
					$(this).attr('name', n);
				});
			});
		});
	}

	function comboReadMatrix() {
		var prices = {};
		$('#mpwcfg-combo-matrix tbody tr').each(function () {
			var vals = [];
			$(this).find('.mpwcfg-combo-matrix-select').each(function () { vals.push($(this).val()); });
			var price = $(this).find('input[name*="[price]"]').val() || '0';
			prices[vals.join('-')] = price;
		});
		return prices;
	}

	function comboOptionCounts() {
		var counts = [];
		$('#mpwcfg-combo-levels .mpwcfg-combo-level').each(function () {
			counts.push($(this).find('.mpwcfg-combo-option').length);
		});
		return counts;
	}

	function comboProducts(counts) {
		var rows = [];
		function walk(level, current) {
			if (level >= counts.length) { rows.push(current.slice()); return; }
			for (var i = 0; i < counts[level]; i++) {
				current.push(i); walk(level + 1, current); current.pop();
			}
		}
		if (counts.length && counts.every(function (n) { return n > 0; })) walk(0, []);
		return rows;
	}

	function comboGenerateMatrix() {
		var $table = $('#mpwcfg-combo-matrix');
		var $levels = $('#mpwcfg-combo-levels .mpwcfg-combo-level');
		var old = comboReadMatrix();
		var counts = comboOptionCounts();
		var rows = comboProducts(counts);
		var head = '<tr>';
		$levels.each(function () { head += '<th>' + $('<div>').text($(this).find('.mpwcfg-combo-level__label').val() || 'Nivel').html() + '</th>'; });
		head += '<th>Precio (CLP)</th><th>Acciones</th></tr>';
		$table.find('thead').html(head);
		var body = '';
		rows.forEach(function (vals, r) {
			var key = vals.join('-');
			body += '<tr>';
			$levels.each(function (li) {
				var options = [];
				$(this).find('.mpwcfg-combo-option').each(function (oi) { options.push($(this).find('input').first().val() || ('Opción ' + (oi + 1))); });
				body += '<td><input type="hidden" name="mpwcfg[combinations][matrix][' + r + '][values][' + li + ']" value="' + vals[li] + '"><select class="mpwcfg-combo-matrix-select" data-level="' + li + '">';
				options.forEach(function (label, oi) { body += '<option value="' + oi + '"' + (oi === vals[li] ? ' selected' : '') + '>' + $('<div>').text(label).html() + '</option>'; });
				body += '</select></td>';
			});
			body += '<td><input type="number" min="0" step="1" name="mpwcfg[combinations][matrix][' + r + '][price]" value="' + (old[key] !== undefined ? old[key] : 0) + '"></td>';
			body += '<td><button type="button" class="button-link-delete mpwcfg-combo-remove-row">×</button></td></tr>';
		});
		$table.find('tbody').html(body);
	}

	function comboShowMode() {
		var mode = $('input[name="mpwcfg[mode]"]:checked').val() || 'traditional';
		$('[data-mode-panel]').hide().filter('[data-mode-panel="' + mode + '"]').show();
		$('.mpwcfg-mode-switch label').removeClass('is-active').filter(function () {
			return $(this).find('input').val() === mode;
		}).addClass('is-active');
	}

	$(function () {
		comboShowMode();
		$(document).on('change', 'input[name="mpwcfg[mode]"]', comboShowMode);

		$(document).on('click', '.mpwcfg-combo-add-level', function () {
			var i = $('#mpwcfg-combo-levels .mpwcfg-combo-level').length;
			var html = '<div class="mpwcfg-combo-level" data-level-index="' + i + '">' +
				'<div class="mpwcfg-combo-level__head"><span class="mpwcfg-combo-drag dashicons dashicons-menu"></span><span class="mpwcfg-combo-number">' + String(i + 1).padStart(2, '0') + '</span>' +
				'<input type="text" class="mpwcfg-combo-level__label" name="mpwcfg[combinations][levels][' + i + '][label]" placeholder="Nombre del nivel">' +
				'<button type="button" class="button mpwcfg-combo-remove-level">×</button></div>' +
				'<div class="mpwcfg-combo-options"></div><button type="button" class="button button-secondary mpwcfg-combo-add-option">+ Agregar opción</button></div>';
			$('#mpwcfg-combo-levels').append(html);
			$(this).closest('.mpwcfg-combinations').find('.mpwcfg-combo-generate').trigger('click');
			comboReindex();
		});

		$(document).on('click', '.mpwcfg-combo-add-option', function () {
			var $level = $(this).closest('.mpwcfg-combo-level');
			var li = $('#mpwcfg-combo-levels .mpwcfg-combo-level').index($level);
			var oi = $level.find('.mpwcfg-combo-option').length;
			$level.find('.mpwcfg-combo-options').append('<div class="mpwcfg-combo-option"><span class="dashicons dashicons-menu"></span><input type="text" name="mpwcfg[combinations][levels][' + li + '][options][' + oi + '][label]" placeholder="Opción"><input type="text" name="mpwcfg[combinations][levels][' + li + '][options][' + oi + '][sku]" placeholder="SKU (opcional)"><button type="button" class="button-link-delete mpwcfg-combo-remove-option">×</button></div>');
			if (li === 0 && $('#mpwcfg-combo-profiles .mpwcfg-combo-profile').length) {
				var fi = $('#mpwcfg-combo-profiles .mpwcfg-combo-profile').length;
				var label = 'Nuevo formato';
				var profile = '<div class="mpwcfg-combo-profile" data-format-index="' + fi + '"><div class="mpwcfg-combo-profile__head"><span class="mpwcfg-combo-letter">' + String.fromCharCode(65 + fi) + '</span><strong>' + label + '</strong><span class="mpwcfg-combo-profile__chevron">⌃</span></div><input type="hidden" name="mpwcfg[combinations][profiles][' + fi + '][format_index]" value="' + fi + '"><div class="mpwcfg-combo-profile__body"><div class="mpwcfg-combo-profile__label">Tramos de hojas para este formato</div><div class="mpwcfg-combo-tier-chips"><span class="mpwcfg-combo-tier-chip"><input type="text" name="mpwcfg[combinations][profiles][' + fi + '][tiers][0][label]" value="50 hojas"><button type="button" class="mpwcfg-combo-remove-tier">×</button></span><button type="button" class="button button-secondary mpwcfg-combo-add-tier">+ agregar tramo</button></div><div class="mpwcfg-combo-profile__label">Cantidades y precio</div><div class="mpwcfg-combo-profile__tables"><div class="mpwcfg-combo-tier-table" data-tier-index="0"><div class="mpwcfg-combo-tier-table__title">50 hojas</div><div class="mpwcfg-combo-profile-grid"><div class="mpwcfg-combo-grid-head"><span>Tramo de hojas</span><span>Cantidad</span><span>Precio (CLP)</span><span></span></div><div class="mpwcfg-combo-grid-row"><input type="text" value="50 hojas" readonly><input type="text" name="mpwcfg[combinations][profiles][' + fi + '][tiers][0][rows][0][quantity]" value=""><input type="number" name="mpwcfg[combinations][profiles][' + fi + '][tiers][0][rows][0][price]" value="0"><button type="button" class="mpwcfg-combo-remove-row">×</button></div></div><button type="button" class="button button-secondary mpwcfg-combo-add-row">+ agregar cantidad</button></div></div></div></div>';
				$('#mpwcfg-combo-profiles').append(profile);
			}
			comboReindex();
		});

		$(document).on('click', '.mpwcfg-combo-remove-option', function () {
			var $level = $(this).closest('.mpwcfg-combo-level');
			var li = $('#mpwcfg-combo-levels .mpwcfg-combo-level').index($level);
			var oi = $level.find('.mpwcfg-combo-option').index($(this).closest('.mpwcfg-combo-option'));
			$(this).closest('.mpwcfg-combo-option').remove();
			if (li === 0) {
				$('#mpwcfg-combo-profiles .mpwcfg-combo-profile').eq(oi).remove();
				$('#mpwcfg-combo-profiles .mpwcfg-combo-profile').each(function(i){ $(this).find('.mpwcfg-combo-letter').text(String.fromCharCode(65+i)); window.mpwcfgProfileReindex && window.mpwcfgProfileReindex($(this)); });
			}
			comboReindex();
		});

		$(document).on('click', '.mpwcfg-combo-remove-level', function () {
			$(this).closest('.mpwcfg-combo-level').remove();
			comboReindex();
		});

		$(document).on('click', '.mpwcfg-combo-generate', comboGenerateMatrix);

		$(document).on('change', '.mpwcfg-combo-matrix-select', function () {
			var $td = $(this).closest('td');
			$td.find('input[type="hidden"]').val($(this).val());
		});

		if ($.fn.sortable) {
			$('#mpwcfg-combo-levels').sortable({ handle: '.mpwcfg-combo-drag', items: '.mpwcfg-combo-level', update: function () { comboReindex(); } });
		}
	});
})(jQuery);

/* ============================================================
   Combinaciones por formato.
   Cada formato es independiente: sus tramos, cantidades y precios no
   se comparten con otros formatos.
   ============================================================ */
(function ($) {
    'use strict';

    function profileReindex($profile) {
        var fi = $('#mpwcfg-combo-profiles .mpwcfg-combo-profile').index($profile);
        $profile.attr('data-format-index', fi);
        $profile.find('input[name*="[format_index]"]').val(fi);

        $profile.find('.mpwcfg-combo-tier-chip').each(function (ti) {
            $(this).find('[name]').each(function () {
                var n = $(this).attr('name');
                n = n.replace(/\[profiles\]\[[^\]]*\]/, '[profiles][' + fi + ']');
                n = n.replace(/\[tiers\]\[[^\]]*\]/, '[tiers][' + ti + ']');
                $(this).attr('name', n);
            });
        });

        $profile.find('.mpwcfg-combo-tier-table').each(function (ti) {
            var $table = $(this).attr('data-tier-index', ti);
            $table.find('[name]').each(function () {
                var n = $(this).attr('name');
                n = n.replace(/\[profiles\]\[[^\]]*\]/, '[profiles][' + fi + ']');
                n = n.replace(/\[tiers\]\[[^\]]*\]/, '[tiers][' + ti + ']');
                $(this).attr('name', n);
            });
            $table.find('.mpwcfg-combo-grid-row').each(function (ri) {
                $(this).find('[name]').each(function () {
                    var n = $(this).attr('name').replace(/\[rows\]\[[^\]]*\]/, '[rows][' + ri + ']');
                    $(this).attr('name', n);
                });
            });
        });
    }

    function allProfilesReindex() {
        $('#mpwcfg-combo-profiles .mpwcfg-combo-profile').each(function (i) {
            var $p = $(this);
            $p.find('.mpwcfg-combo-letter').text(String.fromCharCode(65 + i));
            profileReindex($p);
        });
    }

    function levelLabel(index, fallback) {
        var value = $('#mpwcfg-combo-levels .mpwcfg-combo-level').eq(index).find('.mpwcfg-combo-level__label').val();
        return value || fallback;
    }

    function formatLabels() {
        var labels = [];
        $('#mpwcfg-combo-levels .mpwcfg-combo-level').eq(0).find('.mpwcfg-combo-option').each(function () {
            labels.push($(this).find('input[type="text"]').first().val() || '');
        });
        return labels;
    }

    function syncProfileTitles() {
        var labels = formatLabels();
        $('#mpwcfg-combo-profiles .mpwcfg-combo-profile').each(function (i) {
            var label = labels[i] || 'Nuevo formato';
            $(this).find('.mpwcfg-combo-profile__head strong').text(label);
        });
    }

    function bindTierLabels($profile) {
        $profile.find('.mpwcfg-combo-tier-chip input').off('.mpwcfgTier').on('input.mpwcfgTier change.mpwcfgTier', function () {
            var ti = $profile.find('.mpwcfg-combo-tier-chip input').index(this);
            var value = $(this).val();
            var $table = $profile.find('.mpwcfg-combo-tier-table').eq(ti);
            $table.find('.mpwcfg-combo-tier-table__title').text(value);
            $table.find('.mpwcfg-combo-grid-row input[readonly]').val(value);
        });
    }

    function addEmptyProfile(formatIndex, label) {
        var fi = formatIndex;
        var profile = '' +
            '<div class="mpwcfg-combo-profile" data-format-index="' + fi + '">' +
                '<div class="mpwcfg-combo-profile__head">' +
                    '<span class="mpwcfg-combo-letter">' + String.fromCharCode(65 + fi) + '</span>' +
                    '<strong>' + $('<div>').text(label || 'Nuevo formato').html() + '</strong>' +
                    '<span class="mpwcfg-combo-profile__chevron">⌃</span>' +
                '</div>' +
                '<input type="hidden" name="mpwcfg[combinations][profiles][' + fi + '][format_index]" value="' + fi + '">' +
                '<div class="mpwcfg-combo-profile__body">' +
                    '<div class="mpwcfg-combo-profile__label">' + $('<div>').text('Opciones de ' + levelLabel(1, 'Cantidad de hojas') + ' para este ' + levelLabel(0, 'formato')).html() + '</div>' +
                    '<div class="mpwcfg-combo-tier-chips">' +
                        '<button type="button" class="button button-secondary mpwcfg-combo-add-tier">+ agregar opción</button>' +
                    '</div>' +
                    '<div class="mpwcfg-combo-profile__label">Cantidades y precio</div>' +
                    '<div class="mpwcfg-combo-profile__tables"></div>' +
                '</div>' +
            '</div>';
        $('#mpwcfg-combo-profiles').append(profile);
        var $p = $('#mpwcfg-combo-profiles .mpwcfg-combo-profile').last();
        bindTierLabels($p);
        profileReindex($p);
    }

    $(function () {
        $('.mpwcfg-combo-profile').each(function () { bindTierLabels($(this)); });
        syncProfileTitles();

        $(document).on('input change', '#mpwcfg-combo-levels .mpwcfg-combo-level:first .mpwcfg-combo-option input[type="text"]', function () {
            syncProfileTitles();
        });

        $(document).on('input change', '.mpwcfg-combo-level__label', function () {
            $('#mpwcfg-combo-profiles .mpwcfg-combo-profile__label').each(function () {
                var text = $(this).text();
                if (text.indexOf('para este formato') !== -1) {
                    $(this).text('Opciones de ' + levelLabel(1, 'Cantidad de hojas') + ' para este ' + levelLabel(0, 'formato'));
                }
            });
            $('#mpwcfg-combo-profiles .mpwcfg-combo-grid-head').each(function () {
                var $h = $(this).children('span');
                if ($h.length >= 3) { $h.eq(0).text(levelLabel(1, 'Cantidad de hojas')); $h.eq(1).text(levelLabel(2, 'Unidades')); }
            });
        });

        $(document).on('click', '.mpwcfg-combo-add-option', function () {
            var $level = $(this).closest('.mpwcfg-combo-level');
            var li = $('#mpwcfg-combo-levels .mpwcfg-combo-level').index($level);
            var oi = $level.find('.mpwcfg-combo-option').length;
            var nameBase = 'mpwcfg[combinations][levels][' + li + '][options][' + oi + ']';
            var html = '<div class="mpwcfg-combo-option">' +
                '<span class="dashicons dashicons-menu"></span>' +
                '<input type="text" name="' + nameBase + '[label]" placeholder="Opción">' +
                '<input type="text" name="' + nameBase + '[sku]" placeholder="SKU (opcional)">' +
                '<button type="button" class="button-link-delete mpwcfg-combo-remove-option">×</button>' +
                '</div>';
            $level.find('.mpwcfg-combo-options').append(html);

            if (li === 0) {
                addEmptyProfile(oi, 'Nuevo formato');
            }
            comboReindex();
            syncProfileTitles();
        });

        $(document).on('click', '.mpwcfg-combo-remove-option', function () {
            var $level = $(this).closest('.mpwcfg-combo-level');
            var li = $('#mpwcfg-combo-levels .mpwcfg-combo-level').index($level);
            var oi = $level.find('.mpwcfg-combo-option').index($(this).closest('.mpwcfg-combo-option'));
            $(this).closest('.mpwcfg-combo-option').remove();
            if (li === 0) {
                $('#mpwcfg-combo-profiles .mpwcfg-combo-profile').eq(oi).remove();
                allProfilesReindex();
            }
            comboReindex();
            syncProfileTitles();
        });

        $(document).on('click', '.mpwcfg-combo-add-tier', function () {
            var $p = $(this).closest('.mpwcfg-combo-profile');
            var fi = $('#mpwcfg-combo-profiles .mpwcfg-combo-profile').index($p);
            var ti = $p.find('.mpwcfg-combo-tier-table').length;
            var base = 'mpwcfg[combinations][profiles][' + fi + '][tiers][' + ti + ']';
            var chip = '<span class="mpwcfg-combo-tier-chip">' +
                '<input type="text" name="' + base + '[label]" value="">' +
                '<button type="button" class="mpwcfg-combo-remove-tier">×</button>' +
                '</span>';
            $p.find('.mpwcfg-combo-tier-chips .mpwcfg-combo-add-tier').before(chip);
            var table = '<div class="mpwcfg-combo-tier-table" data-tier-index="' + ti + '">' +
                '<div class="mpwcfg-combo-tier-table__title"></div>' +
                '<div class="mpwcfg-combo-profile-grid">' +
                    '<div class="mpwcfg-combo-grid-head"><span>' + $('<div>').text(levelLabel(1, 'Cantidad de hojas')).html() + '</span><span>' + $('<div>').text(levelLabel(2, 'Unidades')).html() + '</span><span>Precio (CLP)</span><span></span></div>' +
                '</div>' +
                '<button type="button" class="button button-secondary mpwcfg-combo-add-row">+ agregar cantidad</button>' +
                '</div>';
            $p.find('.mpwcfg-combo-profile__tables').append(table);
            bindTierLabels($p);
            profileReindex($p);
        });

        $(document).on('click', '.mpwcfg-combo-remove-tier', function () {
            var $p = $(this).closest('.mpwcfg-combo-profile');
            var ti = $(this).closest('.mpwcfg-combo-tier-chip').index();
            $p.find('.mpwcfg-combo-tier-table').eq(ti).remove();
            $(this).closest('.mpwcfg-combo-tier-chip').remove();
            bindTierLabels($p);
            profileReindex($p);
        });

        $(document).on('click', '.mpwcfg-combo-add-row', function () {
            var $t = $(this).closest('.mpwcfg-combo-tier-table');
            var $p = $(this).closest('.mpwcfg-combo-profile');
            var fi = $('#mpwcfg-combo-profiles .mpwcfg-combo-profile').index($p);
            var ti = $p.find('.mpwcfg-combo-tier-table').index($t);
            var ri = $t.find('.mpwcfg-combo-grid-row').length;
            var label = $t.find('.mpwcfg-combo-tier-table__title').text();
            var base = 'mpwcfg[combinations][profiles][' + fi + '][tiers][' + ti + '][rows][' + ri + ']';
            var row = '<div class="mpwcfg-combo-grid-row">' +
                '<input type="text" value="' + $('<div>').text(label).html() + '" readonly>' +
                '<input type="text" name="' + base + '[quantity]" value="">' +
                '<input type="number" min="0" step="1" name="' + base + '[price]" value="0">' +
                '<button type="button" class="mpwcfg-combo-remove-row">×</button>' +
                '</div>';
            $t.find('.mpwcfg-combo-profile-grid').append(row);
            profileReindex($p);
        });

        $(document).on('click', '.mpwcfg-combo-remove-row', function () {
            var $p = $(this).closest('.mpwcfg-combo-profile');
            $(this).closest('.mpwcfg-combo-grid-row').remove();
            profileReindex($p);
        });
    });
})(jQuery);
