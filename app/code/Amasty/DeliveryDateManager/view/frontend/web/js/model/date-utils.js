define([], function () {
    'use strict';

    return {
        /**
         * @param {Date} d
         * @returns {string} yyyy-mm-dd
         */
        toISODate: function (d) {
            return d.getFullYear() + '-' + ('0' + (d.getMonth() + 1)).slice(-2) + '-' + ('0' + d.getDate()).slice(-2);
        },

        /**
         * Compare days, without hours
         *
         * @param {Date} dateA
         * @param {Date} dateB
         * @returns {boolean}
         */
        isDayEquals: function (dateA, dateB) {
            return dateA.getDate() === dateB.getDate()
                && dateA.getMonth() === dateB.getMonth()
                && dateA.getFullYear() === dateB.getFullYear();
        },

        /**
         * Returns 0 of date are equal
         * Return 1 if dateA is bigger
         * Return -1 if dateA is lower
         *
         * @param {Date} dateA
         * @param {Date} dateB
         * @returns {Number}
         */
        compareYMD: function (dateA, dateB) {
            if (this.isDayEquals(dateA, dateB)) {
                return 0;
            }

            if (dateA.getFullYear() > dateB.getFullYear()) {
                return 1;
            }

            if (dateA.getFullYear() === dateB.getFullYear()) {
                if (dateA.getMonth() > dateB.getMonth()) {
                    return 1;
                }

                if (dateA.getMonth() === dateB.getMonth() && dateA.getDate() > dateB.getDate()) {
                    return 1;
                }
            }

            return -1;
        }
    };
});
