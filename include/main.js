/* 
 * created by Alwin Ebermann (alwin@alwin.net.au)
 */

$(document).ready(function(){
    
    $('#regform').formValidation({
        framework: 'bootstrap',
        icon: {
            valid: 'glyphicon glyphicon-ok',
            invalid: 'glyphicon glyphicon-remove',
            validating: 'glyphicon glyphicon-refresh'
        },
        fields: {
            name: {
                validators: {
                    notEmpty: {
                        message: 'A name is required.'
                    },
                    stringLength: {
                        min: 6,
                        max: 100,
                        message: 'The name must be more than 6 and less than 100 characters long'
                    },
                    regexp: {
                        regexp: /^[a-zA-Z0-9]+$/,
                        message: 'Only letters and numbers please'
                    }
                }
            },
            description: {
                validators: {
                    stringLength: {
                        max: 200,
                        message: 'Not longer than 200 chars please'
                    }
                }
            },
            domain: {
                validators: {
                    notEmpty: {
                        message: 'Please provide a domain your users will be forwarded to after successful authenticating.'
                    },
                    regexp: {
                        regexp: /^(?!\-)(?:[a-zA-Z\d\-]{0,62}[a-zA-Z\d]\.){1,126}(?!\d+)[a-zA-Z\d]{1,63}$/,
                        message: 'Please insert the domain name only.'
                    }
                }
            }
        }
    });
    
    $('.forward').hide();
    var btn = document.getElementById("btn");
    var activation=document.getElementById("activation").textContent;
    btn.onclick = function() {
        var win = window.open("https://telegram.me/xauthbot?start="+activation);
        var counter=0;
        var timer = setInterval(function() {
            counter++;
            $.post("bot/act.php",{act: activation}, function(data) {
                if(data==="false") {
                    win.close();
                    $('#trouble').modal('hide');
                    clearInterval(timer);
                    $('.forward').fadeIn(2000);
                    $('p.xead').animate({
                        height: "toggle",
                        margin: "toggle",
                        padding: "toggle",
                        opacity: "toggle"
                    }, 2000, function(){
                        //nothing
                    });
                    setTimeout(function() {
                        window.location.href = $('.fward').attr('href');
                    }, 2000);
                }
            });
            if(counter===10) {
                console.log('test');
                $('#trouble').modal('show');
            }
            
        }, 3000);
    };
});