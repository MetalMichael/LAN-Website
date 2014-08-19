$(document).ready(function() {
    $('#errors').hide();
    $('#create-button').click(function() {
        $('#errors').hide();    
        var error = false;
        
        var fields = ['username', 'password', 'password2', 'email']; 
        for(var x in fields) {
            if($('#' + fields[x]).val() == "") {
                error = true;
                shake(fields[x]);
            }
        }
        
        if($('#password').val() !== $('#password2').val()) {
            $('#errors div p strong').html("Passwords do not match");
            $("#errors").show();
            error = true;
        }
        
        if(!error) {
            submit();
        }
        return false;
    });
});

function shake(field) {
    $('#' + field).css('border-color', 'red');
    
    $('#' + field).addClass('shaking');
    window.setTimeout("$('#" + field + "').removeClass('shaking');", 1000);
}

function submit() {
    //Overlay.loadingOverlay();
    $.post(
        UrlBuilder.buildUrl(false, "account", "createaccount"),
        { username: $('#username').val(), password: $('#password').val(), email: $('#email').val() },
        function (data) {
            if (data != null) {
                if(data.error) {
                    Overlay.openOverlay(true, data.error);
                    return;
                }
                Overlay.openOverlay(false, "Account Created, Redirecting", 1000);
                window.setTimeout("document.location = '" + UrlBuilder.buildUrl(false, "home", "index") + "';", 500);
            }
        },
        'json');
}