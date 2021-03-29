function ArrayHandler() {}

ArrayHandler.prototype.hasValue = function (array, value, strict = true) {
    let match;

    if (strict === false)
        match = (array.find(element => element == value) !== undefined);
    else
        match = (array.indexOf(value) !== -1);

    return match;
}

// ------------- Polyfill Section -------------

// https://tc39.github.io/ecma262/#sec-array.prototype.find
if (!Array.prototype.find) {
    Object.defineProperty(Array.prototype, 'find', {
        value: function(predicate) {
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