define([
    'jquery',
    'uiRegistry',
    'Magento_Ui/js/form/element/select',
    'wysiwygAdapter',
], function ($,uiRegistry, select, wysiwygAdapter) {
    'use strict';

    return select.extend({
        loadTemplate: function (){
            $.ajax({
                type: 'POST',
                url: this.loadTemplateUrl,
                data: {template_id: uiRegistry.get('index = popup_template_id').value()},
                showLoader: true,
                dataType: 'json',
                success: function (data) {
                    try {
                        wysiwygAdapter.setContent(data);
                    } catch (e) {}
                    uiRegistry.get('index = html_content').value(data);
                }
            }).fail(function(error) {
            });
        }
    });
});