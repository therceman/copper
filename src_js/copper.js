// source: https://tc39.github.io/ecma262/#sec-array.prototype.find
if (!Array.prototype.find) {
    Object.defineProperty(Array.prototype, 'find', {
        value: function (predicate) {
            if (this == null) {
                throw TypeError('"this" is null or not defined');
            }
            var o = Object(this);
            var len = o.length >>> 0;
            if (typeof predicate !== 'function') {
                throw TypeError('predicate must be a function');
            }
            var thisArg = arguments[1];
            var k = 0;
            while (k < len) {
                var kValue = o[k];
                if (predicate.call(thisArg, kValue, k, o)) {
                    return kValue;
                }
                k++;
            }
            return undefined;
        },
        configurable: true,
        writable: true
    });
}

// ----------------------------------------------------

let copper = {};

// --------------------------- stringHandler ---------------------------

function StringHandler() {
}

/**
 * Return a formatted string (PHP sprintf format)
 * -----
 * <code>
 * sprintf('hello %s, where is your %s?', ['John', 'hat'])
 * </code>
 *
 * Output: Hello John, where is your hat
 *
 * If more complex solution is needed, you can check: https://github.com/alexei/sprintf.js
 * @param str
 * @param args
 * @returns {string|undefined}
 */
StringHandler.prototype.sprintf = function (str, args) {
    const regex_str = 's';
    const regex_int = 'd, u, c, o, x, X, b';
    const regex_float = 'e, E, f, F, g, G, h, H';

    const full_regex_str = regex_str + ', ' + regex_int + ', ' + regex_float;
    const full_regex = '%' + full_regex_str.split(', ').join('|%');
    const regex = new RegExp(full_regex);

    const _r = function (p, c) {
        return p.replace(regex, c);
    }

    args = (typeof args === 'undefined') ? [] : args;

    return args.reduce(_r, str);
}

/**
 * Transform first character to Uppercase
 *
 * @param {string} str
 * @returns {string}
 */
StringHandler.prototype.ucfirst = function (str) {
    return str.charAt(0).toUpperCase() + str.slice(1);
}

/**
 *
 * @param {string} str
 * @param {string} delimiter
 * @returns {array}
 */
StringHandler.prototype.split = function (str, delimiter) {
    return str.split(delimiter);
}

copper.stringHandler = new StringHandler();

// --------------------------- arrayHandler ---------------------------

function VarHandler() {

}

VarHandler.prototype.isArray = function (array) {
    return Array.isArray(array);
}

VarHandler.prototype.isObject = function (object) {
    return (typeof object === 'object' &&
        !Array.isArray(object) &&
        object !== null)
}

VarHandler.prototype.objectToArray = function (object) {
    return Object.values(object);
}

VarHandler.prototype.isDefined = function (variable) {
    return (typeof variable !== 'undefined')
}

VarHandler.prototype.toString = function (variable, skipNull, skipBool) {
    if (this.isDefined(skipNull) === false)
        skipNull = false;

    if (this.isDefined(skipBool) === false)
        skipBool = false;

    if (variable === null)
        return skipNull ? '' : 'null';

    if (variable === false)
        return skipBool ? '' : 'false';

    if (variable === true)
        return skipBool ? '' : 'true';

    return variable + '';
}

VarHandler.prototype.isNotEmpty = function (variable) {
    return this.isEmpty(variable) === false;
}

VarHandler.prototype.isEmpty = function (variable) {
    if (this.isDefined(variable) === false)
        return true;

    if (variable === null)
        return true;

    if (this.toString(variable).trim() === '')
        return true;

    if (this.isArray(variable) && variable.length === 0)
        return true;

    if (this.isObject(variable) && Object.keys(variable).length === 0)
        return true;

    return false;
}

VarHandler.prototype.isFunction = function (variable) {
    return (typeof variable === 'function');
}

copper.varHandler = new VarHandler();

// --------------------------- arrayHandler ---------------------------

function ArrayHandler() {

}

ArrayHandler.prototype.merge = function (array1, array2) {
    return array1.concat(array2);
}

ArrayHandler.prototype.clone = function (array) {
    return JSON.parse(JSON.stringify(array));
}

ArrayHandler.prototype.toggle = function (array, value, strict = false) {
    array = this.clone(array);

    if (this.hasValue(array, value, strict))
        array = this.delete(array, value)
    else
        array.push(value);

    return array;
}

ArrayHandler.prototype.delete = function (array, value, strict = false) {
    let self = this;

    let value_list = self.isArray(value) ? value : [value];

    let new_array = [];

    array.forEach(function (value) {
        if (self.hasValue(value_list, value, strict) === false)
            new_array.push(value);
    });

    return new_array;
}

ArrayHandler.prototype.hasValue = function (array, value, strict = false) {
    let match;

    if (strict === false)
        match = (array.find(element => element == value) !== undefined);
    else
        match = (array.indexOf(value) !== -1);

    return match;
}

ArrayHandler.prototype.assocMatch = function (item, filter) {
    let self = this;

    let matched = true;

    Object.keys(filter).forEach(function (pairKey) {
        let pairValue = filter[pairKey];

        if (Array.isArray(pairValue) === false && item[pairKey] != pairValue)
            matched = false;
        else if (Array.isArray(pairValue) && self.hasValue(pairValue, item[pairKey]) === false)
            matched = false;
    });

    return matched;
}

ArrayHandler.prototype.assocDelete = function (array, filter) {
    let self = this;

    let newArray = [];

    array.forEach(function (item) {
        if (self.assocMatch(item, filter) === false)
            newArray.push(item);
    });

    return newArray;
}

ArrayHandler.prototype.lastValue = function (array) {
    return array[array.length - 1];
}

ArrayHandler.prototype.isArray = function (array) {
    return Array.isArray(array);
}

ArrayHandler.prototype.diff = function (arrayA, arrayB) {
    return arrayA.filter(function (i) {
        return arrayB.indexOf(i) < 0;
    });
};

copper.arrayHandler = new ArrayHandler();

// --------------------------- numberHandler ---------------------------

function NumberHandler() {
}

NumberHandler.prototype.isBetween = function (num, min, max, include = false) {
    if (include)
        return (num >= min && num <= max);

    return (num > min && num < max);
}

NumberHandler.prototype.format = function (float, precision) {
    if (precision === void 0)
        precision = 2;

    return parseFloat(float).toFixed(precision);
}

NumberHandler.prototype.round = function (num, precision) {
    if (!("" + num).includes("e")) {
        return +(Math.round(num + "e+" + precision) + "e-" + precision);
    } else {
        let arr = ("" + num).split("e");
        let sig = "";
        if (+arr[1] + precision > 0) {
            sig = "+";
        }
        return +(Math.round(+arr[0] + "e" + sig + (+arr[1] + precision)) + "e-" + precision);
    }
}

copper.numberHandler = new NumberHandler();

// --------------------------- collectionHandler ---------------------------

function CollectionHandler() {
}

CollectionHandler.prototype.match = function (item, filter, strict = false) {
    let self = this;

    let matched = true;

    Object.keys(filter).forEach(function (key) {
        let pairKey = key;
        let pairValue = filter[key];

        if (copper.arrayHandler.isArray(pairValue) === false && item[pairKey] != pairValue)
            matched = false;
        else if (copper.arrayHandler.isArray(pairValue) && self.hasValue(pairValue, item[pairKey], strict) === false)
            matched = false;
    })

    return matched;
}


CollectionHandler.prototype.find = function (collection, filter) {
    let self = this;

    let list = [];

    if (copper.varHandler.isObject(collection))
        collection = copper.varHandler.objectToArray(collection);

    collection.forEach(function (item, k) {
        if (self.match(item, filter))
            list.push(item);
    });

    return list;
}

/**
 * @param {Object} collection
 * @param {Object} filter
 *
 * @returns {*|null}
 */
CollectionHandler.prototype.findFirst = function (collection, filter) {
    let list = this.find(collection, filter);

    return (list.length > 0) ? list[0] : null
}

/**
 * @param {Object} collection
 * @param id
 * @returns {*|null}
 */
CollectionHandler.prototype.findById = function (collection, id) {
    return this.findFirst(collection, {"id": id})
}

copper.collectionHandler = new CollectionHandler();

// ------------------------------------------------------------------------

function CookiesHandler() {

}

CookiesHandler.prototype.set = function (name, value, days) {
    let d = new Date();
    d.setTime(d.getTime() + (days * 24 * 60 * 60 * 1000));
    let expires = "expires=" + d.toUTCString();
    document.cookie = name + "=" + value + ";" + expires + ";path=/";
}

CookiesHandler.prototype.get = function (name) {
    let decodedCookie = decodeURIComponent(document.cookie);
    let ca = decodedCookie.split(';');

    name = name + "=";

    for (let i = 0; i < ca.length; i++) {
        let c = ca[i];
        while (c.charAt(0) === ' ') {
            c = c.substring(1);
        }
        if (c.indexOf(name) === 0) {
            return c.substring(name.length, c.length);
        }
    }

    return "";
}

copper.cookiesHandler = new CookiesHandler();

// ------------------------------------------------------------------------

function RequestHandler() {
    let self = this;

    this.base_uri = '';
    this.headers = {};

    this.timeout = {
        get: 100,
        post: 500
    };

    let timeoutId = {
        get: -1,
        post: -1
    }

    function getRequest(url, callback, params) {
        let http = new XMLHttpRequest();

        url = url + ((params !== void 0) ? '?' + new URLSearchParams(params).toString() : '');

        let full_url = (url.substring(0, 4) === 'http') ? url : self.base_uri + url;

        http.open('GET', full_url, true);

        http.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        http.setRequestHeader('X-CSRF-TOKEN', window['__csrf_token']);

        Object.keys(self.headers).forEach(function (header) {
            http.setRequestHeader(header, self.headers[header]);
        });

        http.onreadystatechange = function () {
            if (http.readyState === 4 && http.status === 200 && typeof callback === 'function')
                callback(JSON.parse(http.responseText));
        }

        http.send();
    }

    function postRequest(url, data, callback, contentType, prepareData) {
        let http = new XMLHttpRequest();

        const FORM_CONTENT = 'application/x-www-form-urlencoded';

        contentType = contentType || FORM_CONTENT;

        let full_url = (url.substring(0, 4) === 'http') ? url : self.base_uri + url;

        http.open('POST', full_url, true);

        http.setRequestHeader('Content-type', contentType);
        http.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        http.setRequestHeader('X-CSRF-TOKEN', window['__csrf_token']);

        Object.keys(self.headers).forEach(function (header) {
            http.setRequestHeader(header, self.headers[header]);
        });

        http.onreadystatechange = function () {
            if (http.readyState === 4 && http.status === 200 && typeof callback === 'function')
                callback(JSON.parse(http.responseText));
        }

        if (typeof prepareData === 'function')
            data = prepareData(data);
        else
            data = new URLSearchParams(data).toString();

        http.send(data);
    }

    function throttle(methodWithUrl, callback, timeout) {
        methodWithUrl = methodWithUrl.split('?')[0];

        if (timeout === false || timeout === 0)
            return callback();

        if (timeoutId[methodWithUrl] === -1) {
            callback();

            timeoutId[methodWithUrl] = setTimeout(function () {
                timeoutId[methodWithUrl] = -1
            }, timeout);

            return timeoutId[methodWithUrl];
        }

        clearTimeout(timeoutId[methodWithUrl]);

        timeoutId[methodWithUrl] = setTimeout(function () {
            callback();
            timeoutId[methodWithUrl] = -1
        }, timeout);
    }

    this.get = function (url, callback, params, throttleTimeout = this.timeout.get) {
        throttle('get@' + url, function () {
            getRequest(url, callback, params);
        }, throttleTimeout)
    }

    this.post = function (url, data, callback, throttleTimeout = this.timeout.post, contentType, prepareData) {
        throttle('post@' + url, function () {
            postRequest(url, data, callback, contentType, prepareData);
        }, throttleTimeout)
    }

}

RequestHandler.prototype.postJSON = function (url, data, callback, throttleTimeout = this.timeout.post) {
    this.post(url, data, callback, throttleTimeout, 'application/json', function (data) {
        return JSON.stringify(data);
    });
}

RequestHandler.prototype.fileUpload = function (url, fileInput, onSuccess, onError, onProgress) {
    let self = this;

    if (fileInput.files.length === 0)
        return (typeof onError === 'function') ? onError('No file provided', 1) : false;

    const file = fileInput.files[0];

    let http = new XMLHttpRequest();

    let full_url = (url.substring(0, 4) === 'http') ? url : self.base_uri + url;

    http.open('POST', full_url, true);

    http.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    http.setRequestHeader('X-CSRF-TOKEN', window['__csrf_token']);

    Object.keys(self.headers).forEach(function (header) {
        http.setRequestHeader(header, self.headers[header]);
    });

    http.onreadystatechange = function () {
        if (http.readyState !== 4)
            return false;

        if (http.status === 200 && typeof onSuccess === 'function')
            onSuccess(JSON.parse(http.responseText));
        else if (typeof onError === 'function')
            onError(http.responseText, 2);
    }

    http.upload.addEventListener("progress", function (event) {
        if (typeof onProgress === "function" && event.lengthComputable)
            onProgress(event.total, event.loaded, Math.round(event.loaded / event.total * 100));
    }, false);

    const formData = new FormData();

    formData.append('file', file);

    http.send(formData);
}

RequestHandler.prototype.setBaseUri = function (uri) {
    this.base_uri = uri;
}

RequestHandler.prototype.addHeader = function (key, value) {
    this.headers[key] = value;
}

RequestHandler.prototype.addWebpSupportHeader = function (value) {
    if (value !== void 0)
        this.headers['X-WEBP'] = value;
}

copper.requestHandler = new RequestHandler();

// ---------- Url Handler -----------------

function UrlHandler() {
}

UrlHandler.prototype.queryString = function (params) {
    let queryStr = new URLSearchParams(params).toString();
    return (queryStr.trim() === '' || params === false || params === true || params === null) ? '' : '?' + queryStr;
}

UrlHandler.prototype.getQueryParameter = function (key, query = null) {
    if (query === null)
        query = document.location.search;

    const urlParams = new URLSearchParams(query);

    return urlParams.get(key);
}

copper.urlHandler = new UrlHandler();

// ---------- Browser Handler -----------------

function BrowserHandler() {
}

BrowserHandler.prototype.hasWebpSupport = function (callback, feature) {
    if (typeof feature === 'undefined')
        feature = 'lossy';

    const kTestImages = {
        lossy: "UklGRiIAAABXRUJQVlA4IBYAAAAwAQCdASoBAAEADsD+JaQAA3AAAAAA",
        lossless: "UklGRhoAAABXRUJQVlA4TA0AAAAvAAAAEAcQERGIiP4HAA==",
        alpha: "UklGRkoAAABXRUJQVlA4WAoAAAAQAAAAAAAAAAAAQUxQSAwAAAARBxAR/Q9ERP8DAABWUDggGAAAABQBAJ0BKgEAAQAAAP4AAA3AAP7mtQAAAA==",
        animation: "UklGRlIAAABXRUJQVlA4WAoAAAASAAAAAAAAAAAAQU5JTQYAAAD/////AABBTk1GJgAAAAAAAAAAAAAAAAAAAGQAAABWUDhMDQAAAC8AAAAQBxAREYiI/gcA"
    };

    const img = new Image();

    img.onload = function () {
        const result = (img.width > 0) && (img.height > 0);
        callback(result, feature);
    };

    img.onerror = function () {
        callback(false, feature);
    };

    img.src = "data:image/webp;base64," + kTestImages[feature];
}

copper.browserHandler = new BrowserHandler();

// ---------- Date Handler -----------------

function DateHandler() {
}

DateHandler.prototype.timestamp = function (offset = 0) {
    if (offset === 0)
        return (new Date()).getDate();

    let date = new Date();

    return date.setDate(date.getDate() + offset);
}

copper.dateHandler = new DateHandler();

// ---------- Key Handler -----------------

function EventHandler() {
}

EventHandler.prototype.keyboardKeys = {
    Enter: "Enter",
    Backspace: "Backspace",
    Tab: "Tab",
    Space: " ",
    ArrowLeft: "ArrowLeft",
    ArrowRight: "ArrowRight"
}

EventHandler.prototype.stop = function (event) {
    event.preventDefault();
    return false;
}

EventHandler.prototype.isKeyPressed = function (event, key, code, keyCode) {
    let keyPressed = false;

    if ("key" in event) {
        keyPressed = (event.key === key);
    } else if ("code" in event) {
        keyPressed = (event.code === code);
    } else {
        keyPressed = (event.keyCode === keyCode);
    }

    return keyPressed;
}

EventHandler.prototype.isKeyFromKeyListPressed = function (event, keyList) {
    let that = this;

    let allowedKeyPressed = false;

    keyList.forEach(function (key) {
        if (that.isKeyPressed(event, key))
            allowedKeyPressed = true;
    })

    return allowedKeyPressed;
}

EventHandler.prototype.isEnterKeyPressed = function (event) {
    return this.isKeyPressed(event, "Enter", "Enter", 13)
}

EventHandler.prototype.isUpKeyPressed = function (event) {
    return this.isKeyPressed(event, "ArrowUp", "ArrowUp", 38)
}

EventHandler.prototype.isDownKeyPressed = function (event) {
    return this.isKeyPressed(event, "ArrowDown", "ArrowDown", 40)
}

EventHandler.prototype.isDeleteKeyPressed = function (event) {
    return this.isKeyPressed(event, "Delete", "Delete", 46)
}

EventHandler.prototype.isBackspaceKeyPressed = function (event) {
    return this.isKeyPressed(event, "Backspace", "Backspace", 8)
}

EventHandler.prototype.isEscapeKeyPressed = function (event) {
    return this.isKeyPressed(event, "Escape", "Escape", 27)
}

EventHandler.prototype.isNumericKeyPressed = function (event, strict) {
    strict = (strict === void 0) ? true : strict;

    let allowedKeys = '1234567890';

    if (strict === false)
        allowedKeys += '.';

    allowedKeys = allowedKeys.split('');

    allowedKeys.push(this.keyboardKeys.Backspace);
    allowedKeys.push(this.keyboardKeys.ArrowLeft);
    allowedKeys.push(this.keyboardKeys.ArrowRight);

    return this.isKeyFromKeyListPressed(event, allowedKeys);
}

copper.eventHandler = new EventHandler();

window.copper = copper;