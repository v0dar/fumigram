
$('body').on('click', '.proceed', function(e) {
    $(".requirements").slideUp();
    $(".installation").slideDown();
});

$('body').on('click', '.install', function(e) {
    e.preventDefault();
    if (!$(".installation").hasClass('processing')) {
        $(".installation").addClass('processing');
        $(".installation > form > .message").addClass('d-none');
        var request = 'process.php';
        var data = new FormData($('.installation > form')[0]);
        $.ajax({
            url: request,
            type: 'POST',
            dataType: 'text',
            data: data,
            async: true,
            cache: false,
            contentType: false,
            processData: false,
        }).done(function(data) {
             if (isJSON(data)) {
                data = $.parseJSON(data);
                if (data.status == 200) {
                    $(".installation").slideUp();
                    $(".installed").slideDown();
                    $(".description").text(data.message);
                    $("#ready").attr('href', data.siteurl);
                    $(".installer > .wrapper > .install-box").scrollTop(0);
                } else {
                    $(".installation > form > .message > div > span").text(data.message);
                    $(".installation > form > .message").removeClass('d-none');
                    $(".installer > .wrapper > .install-box").scrollTop(0);
                }
            } else {
                $(".installation > form > .message > div > span").text('An error has occured. Please check your details');
                $(".installation > form > .message").removeClass('d-none');
                $(".installer > .wrapper > .install-box").scrollTop(0);
             }
            $(".installation").removeClass('processing');
        }).fail(function(jqXHR, textStatus, errorThrown) {
            var message = '';
            if (jqXHR.status === 0) {
                message = 'Not connect.\n Verify Network.';
            } else if (jqXHR.status == 404) {
                message = 'Requested page not found. [404]';
            } else if (jqXHR.status == 500) {
                message = 'Internal Server Error [500].';
            } else if (exception === 'parsererror') {
                message = 'Requested JSON parse failed.';
            } else if (exception === 'timeout') {
                message = 'Time out error.';
            } else if (exception === 'abort') {
                message = 'Ajax request aborted.';
            } else {
                message = 'Uncaught Error.\n' + jqXHR.responseText;
            }
            console.log(errorThrown);
            $(".installation > form > .message > div > span").text(message);
            $(".installation > form > .message").removeClass('d-none');
            $(".installer > .wrapper > .install-box").scrollTop(0);
            $(".installation").removeClass('processing');
        });
    }
});

function isJSON (data) {
    var IS_JSON = true;
    try{var json = $.parseJSON(data);}
    catch(err) {IS_JSON = false;}
    return IS_JSON;
}