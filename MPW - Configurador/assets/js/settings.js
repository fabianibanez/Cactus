/* MPW · Configurador — panel de apariencia (dashboard) */
(function ($) {
	'use strict';

	$(function () {
		if ($.fn.wpColorPicker) {
			$('.mpwcfg-color-field').wpColorPicker();
		}
	});
})(jQuery);
