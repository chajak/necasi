var locator = new function () {
    this.lat = null;
    this.lng = null;
    this.address = null;
    this.search = null;
    this.cookieObj = null;

    this.init = function () {
        locator.cookieObj = cook.areCookiesSet();
        if (!locator.cookieObj) {
            locator.tryGeolocation();
        }
        else {
            locator.setDataFromCookieObj();
        }
    }

    this.tryGeolocation = function () {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(locator.browserGeolocationSuccess, locator.browserGeolocationFail, { maximumAge: 50000, timeout: 20000, enableHighAccuracy: true });
        }
        else {
            locator = locator.tryAPIGeolocation();
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
                    if (!!response.results) {
                        locator.updateSearch(response.results[0], true);
                    }
                }
            }
        }
    }

    this.getGpsFromAddress = function (searchedAddress, updateLocatorSearch) {
        if (updateLocatorSearch == true) {
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
                    if (!!response.results) {
                        locator.updateSearch(response.results[0], false);
                    }
                }
            }
        }
    }

    this.setCookieObj = function() {
        locator.cookieObj = { lat: locator.lat, lng: locator.lng, address: JSON.stringify(locator.address), search: locator.search };
        cook.setCookies(locator.cookieObj);
    }

    this.updateSearch = function (address, updateSearchText) {
        locator.address = address;
        locator.lat = locator.address.geometry.location.lat;
        locator.lng = locator.address.geometry.location.lng;

        if (updateSearchText == true) {
            locator.search = locator.address.formatted_address;
        }

        locator.updateFields();
        locator.setCookieObj();
    }

    this.updateFields = function () {
        document.getElementById("search").value = locator.search;
        document.getElementById("lat").value = locator.lat;
        document.getElementById("lng").value = locator.lng;
    }

    this.setDataFromCookieObj = function (cookieObj) {
        if(!!cookieObj) {
            locator.cookieObj = cookieObj;
        }

        locator.lat = locator.cookieObj.lat;
        locator.lng = locator.cookieObj.lng;
        locator.address = locator.cookieObj.address;
        locator.search = locator.cookieObj.search;

        locator.updateFields();
    }
}

var cook = new function() {
    this.setCookie = function(cname, cvalue, exdays) {
        var d = new Date();
        d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
        var expires = "expires=" + d.toUTCString();
        document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
    }
    
    this.setCookies = function(cookieObj) {
        var cookieDaysExpiration = 30;
        for (var key in cookieObj) {
            cook.setCookie(key, cookieObj[key], cookieDaysExpiration);
        }
    }
    
    this.getCookie = function(cname) {
        var name = cname + "=";
        var ca = document.cookie.split(';');
        for (var i = 0; i < ca.length; i++) {
            var c = ca[i];
            while (c.charAt(0) == ' ') {
                c = c.substring(1);
            }
            if (c.indexOf(name) == 0) {
                return c.substring(name.length, c.length);
            }
        }
        return "";
    }
    
    this.areCookiesSet = function() {
        var lat = cook.getCookie("lat");
        if(!!lat) {
            var lng = cook.getCookie("lng");
            if(!!lng) {
                var address = cook.getCookie("address");
                if(!!address) {
                    var search = cook.getCookie("search");
                    if(!!search) {
                        return { lat: lat, lng: lng, address: JSON.parse(address), search: search};
                    }
                }
            }
        }

        return null;
    }
}

function processForm(e) {
    var searchedAddress = document.getElementById("search").value;
    if (searchedAddress == locator.search) {
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
    locator.init();
    var form = document.getElementById('searchForm');
    form.addEventListener("submit", processForm);
};