require([
    'jquery',
    'jquery/ui',
    'jquery/validate',
    'mage/translate'
], function ($) {
    $(document).ready(function () {
        $('#import-blacklist').click(function (e) {
            //stop submitting the form to see the disabled button effect
            e.preventDefault();
            var file = document.getElementById('blacklist').value;
            var arr = file.split(".");
            var checkfile = arr[1];
            if (file.indexOf(".sql") > 0 || checkfile !== 'csv') {
                var noti = '<i style="clear: both;display: block;color: red;margin-left: 28px;" id="noti">You must upload file csv.</i>';
                var myElem = document.getElementById('noti');
                if (myElem === null) {
                    $('#blacklist').after(noti);
                }
                return false;
            } else {
                $('#form_import').submit();
            }
        });
    });
});
