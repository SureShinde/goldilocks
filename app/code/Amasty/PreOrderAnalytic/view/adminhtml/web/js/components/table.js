/**
 * Table Component
 */
define([
    'jquery',
    'uiComponent',
    'ko',
    'underscore'
], function ($, Component, ko, _) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Amasty_PreOrderAnalytic/components/table/wrapper',
            templates: {
                header: 'Amasty_PreOrderAnalytic/components/table/header',
                body: 'Amasty_PreOrderAnalytic/components/table/body',
                row: 'Amasty_PreOrderAnalytic/components/table/row',
                column: 'Amasty_PreOrderAnalytic/components/table/columns/column',
                column_status: 'Amasty_PreOrderAnalytic/components/table/columns/status'
            }
        }
    });
});
