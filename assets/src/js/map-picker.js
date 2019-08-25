
// Taking Over window.console.error
var isMapError = undefined, isMapErrorInterval;

var windowConsoleError = window.console.error;

window.console.error = function () {
    if (arguments[0].toLowerCase().indexOf('google') !== -1) {
        isMapError = arguments[0];
    }

    windowConsoleError.apply(windowConsoleError, arguments);
};

/**
 * Map Picker
 */
var woograbexpressMapPicker = {
    params: {},
    origin_lat: '',
    origin_lng: '',
    origin_address: '',
    zoomLevel: 16,
    apiKeyBrowser: '',
    init: function (params) {
        'use strict';

        woograbexpressMapPicker.params = params;

        // Edit Api Key
        $(document).off('click', '.woograbexpress-edit-api-key', woograbexpressMapPicker.editApiKey);
        $(document).on('click', '.woograbexpress-edit-api-key', woograbexpressMapPicker.editApiKey);

        // Get API Key
        $(document).off('click', '#woograbexpress-btn--get-api-key', woograbexpressMapPicker.getApiKey);
        $(document).on('click', '#woograbexpress-btn--get-api-key', woograbexpressMapPicker.getApiKey);

        // Show Store Location Picker
        $(document).off('click', '.woograbexpress-edit-location', woograbexpressMapPicker.showStoreLocationPicker);
        $(document).on('click', '.woograbexpress-edit-location', woograbexpressMapPicker.showStoreLocationPicker);

        // Hide Store Location Picker
        $(document).off('click', '#woograbexpress-btn--map-cancel', woograbexpressMapPicker.hideStoreLocationPicker);
        $(document).on('click', '#woograbexpress-btn--map-cancel', woograbexpressMapPicker.hideStoreLocationPicker);

        // Apply Store Location
        $(document).off('click', '#woograbexpress-btn--map-apply', woograbexpressMapPicker.applyStoreLocation);
        $(document).on('click', '#woograbexpress-btn--map-apply', woograbexpressMapPicker.applyStoreLocation);

        // Toggle Map Search Panel
        $(document).off('click', '#woograbexpress-map-search-panel-toggle', woograbexpressMapPicker.toggleMapSearch);
        $(document).on('click', '#woograbexpress-map-search-panel-toggle', woograbexpressMapPicker.toggleMapSearch);
    },
    testDistanceMatrix: function () {
        var origin = new google.maps.LatLng(parseFloat(woograbexpressMapPicker.params.defaultLat), parseFloat(woograbexpressMapPicker.params.defaultLng));
        var destination = new google.maps.LatLng(parseFloat(woograbexpressMapPicker.params.testLat), parseFloat(woograbexpressMapPicker.params.testLng));
        var service = new google.maps.DistanceMatrixService();

        service.getDistanceMatrix(
            {
                origins: [origin],
                destinations: [destination],
                travelMode: 'DRIVING',
                unitSystem: google.maps.UnitSystem.METRIC
            }, function (response, status) {
                if (status.toLowerCase() === 'ok') {
                    isMapError = false;
                } else {
                    if (response.error_message) {
                        isMapError = response.error_message;
                    } else {
                        isMapError = 'Error: ' + status;
                    }
                }
            });
    },
    editApiKey: function (e) {
        'use strict';

        e.preventDefault();

        var $link = $(e.currentTarget);
        var $input = $link.closest('tr').find('input[type=hidden]');
        var $inputDummy = $link.closest('tr').find('input[type=text]');
        var apiKey = $input.val();
        var apiKeyDummy = $inputDummy.val();

        if ($link.hasClass('editing')) {
            if (apiKey !== apiKeyDummy) {
                $link.addClass('loading').attr('disabled', true);

                switch ($link.attr('id')) {
                    case 'api_key': {
                        woograbexpressMapPicker.initMap(apiKeyDummy, woograbexpressMapPicker.testDistanceMatrix);

                        clearInterval(isMapErrorInterval);

                        isMapErrorInterval = setInterval(function () {
                            if (typeof isMapError !== 'undefined') {
                                clearInterval(isMapErrorInterval);

                                if (isMapError) {
                                    $inputDummy.val(apiKey);
                                    window.alert(isMapError);
                                } else {
                                    $input.val(apiKeyDummy);
                                }

                                $link.removeClass('loading editing').attr('disabled', false);
                                $inputDummy.prop('readonly', true);
                            }
                        }, 100);
                        break;
                    }

                    default: {
                        $.ajax({
                            method: "POST",
                            url: woograbexpressMapPicker.params.ajax_url,
                            data: {
                                action: "woograbexpress_validate_api_key_server",
                                nonce: woograbexpressMapPicker.params.validate_api_key_nonce,
                                key: apiKeyDummy,
                            }
                        }).done(function () {
                            // Set new API Key value
                            $input.val(apiKeyDummy);
                        }).fail(function (error) {
                            // Restore existing API Key value
                            $inputDummy.val(apiKey);

                            // Show error
                            if (error.responseJSON && error.responseJSON.data) {
                                return window.alert(error.responseJSON.data);
                            }

                            if (error.statusText) {
                                return window.alert(error.statusText);
                            }

                            window.alert('Error');
                        }).always(function () {
                            $link.removeClass('loading editing').attr('disabled', false);
                            $inputDummy.prop('readonly', true);
                        });
                    }
                }
            } else {
                $link.removeClass('editing');
                $inputDummy.prop('readonly', true);
            }
        } else {
            $link.addClass('editing');
            $inputDummy.prop('readonly', false);
        }
    },
    getApiKey: function (e) {
        'use strict';

        e.preventDefault();

        window.open('https://cloud.google.com/maps-platform/#get-started', '_blank').focus();
    },
    showStoreLocationPicker: function (e) {
        'use strict';

        e.preventDefault();

        $('.modal-close-link').hide();

        toggleBottons({
            left: {
                id: 'map-cancel',
                label: 'Back',
                icon: 'undo'
            },
            right: {
                id: 'map-apply',
                label: 'Apply Changes',
                icon: 'editor-spellcheck'
            }
        });

        $('#woograbexpress-field-group-wrap--location_picker').fadeIn().siblings().hide();

        woograbexpressMapPicker.initMap($('#woocommerce_woograbexpress_api_key').val(), woograbexpressMapPicker.renderMap);
    },
    hideStoreLocationPicker: function (e) {
        'use strict';

        e.preventDefault();

        woograbexpressMapPicker.destroyMap();

        $('.modal-close-link').show();

        toggleBottons();

        $('#woograbexpress-field-group-wrap--location_picker').hide().siblings().not('.woograbexpress-hidden').fadeIn();
    },
    applyStoreLocation: function (e) {
        'use strict';

        e.preventDefault();

        if (isMapError) {
            return;
        }

        woograbexpressMapPicker.initMap($('#woocommerce_woograbexpress_api_key').val(), woograbexpressMapPicker.testDistanceMatrix);

        clearInterval(isMapErrorInterval);

        isMapErrorInterval = setInterval(function () {
            if (typeof isMapError !== 'undefined') {
                clearInterval(isMapErrorInterval);

                if (isMapError) {
                    window.alert(isMapError);
                } else {
                    $('#woocommerce_woograbexpress_origin_lat').val(woograbexpressMapPicker.origin_lat);
                    $('#woocommerce_woograbexpress_origin_lng').val(woograbexpressMapPicker.origin_lng);
                    $('#woocommerce_woograbexpress_origin_address').val(woograbexpressMapPicker.origin_address);
                    woograbexpressMapPicker.hideStoreLocationPicker(e);
                }
            }
        }, 100);
    },
    toggleMapSearch: function (e) {
        'use strict';

        e.preventDefault();

        $("#woograbexpress-map-search-panel")
            .toggleClass('expanded')
            .find('.dashicons')
            .toggleClass('dashicons-dismiss dashicons-search');
    },
    initMap: function (apiKey, callback) {
        woograbexpressMapPicker.destroyMap();

        isMapError = undefined;

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

        $('#woograbexpress-map-search-panel').removeClass('woograbexpress-hidden');

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
                        '<span class="woograbexpress-map-pin-label">' + woograbexpressMapPicker.params.i18n.latitude + '</span><span class="woograbexpress-map-pin-value">' + location.lat().toString() + '</span>',
                        '<span class="woograbexpress-map-pin-label">' + woograbexpressMapPicker.params.i18n.longitude + '</span><span class="woograbexpress-map-pin-value">' + location.lng().toString() + '</span>'
                    ];

                    infowindow.setContent('<div class="woograbexpress-map-pin-info">' + infowindowContents.join('</div><div class="woograbexpress-map-pin-info">') + '</div>');
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
    }
};
