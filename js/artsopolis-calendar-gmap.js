(function($) {
    $(document).ready(function(){
        if (typeof __elisoft !== 'undefined' && __elisoft.art_calendar && __elisoft.art_calendar.google_map_api_key != '') {
            var browserKey = __elisoft.art_calendar.google_map_api_key;
            var latitude = '';
            var longitude = '';
            var fullAddress = '';

            if(__elisoft.art_calendar && __elisoft.art_calendar.latitude ) {
                latitude = __elisoft.art_calendar.latitude;
                longitude = __elisoft.art_calendar.longitude;
            }
            else if(__elisoft.art_calendar && __elisoft.art_calendar.gmap_address) {
                fullAddress = __elisoft.art_calendar.gmap_address;
            }

            $.getScript('https://maps.googleapis.com/maps/api/js?key=' + browserKey, function() {
                geocoder = new google.maps.Geocoder();
                var mapOptions = {
                    zoom: 16
                };
                map = new google.maps.Map(document.getElementById('artsopolis_calendar_map_canvas'), mapOptions);
                var latLng = {};
                if (fullAddress == '') {
                    latLng = {lat:parseFloat(latitude), lng:parseFloat(longitude)};
                    map.setCenter(latLng);
                    aeeSetMarker(map, latLng);
                } else {
                    geocoder.geocode( { 'address': fullAddress}, function(results, status) {
                        if (status == 'OK') {
                            map.setCenter(results[0].geometry.location);
                            latLng = results[0].geometry.location;
                            aeeSetMarker(map, latLng);
                        } else {
                            alert('Geocode was not successful for the following reason: ' + status);
                        }
                    });
                }

            });
        }

    });

    var aeeSetMarker = function (map, latLng) {
        var marker = new google.maps.Marker({
            map: map,
            position: latLng,
            draggable:true
        });
    }
            
})(jQuery);


