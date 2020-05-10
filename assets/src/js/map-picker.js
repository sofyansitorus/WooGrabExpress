/**
 * Map Picker
 */
var woograbexpressMapPicker = {
	params: {},
	origin_lat: '',
	origin_lng: '',
	origin_address: '',
	zoomLevel: 16,
	apiKeyErrorCheckInterval: null,
	apiKeyError: '',
	editingAPIKey: false,
	init: function (params) {
		woograbexpressMapPicker.params = params;
		woograbexpressMapPicker.apiKeyError = '';
		woograbexpressMapPicker.editingAPIKey = false;

		ConsoleListener.on('error', function (errorMessage) {
			if (errorMessage.toLowerCase().indexOf('google') !== -1) {
				woograbexpressMapPicker.apiKeyError = errorMessage;
			}

			if ($('.gm-err-message').length) {
				$('.gm-err-message').replaceWith('<p style="text-align:center">' + woograbexpressMapPicker.convertError(errorMessage) + '</p>');
			}
		});

		$('[data-link="api_key"]').each(function () {
			$(this).after(wp.template('woograbexpress-button')({
				href: '#',
				class: 'woograbexpress-buttons--has-icon woograbexpress-api-key-button',
				text: '<span class="dashicons"></span>',
			}));
		});

		// Edit Api Key
		$(document).off('focus', '[data-link="api_key"]');
		$(document).on('focus', '[data-link="api_key"]', function () {
			if ($(this).prop('readonly') && !$(this).hasClass('loading')) {
				$(this).data('value', $(this).val()).prop('readonly', false);
			}
		});

		$(document).off('blur', '[data-link="api_key"]');
		$(document).on('blur', '[data-link="api_key"]', function () {
			if (!$(this).prop('readonly') && !$(this).hasClass('editing')) {
				$(this).data('value', undefined).prop('readonly', true);
			}
		});

		$(document).off('input', '[data-link="api_key"]', woograbexpressMapPicker.handleApiKeyInput);
		$(document).on('input', '[data-link="api_key"]', woograbexpressMapPicker.handleApiKeyInput);

		// Edit Api Key
		$(document).off('click', '.woograbexpress-api-key-button', woograbexpressMapPicker.editApiKey);
		$(document).on('click', '.woograbexpress-api-key-button', woograbexpressMapPicker.editApiKey);

		// Show Store Location Picker
		$(document).off('click', '.woograbexpress-field--origin');
		$(document).on('click', '.woograbexpress-field--origin', function () {
			if ($(this).prop('readonly')) {
				$('.woograbexpress-edit-location-picker').trigger('click');
			}
		});

		// Show Store Location Picker
		$(document).off('focus', '[data-link="location_picker"]', woograbexpressMapPicker.showLocationPicker);
		$(document).on('focus', '[data-link="location_picker"]', woograbexpressMapPicker.showLocationPicker);

		// Hide Store Location Picker
		$(document).off('click', '#woograbexpress-btn--map-cancel', woograbexpressMapPicker.hideLocationPicker);
		$(document).on('click', '#woograbexpress-btn--map-cancel', woograbexpressMapPicker.hideLocationPicker);

		// Apply Store Location
		$(document).off('click', '#woograbexpress-btn--map-apply', woograbexpressMapPicker.applyLocationPicker);
		$(document).on('click', '#woograbexpress-btn--map-apply', woograbexpressMapPicker.applyLocationPicker);

		// Toggle Map Search Panel
		$(document).off('click', '#woograbexpress-map-search-panel-toggle', woograbexpressMapPicker.toggleMapSearch);
		$(document).on('click', '#woograbexpress-map-search-panel-toggle', woograbexpressMapPicker.toggleMapSearch);
	},
	validateAPIKeyBothSide: function ($input) {
		woograbexpressMapPicker.validateAPIKeyServerSide($input, woograbexpressMapPicker.validateAPIKeyBrowserSide);
	},
	validateAPIKeyBrowserSide: function ($input) {
		woograbexpressMapPicker.apiKeyError = '';

		woograbexpressMapPicker.initMap($input.val(), function () {
			var geocoderArgs = {
				latLng: new google.maps.LatLng(parseFloat(woograbexpressMapPicker.params.defaultLat), parseFloat(woograbexpressMapPicker.params.defaultLng)),
			};

			var geocoder = new google.maps.Geocoder();

			geocoder.geocode(geocoderArgs, function (results, status) {
				if (status.toLowerCase() === 'ok') {
					console.log('validateAPIKeyBrowserSide', results);

					$input.addClass('valid');

					setTimeout(function () {
						$input.removeClass('editing loading valid');
					}, 2000);
				}
			});

			clearInterval(woograbexpressMapPicker.apiKeyErrorCheckInterval);

			woograbexpressMapPicker.apiKeyErrorCheckInterval = setInterval(function () {
				if ($input.hasClass('valid') || woograbexpressMapPicker.apiKeyError) {
					clearInterval(woograbexpressMapPicker.apiKeyErrorCheckInterval);
				}

				if (woograbexpressMapPicker.apiKeyError) {
					woograbexpressMapPicker.showError($input, woograbexpressMapPicker.apiKeyError);
					$input.prop('readonly', false).removeClass('loading');
				}
			}, 300);
		});
	},
	validateAPIKeyServerSide: function ($input, onSuccess) {
		$.ajax({
			method: 'POST',
			url: woograbexpressMapPicker.params.ajax_url,
			data: {
				action: 'woograbexpress_validate_api_key_server',
				nonce: woograbexpressMapPicker.params.validate_api_key_nonce,
				key: $input.val(),
			}
		}).done(function (response) {
			console.log('validateAPIKeyServerSide', response);

			if (typeof onSuccess === 'function') {
				onSuccess($input);
			} else {
				$input.addClass('valid');

				setTimeout(function () {
					$input.removeClass('editing loading valid');
				}, 2000);
			}
		}).fail(function (error) {
			if (error.responseJSON && error.responseJSON.data) {
				woograbexpressMapPicker.showError($input, error.responseJSON.data);
			} else if (error.statusText) {
				woograbexpressMapPicker.showError($input, error.statusText);
			} else {
				woograbexpressMapPicker.showError($input, 'Google Distance Matrix API error: Uknown');
			}

			$input.prop('readonly', false).removeClass('loading');
		});
	},
	showError: function ($input, errorMessage) {
		$('<div class="error notice woograbexpress-error-box"><p>' + woograbexpressMapPicker.convertError(errorMessage) + '</p></div>')
			.hide()
			.appendTo($input.closest('td'))
			.slideDown();
	},
	removeError: function ($input) {
		$input.closest('td')
			.find('.woograbexpress-error-box')
			.remove();
	},
	convertError: function (text) {
		var exp = /(\b(https?|ftp|file):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|])/ig;
		return text.replace(exp, "<a href='$1' target='_blank'>$1</a>");
	},
	handleApiKeyInput: function (e) {
		var $input = $(e.currentTarget);

		if ($input.val() === $input.data('value')) {
			$input.removeClass('editing').next('.woograbexpress-edit-api-key').removeClass('editing');
		} else {
			$input.addClass('editing').next('.woograbexpress-edit-api-key').addClass('editing');
		}

		woograbexpressMapPicker.removeError($input);
	},
	editApiKey: function (e) {
		e.preventDefault();

		var $input = $(this).blur().prev('input');

		if (!$input.hasClass('editing') || $input.hasClass('loading')) {
			return;
		}

		$input.prop('readonly', true).addClass('loading');

		if ($input.attr('data-key') === 'api_key') {
			woograbexpressMapPicker.validateAPIKeyServerSide($input);
		} else {
			woograbexpressMapPicker.validateAPIKeyBrowserSide($input);
		}

		woograbexpressMapPicker.removeError($input);
	},
	showLocationPicker: function (event) {
		event.preventDefault();

		$(this).blur();

		woograbexpressMapPicker.apiKeyError = '';

		var api_key_picker = $('#woocommerce_woograbexpress_api_key_picker').val();

		if (woograbexpressMapPicker.isEditingAPIKey()) {
			return window.alert(woograbexpressError('finish_editing_api'));
		} else if (!api_key_picker.length) {
			return window.alert(woograbexpressError('api_key_picker_empty'));
		}

		$('.modal-close-link').hide();

		woograbexpressToggleButtons({
			btn_left: {
				id: 'map-cancel',
				label: woograbexpressI18n('buttons.Cancel'),
				icon: 'undo'
			},
			btn_right: {
				id: 'map-apply',
				label: woograbexpressI18n('buttons.Apply Changes'),
				icon: 'editor-spellcheck'
			}
		});

		$('#woograbexpress-field-group-wrap--location_picker').fadeIn().siblings().hide();

		var $subTitle = $('#woograbexpress-field-group-wrap--location_picker').find('.wc-settings-sub-title').first().addClass('woograbexpress-hidden');

		$('.wc-backbone-modal-header').find('h1').append('<span>' + $subTitle.text() + '</span>');

		woograbexpressMapPicker.initMap(api_key_picker, woograbexpressMapPicker.renderMap);
	},
	hideLocationPicker: function (e) {
		e.preventDefault();

		woograbexpressMapPicker.destroyMap();

		$('.modal-close-link').show();

		woograbexpressToggleButtons();

		$('#woograbexpress-field-group-wrap--location_picker').find('.wc-settings-sub-title').first().removeClass('woograbexpress-hidden');

		$('.wc-backbone-modal-header').find('h1 span').remove();

		$('#woograbexpress-field-group-wrap--location_picker').hide().siblings().not('.woograbexpress-hidden').fadeIn();
	},
	applyLocationPicker: function (e) {
		e.preventDefault();

		if (!woograbexpressMapPicker.apiKeyError) {
			$('#woocommerce_woograbexpress_origin_lat').val(woograbexpressMapPicker.origin_lat);
			$('#woocommerce_woograbexpress_origin_lng').val(woograbexpressMapPicker.origin_lng);
			$('#woocommerce_woograbexpress_origin_address').val(woograbexpressMapPicker.origin_address);
		}

		woograbexpressMapPicker.hideLocationPicker(e);
	},
	toggleMapSearch: function (e) {
		e.preventDefault();

		$('#woograbexpress-map-search-panel').toggleClass('expanded');
	},
	initMap: function (apiKey, callback) {
		woograbexpressMapPicker.destroyMap();

		if (_.isEmpty(apiKey)) {
			apiKey = 'InvalidKey';
		}

		$.getScript('https://maps.googleapis.com/maps/api/js?libraries=geometry,places&key=' + apiKey, callback);
	},
	renderMap: function () {
		woograbexpressMapPicker.origin_lat = $('#woocommerce_woograbexpress_origin_lat').val();
		woograbexpressMapPicker.origin_lng = $('#woocommerce_woograbexpress_origin_lng').val();

		var currentLatLng = {
			lat: _.isEmpty(woograbexpressMapPicker.origin_lat) ? parseFloat(woograbexpressMapPicker.params.defaultLat) : parseFloat(woograbexpressMapPicker.origin_lat),
			lng: _.isEmpty(woograbexpressMapPicker.origin_lng) ? parseFloat(woograbexpressMapPicker.params.defaultLng) : parseFloat(woograbexpressMapPicker.origin_lng)
		};

		var map = new google.maps.Map(
			document.getElementById('woograbexpress-map-canvas'),
			{
				mapTypeId: 'roadmap',
				center: currentLatLng,
				zoom: woograbexpressMapPicker.zoomLevel,
				streetViewControl: false,
				mapTypeControl: false
			}
		);

		var marker = new google.maps.Marker({
			map: map,
			position: currentLatLng,
			draggable: true,
			icon: woograbexpressMapPicker.params.marker
		});

		var infowindow = new google.maps.InfoWindow({ maxWidth: 350 });

		if (_.isEmpty(woograbexpressMapPicker.origin_lat) || _.isEmpty(woograbexpressMapPicker.origin_lng)) {
			infowindow.setContent(woograbexpressMapPicker.params.i18n.drag_marker);
			infowindow.open(map, marker);
		} else {
			woograbexpressMapPicker.setLatLng(marker.position, marker, map, infowindow);
		}

		google.maps.event.addListener(marker, 'dragstart', function () {
			infowindow.close();
		});

		google.maps.event.addListener(marker, 'dragend', function (event) {
			woograbexpressMapPicker.setLatLng(event.latLng, marker, map, infowindow);
		});

		$('#woograbexpress-map-wrap').prepend(wp.template('woograbexpress-map-search-panel')());
		map.controls[google.maps.ControlPosition.TOP_LEFT].push(document.getElementById('woograbexpress-map-search-panel'));

		var mapSearchBox = new google.maps.places.SearchBox(document.getElementById('woograbexpress-map-search-input'));

		// Bias the SearchBox results towards current map's viewport.
		map.addListener('bounds_changed', function () {
			mapSearchBox.setBounds(map.getBounds());
		});

		var markers = [];

		// Listen for the event fired when the user selects a prediction and retrieve more details for that place.
		mapSearchBox.addListener('places_changed', function () {
			var places = mapSearchBox.getPlaces();

			if (places.length === 0) {
				return;
			}

			// Clear out the old markers.
			markers.forEach(function (marker) {
				marker.setMap(null);
			});

			markers = [];

			// For each place, get the icon, name and location.
			var bounds = new google.maps.LatLngBounds();

			places.forEach(function (place) {
				if (!place.geometry) {
					console.log('Returned place contains no geometry');
					return;
				}

				marker = new google.maps.Marker({
					map: map,
					position: place.geometry.location,
					draggable: true,
					icon: woograbexpressMapPicker.params.marker
				});

				woograbexpressMapPicker.setLatLng(place.geometry.location, marker, map, infowindow);

				google.maps.event.addListener(marker, 'dragstart', function () {
					infowindow.close();
				});

				google.maps.event.addListener(marker, 'dragend', function (event) {
					woograbexpressMapPicker.setLatLng(event.latLng, marker, map, infowindow);
				});

				// Create a marker for each place.
				markers.push(marker);

				if (place.geometry.viewport) {
					// Only geocodes have viewport.
					bounds.union(place.geometry.viewport);
				} else {
					bounds.extend(place.geometry.location);
				}
			});

			map.fitBounds(bounds);
		});

		setTimeout(function () {
			$('#woograbexpress-map-search-panel').removeClass('woograbexpress-hidden');
		}, 500);
	},
	destroyMap: function () {
		if (window.google) {
			window.google = undefined;
		}

		$('#woograbexpress-map-canvas').empty();
		$('#woograbexpress-map-search-panel').remove();
	},
	setLatLng: function (location, marker, map, infowindow) {
		var geocoder = new google.maps.Geocoder();

		geocoder.geocode(
			{
				latLng: location
			},
			function (results, status) {
				if (status === google.maps.GeocoderStatus.OK && results[0]) {
					var infowindowContents = [
						woograbexpressMapPicker.params.i18n.latitude + ': ' + location.lat().toString(),
						woograbexpressMapPicker.params.i18n.longitude + ': ' + location.lng().toString()
					];

					infowindow.setContent(infowindowContents.join('<br />'));
					infowindow.open(map, marker);

					marker.addListener('click', function () {
						infowindow.open(map, marker);
					});

					$('#woograbexpress-map-search-input').val(results[0].formatted_address);

					woograbexpressMapPicker.origin_lat = location.lat();
					woograbexpressMapPicker.origin_lng = location.lng();
					woograbexpressMapPicker.origin_address = results[0].formatted_address;
				}
			}
		);

		map.setCenter(location);
	},
	isEditingAPIKey: function () {
		return $('[data-link="api_key"].editing').length > 0;
	},
};
