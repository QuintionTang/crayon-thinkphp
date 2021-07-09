(function ($) {
    'use strict'
    function App() {
        this._ui = new Ui();
    }
    function initSlimscroll() {
        $(".slimscroll").slimscroll({
            height: "auto",
            position: "right",
            size: "7px",
            color: "#ebf0f6",
            wheelStep: 5,
            opacity: 1,
            touchScrollStep: 50,
        });
    }
    function getFeatureId(url, key) {
        var feature_id = 0,
            _key = key;
        if (url != "") {
            url = url.replace(".shtml", "");
            var array_url = url.split("/"),
                index_number = _.findIndex(array_url, function (val) {
                    return val === _key;
                });
            if (index_number >= 0) {
                feature_id = array_url[index_number + 1];
            } else {
                feature_id = 0;
            }
        }
        return feature_id;
    }
    function initMainIconMenu() {
        var pageUrl = window.location.href.split(/[?#]/)[0];
        $(".navigation-menu a").each(function () {
            var _this_feature_id = getFeatureId(this.href, "feature_id"),
                _this_parent_feature_id = getFeatureId(
                    pageUrl,
                    "parent_feature_id"
                ),
                _page_feature_id = getFeatureId(pageUrl, "feature_id");
            if (
                (_this_feature_id > 0 &&
                    _this_parent_feature_id > 0 &&
                    _this_feature_id === _this_parent_feature_id) ||
                (_this_feature_id > 0 &&
                    _page_feature_id > 0 &&
                    _this_feature_id === _page_feature_id)
            ) {
                $(this).parent().addClass("active"); // add active to li of the current link
                $(this).parent().parent().parent().parent().addClass("show");
                $(this).parent().parent().addClass("show");
                $(this).parent().parent().parent().addClass("active"); // add active class to an anchor
                $(this).parent().parent().parent().parent().addClass("active");
                $(this)
                    .parent()
                    .parent()
                    .parent()
                    .parent()
                    .parent()
                    .addClass("in");
                $(this)
                    .parent()
                    .parent()
                    .parent()
                    .parent()
                    .parent()
                    .addClass("active"); // add active class to an anchor
            }
        });
    }
    
    function initTopbarMenu() {
        $(".navbar-toggle").on("click", function (event) {
            $(this).toggleClass("open");
            $("#navigation").slideToggle(400);
        });

        $(".navigation-menu>li").slice(-2).addClass("last-elements");

        $('.navigation-menu li.has-submenu a[href="javascript:;"]').on(
            "click",
            function (e) {
                if ($(window).width() < 992) {
                    e.preventDefault();
                    $(this)
                        .parent("li")
                        .toggleClass("open")
                        .find(".submenu:first")
                        .toggleClass("open");
                }
            }
        );
    }

    function initnavbarMenu() {
        $(".has-submenu").on("click", function (e) {
            e.preventDefault();
            $(this).addClass("active");
            $(this).siblings().removeClass("active");
            $(".submenu-tab").addClass("show");
            var targ = $(this).attr("href");
            $(targ).addClass("active");
            $(targ).siblings().removeClass("active");
        });
        $(".submenu li").on("click", function (e) {
            // e.preventDefault();
            $(this).addClass("active");
            $(this).siblings().removeClass("active");
            window.location.href = e.target.href;
        });
    }

    function init() {
        initMainIconMenu();
        initSlimscroll();
        initTopbarMenu();
        initnavbarMenu();
        Waves.init();
    }
    App.prototype.init = function (helper, options) {
        var _this = this;
        this._ui.init(helper, options);
    };
    init();
    $.App = new App();
    $.App.Constructor = App;
})(jQuery);
