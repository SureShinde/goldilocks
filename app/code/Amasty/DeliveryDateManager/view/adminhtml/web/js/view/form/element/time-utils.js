define([], function () {
    'use strict';

    return {
        /**
         * @param {String} timeString
         * @returns {String}
         */
        normalizeTime: function (timeString) {
            var timeWithZeroes = this.timeHoursConverter(timeString);

            return this.convertTo24hFormat(timeWithZeroes);
        },

        /**
         * Convert time hours from '7:00' to '07:00'
         * @param {String} timeString
         * @returns {String}
         */
        timeHoursConverter: function (timeString) {
            var timeHours = timeString.split(':')[0],
                timeMinutes = timeString.split(':')[1];

            if (!timeHours || timeHours.length !== 1) {
                return timeString;
            }

            return '0' + timeHours + ':' + timeMinutes;
        },

        /**
         * @param {String} timeString - time as string (ex. '07:00 AM', not '7:00 AM'. Use timeHoursConverter)
         * @returns {String}
         */
        convertTo24hFormat: function (timeString) {
            const ampm = timeString.slice(-2),
                hours = Number(timeString.slice(0, 2));

            var time = timeString.slice(0, -2).trim();

            if (ampm.toUpperCase() === 'AM') {
                if (hours === 12) {
                    return time.replace(timeString.slice(0, 2), '00');
                }

                return time;
            }

            if (ampm.toUpperCase() === 'PM') {
                if (hours !== 12) {
                    return time.replace(timeString.slice(0, 2), String(hours + 12));
                }

                return time;
            }

            return timeString.trim();
        }
    };
});
