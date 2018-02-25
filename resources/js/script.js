var locator = new function () {
    this.lat = null;
    this.lng = null;
    this.address = null;
    this.search = null;
    this.cookieObj = null;

    this.init = function () {
        this.cookieObj = cook.areCookiesSet();
        if (!this.cookieObj) {
            this.tryGeolocation();
        }
        else {
            this.setDataFromCookieObj();
        }
    }

    this.tryGeolocation = function () {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(this.browserGeolocationSuccess, this.browserGeolocationFail, { maximumAge: 50000, timeout: 20000, enableHighAccuracy: true });
        }
        else {
            this.tryAPIGeolocation();
        }
    };

    this.apiGeolocationSuccess = function (position) {
        this.lat = position.coords.latitude;
        this.lng = position.coords.lnggitude;
        this.getAddressFromGps(this.lat, this.lng);
    };

    this.browserGeolocationSuccess = function (position) {
        this.lat = position.coords.latitude;
        this.lng = position.coords.lnggitude;
        this.getAddressFromGps(locator.lat, locator.lng);
    };

    this.tryAPIGeolocation = function () {
        var self = this;

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
                    self.apiGeolocationSuccess({ coords: { latitude: response.location.lat, lnggitude: response.location.lng } });
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
                    this.tryAPIGeolocation();
                }
                break;
            case error.POSITION_UNAVAILABLE:
                console.log("Browser geolocation error !\n\nPosition unavailable.");
                break;
        }
    };

    this.getAddressFromGps = function () {
        var self = this;
        var request = new XMLHttpRequest();
        request.open('GET', 'https://maps.googleapis.com/maps/api/geocode/json?latlng=' + this.lat + ',' + this.lng + '&key=AIzaSyCq7ai9qtsKKPA_HfNb_uFQapZ6T4azB8I&language=cs&result_type=political', true);
        request.send();

        request.onreadystatechange = function () {
            if (request.readyState == 4 && request.status == 200) {
                var response = "";
                if (!!request.responseText) {
                    response = JSON.parse(request.responseText);
                    if (!!response && !!response.results) {
                        self.updateSearch(response.results[0], true);
                        weatherman.locator = self;
                        weatherman.getWeather();
                    }
                }
            }
        }
    }

    this.getGpsFromAddress = function (searchedAddress, updateLocatorSearch) {
        var self = this;

        if (updateLocatorSearch == true) {
            self.search = searchedAddress;
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
                    if (!!response.results ) {
                        weatherman.locator = self;
                        self.updateSearch(response.results[0], false);
                        
                        weatherman.getWeather();
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
        this.address = address;
        this.lat = this.address.geometry.location.lat;
        this.lng = this.address.geometry.location.lng;

        if (updateSearchText == true) {
            this.search = this.address.formatted_address;
        }

        this.updateFields();
        this.setCookieObj();
    }

    this.updateFields = function () {
        document.getElementById("search").value = this.search;
        document.getElementById("lat").value = this.lat;
        document.getElementById("lng").value = this.lng;
    }

    this.setDataFromCookieObj = function (cookieObj) {
        if(!!cookieObj) {
            this.cookieObj = cookieObj;
        }

        this.lat = this.cookieObj.lat;
        this.lng = this.cookieObj.lng;
        this.address = this.cookieObj.address;
        this.search = this.cookieObj.search;

        this.updateFields();
    }
}

var weatherman = new function() {
    this.locator = null;
    this.datetime = "";
    this.url = "";

    this.hourTemplate = function(hour) {
        var temperatureUnit = "°C";
        var cloudinessUnit = "%";
        var fogUnit = "%";
        var rainUnit = "mm";
    
        return '' + 
        '<div class="col-sm-2 col-md-2 hour">' +
            '<div class="row formattedTime">' + hour.formattedTime + '</div>' +
            '<div class="row temperature">' + hour.temperature + ' ' + temperatureUnit + '</div>' +
            '<div class="row cloudiness">' + hour.cloudiness + ' ' + cloudinessUnit + '</div>' +
            '<div class="row rain">' + hour.rain + ' ' + rainUnit + '</div>' +
            ((hour.fog > 0) ? '<div class="row fog">' + hour.fog + ' ' + fogUnit + '</div>' : '') + 
        '</div>';
    }

    this.getWeather = function() {
        this.url = "/services/rest/weather.php?lat=" + this.locator.lat + "&lng=" + this.locator.lng + "&datetime=" + this.datetime;
        this.callGet();
    }

    this.callGet = function() {
        var self = this;

        var xhr = new XMLHttpRequest();
        xhr.open('GET', this.url);
        xhr.onload = function() {
            if (xhr.status === 200) {
                //handle response here
                self.model = JSON.parse(xhr.responseText);
                self.displayResults();
            }
            else {
                console.log("ERROR");
            }

            submitter.disabled = false;
            utils.removeClass(submitter, "disabled");
            utils.removeClass(submitter, "waiting");

            self.initMap();
        };
        xhr.send();
    }

    this.initMap = function() {
        var searchedAddress = document.getElementById("search").value;

        if (searchedAddress != this.locator.search || firstLoad == true) {
            firstLoad = false;
            var place = {lat: parseFloat(this.locator.lat), lng: parseFloat(this.locator.lng)};
            var map = new google.maps.Map(document.getElementById('map'), {
                zoom: 14,
                center: place
            });
            var marker = new google.maps.Marker({
                position: place,
                map: map
            });
        }
    }

    this.displayResults = function() {
        var resultsWrapperDiv = document.getElementById("resultsWrapper");
        utils.removeClass(resultsWrapperDiv, "hidden");

        var resultsDiv = document.getElementById("results");
        var hoursArray = Object.values(this.model.hours);
        var formattedResult = hoursArray.map(this.hourTemplate).join('');
        resultsDiv.innerHTML = formattedResult;
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

var utils = new function() {
    this.hasClass = function(el, className) {
        if (el.classList) {
            return el.classList.contains(className);
        }
        else {
            return !!el.className.match(new RegExp('(\\s|^)' + className + '(\\s|$)'));
        }
    }
    
    this.addClass = function(el, className) {
        if (el.classList) {
            el.classList.add(className);
        }
        else if (!hasClass(el, className)) {
            el.className += " " + className;
        }
    }
    
    this.removeClass = function(el, className) {
        if (el.classList) {
            el.classList.remove(className);
        }
        else if (hasClass(el, className)) {
            var reg = new RegExp('(\\s|^)' + className + '(\\s|$)');
            el.className=el.className.replace(reg, ' ');
        }
    }
}

function processForm(e) {
    weatherman.datetime = document.querySelector('input[name="day"]:checked').value;
    weatherman.locator = locator;
    var searchedAddress = document.getElementById("search").value;

    submitter.disabled = true;
    utils.addClass(submitter, "disabled");
    utils.addClass(submitter, "waiting");

    if (searchedAddress == locator.search) {
        weatherman.getWeather();
    }
    else {
        locator.getGpsFromAddress(searchedAddress, true);
    }

    e.preventDefault();
    return false;
}

function btnClick(e) {
    var clickedBtn = this;
    for(var i = 0; i < btns.length; i++) {
        var btn = btns[i];

        if(btn === clickedBtn) {
            btn.classList.add("active");
            btn.getElementsByTagName("input")[0].checked = true;
        }
        else {
            btn.classList.remove("active");
            btn.getElementsByTagName("input")[0].checked = false;
        }
    }
}

//global now ...
var btns = null;
var firstLoad = true;
var submitter = null;

window.onload = function () {
    locator.init();
    var form = document.getElementById("searchForm");
    submitter = document.getElementById("submitter");
    form.addEventListener("submit", processForm);

    btns = document.querySelectorAll(".days > .btn");
    for(var i = 0; i < btns.length; i++) {
        var btn = btns[i];
        btn.addEventListener("click", btnClick);
    }
};