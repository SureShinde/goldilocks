/**
 * @package     Plumrocket_PrivateSale
 * @copyright   Copyright (c) 2020 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license/  End-user License Agreement
 */

define([
    'Magento_Ui/js/grid/columns/select',
], function (Column) {
    'use strict';

    return Column.extend({
        initialize: function () {
            return this._super()
                .processingUpdateTypesMap();
        },

        getStatusCode: function (row) {
            switch (parseInt(row['status'])) {
                case 0:
                    return 'status-disabled';
                case 1:
                    return 'status-active';
                case 2:
                    return 'status-ending-soon';
                case 3:
                    return 'status-upcoming';
                case 4 :
                    return 'status-ended';
                case 5 :
                    return 'status-coming-soon';
            }

            return '';
        },

        getStatusData: function (record) {
            return record[this.index] ? record[this.index] : [];
        },

        getLabel: function (value) {
            switch (parseInt(value.status)) {
                case 0:
                    return 'Disable';
                case 1:
                    return 'Active';
                case 2:
                    return 'Ending Soon';
                case 3:
                    return 'Upcoming';
                case 4:
                    return 'Ended';
                case 5:
                    return 'Coming Soon';
            }

            return '';
        },

        processingUpdateTypesMap: function () {
            var optionData;

            if (! this.updateTypesMap) {
                return;
            }

            this.updateTypesMap.forEach(function (data) {
                optionData = _.findWhere(this.options, {
                    value: data.value
                });

                data.label = optionData ? optionData.label : '';
            }, this);

            return this;
        },
    });
});
