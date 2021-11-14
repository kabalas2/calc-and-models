jQuery(document).ready(function () {
    const queryString = window.location.search;
    const urlParams = new URLSearchParams(queryString);
    const slug = urlParams.get('model');
    if(slug !== null) {
        const activeBtn = jQuery('[data-slug="' + slug + '"]');
        activeBtn.addClass("active");
        const id = activeBtn.attr("data-modelid");
        jQuery("#info-box-" + id).show();
    }
    jQuery(".model-box").click(function () {
        jQuery(".model-info-box").hide();

        let id = jQuery(this).attr("data-modelid");

        if (jQuery(this).hasClass("active")) {
            jQuery(this).removeClass("active");
            jQuery("#info-box-" + id).hide();
            let newUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
            window.history.pushState({path: newUrl}, "", newUrl);
        } else {
            jQuery(".model-box").removeClass("active");
            jQuery(this).addClass("active");
            jQuery("#info-box-" + id).show();
            let slug = jQuery(this).attr("data-slug");
            let currentUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
            if (currentUrl.includes("?model=")) {
                currentUrl = currentUrl.substring(0, currentUrl.indexOf("?model="));
            }
            let newUrl = currentUrl + "?model=" + slug;
            window.history.pushState({path: newUrl}, "", newUrl);
        }
    });
})