// fix calendar format with only time enabled
define([
    'mage/utils/wrapper'
], function (wrapper) {
    'use strict';

    return function (misc) {
        misc.convertToMomentFormat = wrapper.wrap(misc.convertToMomentFormat, function (original, format) {
            var result = original(format);

            return result.trim();
        });

        return misc;
    };
});
