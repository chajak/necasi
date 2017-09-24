var locator = new function () {
    this.lat = null;
    this.lng = null;
    this.address = null;
    this.search = null;

    this.init = function () {
        locator.tryGeolocation();
    }

    this.tryGeolocation = function () {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(locator.browserGeolocationSuccess, locator.browserGeolocationFail, { maximumAge: 50000, timeout: 20000, enableHighAccuracy: true });
        }
        else {
            locator = this.tryAPIGeolocation();
        }
    };

    this.apiGeolocationSuccess = function (position) {
        locator.lat = position.coords.latitude;
        locator.lng = position.coords.lnggitude;
        locator.getAddressFromGps(locator.lat, locator.lng);
    };

    this.browserGeolocationSuccess = function (position) {
        locator.lat = position.coords.latitude;
        locator.lng = position.coords.lnggitude;
        locator.getAddressFromGps(locator.lat, locator.lng);
    };

    this.tryAPIGeolocation = function () {
        var request = new XMLHttpRequest();
        request.open('POST', 'https://www.googleapis.com/geolocation/v1/geolocate?key=AIzaSyCq7ai9qtsKKPA_HfNb_uFQapZ6T4azB8I', true);
        request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
        request.send();

        request.onreadystatechange = function () {
            if (request.readyState == 4 && request.status == 200) {
                var response = "";
                if (!!request.responseText) {
                    response = JSON.parse(request.responseText);
                }

                if (!!response.location) {
                    locator.apiGeolocationSuccess({ coords: { latitude: response.location.lat, lnggitude: response.location.lng } });
                }
            }
        }
    };

    this.browserGeolocationFail = function (error) {
        switch (error.code) {
            case error.TIMEOUT:
                console.log("Browser geolocation error !\n\nTimeout.");
                break;
            case error.PERMISSION_DENIED:
                if (error.message.indexOf("Only secure origins are allowed") == 0) {
                    locator.tryAPIGeolocation();
                }
                break;
            case error.POSITION_UNAVAILABLE:
                console.log("Browser geolocation error !\n\nPosition unavailable.");
                break;
        }
    };

    this.getAddressFromGps = function (lat, lng) {
        var request = new XMLHttpRequest();
        request.open('GET', 'https://maps.googleapis.com/maps/api/geocode/json?latlng=' + lat + ',' + lng + '&key=AIzaSyCq7ai9qtsKKPA_HfNb_uFQapZ6T4azB8I&language=cs&result_type=political', true);
        request.send();

        request.onreadystatechange = function () {
            if (request.readyState == 4 && request.status == 200) {
                var response = "";
                if (!!request.responseText) {
                    response = JSON.parse(request.responseText);
                    if(!!response.results) {
                        locator.updateSearch(response.results[0], true);
                    }
                }
            }
        }
    }

    this.getGpsFromAddress = function(searchedAddress, updateLocatorSearch) {
        if(updateLocatorSearch == true) {
            locator.search = searchedAddress;
        }

        var encodedAddress = encodeURI(searchedAddress);
        var request = new XMLHttpRequest();
        request.open('GET', 'https://maps.googleapis.com/maps/api/geocode/json?address=' + encodedAddress + '&key=AIzaSyCq7ai9qtsKKPA_HfNb_uFQapZ6T4azB8I&language=cs', true);
        request.send();

        request.onreadystatechange = function () {
            if (request.readyState == 4 && request.status == 200) {
                var response = "";
                if (!!request.responseText) {
                    response = JSON.parse(request.responseText);
                    if(!!response.results) {
                        locator.updateSearch(response.results[0], false);
                    }
                }
            }
        }
    }

    this.updateSearch = function (address, updateSearchText) {
        locator.address = address;
        locator.lat = locator.address.geometry.location.lat;
        locator.lng = locator.address.geometry.location.lng;
        
        if(updateSearchText == true) {
            locator.search = locator.address.formatted_address;
        }

        locator.updateFields();
    }

    this.updateFields = function () {
        document.getElementById("search").value = locator.search;
        document.getElementById("lat").value = locator.lat;
        document.getElementById("lng").value = locator.lng;
    }
}

locator.init();

function processForm(e) {
    var searchedAddress = document.getElementById("search").value;
    if(searchedAddress == locator.search) {
        //not changed address
        console.log("Address: " + searchedAddress + " SAME (GPS: " + locator.lat + "," + locator.lng + ")");
    }
    else {
        console.log("Address: " + searchedAddress + " NOT SAME - RECALCULATING");
        locator.getGpsFromAddress(searchedAddress, true);
    }
    e.preventDefault();
    return false;
}

window.onload = function () {
    var form = document.getElementById('searchForm');
    form.addEventListener("submit", processForm);
};