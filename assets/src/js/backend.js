/**
 * Backend Scripts
 */

var woograbexpressBackend = {
	renderForm: function () {
		if (!$('#woocommerce_woograbexpress_origin_type') || !$('#woocommerce_woograbexpress_origin_type').length) {
			return;
		}

		// Submit form
		$(document).off('click', '#woograbexpress-btn--save-settings', woograbexpressBackend.submitForm);
		$(document).on('click', '#woograbexpress-btn--save-settings', woograbexpressBackend.submitForm);

		// Toggle Store Origin Fields
		$(document).off('change', '#woocommerce_woograbexpress_origin_type', woograbexpressBackend.toggleStoreOriginFields);
		$(document).on('change', '#woocommerce_woograbexpress_origin_type', woograbexpressBackend.toggleStoreOriginFields);

		$('#woocommerce_woograbexpress_origin_type').trigger('change');

		$('.wc-modal-shipping-method-settings table.form-table').each(function () {
			var $table = $(this);
			var $rows = $table.find('tr');

			if (!$rows.length) {
				$table.remove();
			}
		});

		$('.woograbexpress-field-group').each(function () {
			var $fieldGroup = $(this);

			var fieldGroupId = $fieldGroup
				.attr('id')
				.replace('woocommerce_woograbexpress_field_group_', '');

			var $fieldGroupDescription = $fieldGroup
				.next('p')
				.detach();

			var $fieldGroupTable = $fieldGroup
				.nextAll('table.form-table')
				.first()
				.attr('id', 'woograbexpress-table--' + fieldGroupId)
				.addClass('woograbexpress-table woograbexpress-table--' + fieldGroupId)
				.detach();

			$fieldGroup
				.wrap('<div id="woograbexpress-field-group-wrap--' + fieldGroupId + '" class="woograbexpress-field-group-wrap stuffbox woograbexpress-field-group-wrap--' + fieldGroupId + '"></div>');

			$fieldGroupDescription
				.appendTo('#woograbexpress-field-group-wrap--' + fieldGroupId);

			$fieldGroupTable
				.appendTo('#woograbexpress-field-group-wrap--' + fieldGroupId);

			if ($fieldGroupTable && $fieldGroupTable.length) {
				if ($fieldGroup.hasClass('woograbexpress-field-group-hidden')) {
					$('#woograbexpress-field-group-wrap--' + fieldGroupId)
						.addClass('woograbexpress-hidden');
				}
			} else {
				$('#woograbexpress-field-group-wrap--' + fieldGroupId).remove();
			}
		});

		$('.woograbexpress-field').each(function () {
			var $field = $(this);
			var fieldUnit = $field.attr('data-unit');

			if (!fieldUnit) {
				return;
			}

			$field.next('.woograbexpress-field-unit').remove();
			$field.after('<span class="woograbexpress-field-unit">' + fieldUnit + '</span>');
		});

		var params = _.mapObject(woograbexpress_backend, function (val, key) {
			switch (key) {
				case 'default_lat':
				case 'default_lng':
				case 'test_destination_lat':
				case 'test_destination_lng':
					return parseFloat(val);

				default:
					return val;
			}
		});

		woograbexpressMapPicker.init(params);

		woograbexpressToggleButtons();
	},
	maybeOpenModal: function () {
		// Try show settings modal on settings page.
		if (woograbexpress_backend.showSettings) {
			setTimeout(function () {
				var isMethodAdded = false;
				var methods = $(document).find('.wc-shipping-zone-method-type');
				for (var i = 0; i < methods.length; i++) {
					var method = methods[i];
					if ($(method).text() === woograbexpress_backend.methodTitle) {
						$(method).closest('tr').find('.row-actions .wc-shipping-zone-method-settings').trigger('click');
						isMethodAdded = true;
						return;
					}
				}

				// Show Add shipping method modal if the shipping is not added.
				if (!isMethodAdded) {
					$('.wc-shipping-zone-add-method').trigger('click');
					$('select[name="add_method_id"]').val(woograbexpress_backend.methodId).trigger('change');
				}
			}, 500);
		}
	},
	submitForm: function (e) {
		e.preventDefault();

		if (woograbexpressMapPicker.isEditingAPIKey()) {
			window.alert(woograbexpressError('finish_editing_api'));
		} else {
			$('#btn-ok').trigger('click');
		}
	},
	toggleStoreOriginFields: function (e) {
		e.preventDefault();
		var selected = $(this).val();
		var fields = $(this).data('fields');
		_.each(fields, function (fieldIds, fieldValue) {
			_.each(fieldIds, function (fieldId) {
				if (fieldValue !== selected) {
					$('#' + fieldId).closest('tr').hide();
				} else {
					$('#' + fieldId).closest('tr').show();
				}
			});
		});
	},
	initForm: function () {
		// Init form
		$(document.body).off('wc_backbone_modal_loaded', woograbexpressBackend.renderForm);
		$(document.body).on('wc_backbone_modal_loaded', woograbexpressBackend.renderForm);
	},
	init: function () {
		woograbexpressBackend.initForm();
		woograbexpressBackend.maybeOpenModal();
	}
};

$(document).ready(woograbexpressBackend.init);
