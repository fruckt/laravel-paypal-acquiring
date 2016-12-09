/**
 * Created by fruckt on 21.12.2015.
 */

$('form').ajaxForm(function() {
    $.ajax({
        url: '{!! env("CERTIFICATE_SITE") !!}/api/certificate'
        , xhrFields: {
            withCredentials: true
        }
        , data: {
            phone: $('input[name=phone]').val()
            , email: $('input[name=email]').val()
            , currency: $('select[name=currency]').val()
            , face_value: $('select[name=face_value]').val()
        }
        , dataType: "json"
        , method: "post"
        , success: function (json) {
            console.log(json);
        }
        , error: function (error) {
        }
    });
});

