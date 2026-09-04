/**
 * Admin: Media Library picker for the image fields in the Product / Testimonial meta boxes
 * (markup in inc/post-types.php: .dq-image-field > input + .dq-pick-image + .dq-clear-image + .dq-image-preview).
 */
(function ($) {
	'use strict';

	function setPreview($field, url) {
		var $img = $field.find('.dq-image-preview');
		if (url && /^https?:\/\//i.test(url)) {
			$img.attr('src', url).show();
		} else if (!url) {
			$img.attr('src', '').hide();
		}
	}

	$(document).on('click', '.dq-pick-image', function (e) {
		e.preventDefault();
		if (!window.wp || !wp.media) {
			return;
		}
		var $field = $(this).closest('.dq-image-field');
		var frame = wp.media({
			title: 'Select image',
			library: { type: 'image' },
			button: { text: 'Use this image' },
			multiple: false
		});
		frame.on('select', function () {
			var a = frame.state().get('selection').first().toJSON();
			var url = a.sizes && a.sizes.full ? a.sizes.full.url : a.url;
			$field.find('input[type=text]').val(url).trigger('change');
			setPreview($field, url);
		});
		frame.open();
	});

	$(document).on('click', '.dq-clear-image', function (e) {
		e.preventDefault();
		var $field = $(this).closest('.dq-image-field');
		$field.find('input[type=text]').val('').trigger('change');
		setPreview($field, '');
	});

	// Pasting an absolute URL updates the preview; theme-relative "assets/…" paths keep the server-rendered preview.
	$(document).on('change', '.dq-image-field input[type=text]', function () {
		setPreview($(this).closest('.dq-image-field'), $.trim($(this).val()));
	});
})(jQuery);
