jQuery(document).ready(function () {
    jQuery(".model-box").click(function () {
        jQuery(".model-info-box").hide();
        jQuery(".model-box").removeClass("active");
        jQuery(this).addClass("active");
        let id = jQuery(this).attr("data-modelid");
        jQuery("#info-box-" + id).show();
        let slug = jQuery(this).attr("data-slug");
        let currentUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
        if (currentUrl.includes("?model=")) {
            currentUrl = currentUrl.substring(0, currentUrl.indexOf("?model="));
        }
        let newUrl = currentUrl + "?model=" + slug;
        window.history.pushState({path: newUrl}, "", newUrl);

    });
})