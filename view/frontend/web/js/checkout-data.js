/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true*/
/*global alert*/
/**
 * Checkout adapter for customer data storage
 */
define([
    'jquery',
    'Magento_Customer/js/customer-data'
], function ($, storage) {
    'use strict';

    var cacheKey = 'checkout-data';

    var getData = function () {
        return storage.get(cacheKey)();
    };

    var saveData = function (checkoutData) {
        storage.set(cacheKey, checkoutData);
    };

    return {
        getInputFieldUsernameValue: function () {
            var obj = getData();
            return (obj.inputFieldUsernameValue) ? obj.inputFieldUsernameValue : '';
        },

        setInputFieldUsernameValue: function (username) {
            var obj = getData();
            obj.inputFieldUsernameValue = username;
            saveData(obj);
        }
    }
});
