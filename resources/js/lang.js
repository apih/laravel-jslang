/**
 * JS Language Translator, lightly based on Laravel's Translator.
 *
 * It is not feature complete, but should be usable.
 */

(function (global, factory) {
    if (typeof define === 'function' && define.amd) {
        // AMD support.
        define(factory);
    } else if (typeof exports === 'object') {
        // NodeJS support.
        module.exports = factory();
    } else {
        // Browser global support.
        global.Lang = factory();
    }
}(this, (function () {
    'use strict';

    let locale = 'en';
    let messages = {};

    /**
     * Get the default locale being used.
     *
     * @return {string}
     */
    function getLocale() {
        return locale;
    }

    /**
     * Set the default locale.
     *
     * @param {string} newLocale
     */
    function setLocale(newLocale) {
        locale = newLocale;
    }

    /**
     * Set the language localization messages.
     *
     * @param {string} newMessages
     */
    function setMessages(newMessages) {
        messages = Object.assign(messages, newMessages);
    }

    /**
     * Determine if a translation exists.
     *
     * @param {string} key
     * @return {boolean}
     */
    function has(key) {
        return get(key) !== key;
    }

    /**
     * Get the translation for the given key.
     *
     * @param {string} key
     * @param {object} replace
     * @return {string}
     */
    function get(key, replace) {
        key = typeof key !== 'undefined' ? key : null;

        if (key === null) {
            return '';
        }

        let found = false;
        let message = '';

        if (key.indexOf('.') !== -1 && key.indexOf(' ') === -1) {
            message = key.split('.').reduce(function (obj, key) {
                return typeof obj === 'object' ? obj[key] : '';
            }, messages[locale].short);

            found = message ? true : false;
        }

        if (!found && messages[locale].hasOwnProperty('long')) {
            message = messages[locale].long.hasOwnProperty(key) ? messages[locale].long[key] : '';
            found = message ? true : false;
        }

        if (!found) {
            message = key;
        }

        return makeReplacements(message, replace);
    }

    /**
     * Get a translation according to an integer value.
     *
     * @param {string} key
     * @param {number|array} number
     * @param {object} replace
     * @return {string}
     */
    function choice(key, number, replace) {
        key = typeof key !== 'undefined' ? key : null;
        number = typeof number !== 'undefined' ? number : null;
        replace = typeof replace !== 'undefined' ? replace : {};

        if (key === null || number === null) {
            return '';
        }

        if (number instanceof Array) {
            number = number.length;
        }

        let message = chooseMessage(key, number);
        replace = Object.assign({ count: number }, replace);

        return makeReplacements(message, replace);
    }

    /**
     * Make the placeholder replacements on a message.
     *
     * @param {string} message
     * @param {object} replace
     * @return {string}
     */
    function makeReplacements(message, replace) {
        if (typeof replace === 'undefined') {
            return message;
        }

        let keys = Object.keys(replace);
        let sortedReplace = {};

        keys.sort(function (a, b) {
            return b.length - a.length;
        });

        for (let i = 0; i < keys.length; i++) {
            sortedReplace[keys[i]] = replace[keys[i]];
        }

        for (let [key, value] of Object.entries(sortedReplace)) {
            message = message.replace(new RegExp(':' + key, 'gi'), function (match) {
                match = match.slice(1);

                if (match === match.toLocaleUpperCase()) {
                    return value.toLocaleUpperCase();
                } else if (match === (match.charAt(0).toLocaleUpperCase() + match.slice(1))) {
                    return value.charAt(0).toLocaleUpperCase() + value.slice(1);
                }

                return value;
            });
        }

        return message;
    }

    /**
     * Choose the appropriate message based on the number's value.
     *
     * @param {string} key
     * @param {number} number
     * @return {string}
     */
    function chooseMessage(key, number) {
        let segments = get(key).split('|');

        if (segments.length === 1) {
            return segments[0];
        }

        let message = '';

        for (let i = 0; i < segments.length; i++) {
            let matches = segments[i].match(/^[\{\[]([^\[\]\{\}]*)[\}\]](.*)/s);

            if (matches === null || matches.length !== 3) {
                continue;
            }

            let condition = matches[1];
            let value = matches[2];

            if (condition.indexOf(',') !== -1) {
                let [from, to] = condition.split(',', 2);

                if (to === '*' && number >= from) {
                    message = value;
                } else if (from === '*' && number <= to) {
                    message = value;
                } else if (number >= from && number <= to) {
                    message = value;
                }
            } else {
                message = parseInt(condition, 10) === parseInt(number, 10) ? value : '';
            }

            if (message.length > 0) {
                break;
            }
        }

        message = message.length > 0 ? message.trim() : key;

        return message;
    }

    return {
        getLocale: getLocale,
        setLocale: setLocale,
        setMessages: setMessages,
        has: has,
        get: get,
        choice: choice,
    };
})));
