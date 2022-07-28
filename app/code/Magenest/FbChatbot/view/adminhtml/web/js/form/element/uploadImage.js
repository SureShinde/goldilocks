define([
    'Magento_Ui/js/form/element/image-uploader'
], function (imageUploader) {
    'use strict';

    const BOT_UPLOAD_IMAGE = "bot_upload_image";

    return imageUploader.extend({
        "defaults": {
            "imports": {
                'updateVisibility': '${ $.provider }:${ $.parentScope }.message_type',
            }
        },
        updateVisibility: function (value) {
            switch (value) {
                case 8:
                    this.show();
                    break;
                default:
                    this.hide();
                    break;
            }
        },
        getAllowedFileExtensionsInCommaDelimitedFormat: function () {
            var allowedExtensions = this.allowedExtensions.toUpperCase().split(' ');

            return allowedExtensions.join(', ');
        },
        onBeforeFileUpload: function (e, data) {
            data.paramName = BOT_UPLOAD_IMAGE;
            this._super();
        }
    });
});
