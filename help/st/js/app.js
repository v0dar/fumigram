! function (e) {
    "use strict";
    e("#vertical-menu-btn").on("click", function (t) {
        t.preventDefault(), e("body").toggleClass("sidebar-enable"), 992 <= e(window).width() ? e("body").toggleClass("vertical-collpsed") : e("body").removeClass("vertical-collpsed")
    }), 
    e(".right-side-nav a").on("click", function (t) {
        var a = e(this);
        e("html, body").stop().animate({
            scrollTop: e(a.attr("href")).offset().top - 94
        }, 1500, "easeInOutExpo"), t.preventDefault()
    })

}(jQuery);