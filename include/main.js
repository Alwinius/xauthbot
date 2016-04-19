/* 
 * created by Alwin Ebermann (alwin@alwin.net.au)
 */

$(document).ready(function(){
    $('.forward').hide();
    var btn = document.getElementById("btn");
    var activation=document.getElementById("activation").textContent;
    btn.onclick = function() {
        var win = window.open("https://telegram.me/xauthbot?start="+activation);
        var timer = setInterval(function() {
            $.post("bot/act.php",{act: activation}, function(data) {
                if(data==="false") {
                    win.close();
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
        }, 1000);
    };
});