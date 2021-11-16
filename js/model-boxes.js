jQuery(document).ready(function () {
    const queryString = window.location.search;
    const urlParams = new URLSearchParams(queryString);
    const slug = urlParams.get('model');
    if (slug !== null) {
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
});

jQuery(document).ready(function () {
    jQuery('#calculate').click(function () {
        jQuery('.tank-sugest').hide();
        let power = (parseInt(jQuery('#area').val()) * parseInt(jQuery('#kw').val())) / 1000;
        let tank = 0;
        let water = 0;
        if (parseInt(jQuery("input[type='radio'][name='hot_water']:checked").val()) === 1) {
            tank = 1;
            water = (parseInt(jQuery('#water').val()) * 1000) / 30;
            if (water <= 200 && power < 8) {
                power += 2;
            } else if (water > 200 && power < 8) {
                power += 3;
            }

            tank = parseInt(jQuery("input[type='radio'][name='hot_water_tank']:checked").val());
        }
        console.log(power);
        console.log(tank);
        console.log(water);

        if (power > 16) {
            jQuery('.results').html(' 0 ');
        } else {
            jQuery.ajax({
                type: "post",
                dataType: "json",
                url: models_ajax_object.ajax_url,
                data: {
                    action: "get_models",
                    power: power,
                    tank: tank,
                },
                success: function (rez) {
                    //const result = JSON.parse(rez);
                    let html = '';
                    jQuery('.results-wrapper').show();
                    console.log(rez);
                    if (rez !== 0) {
                        rez.forEach(function (model) {
                            html += '<a href="' + model.link + '">' + model.title + '</a>';
                        });
                        jQuery('.results').html(html);
                    } else {
                        jQuery('.results').html(' 0 ');
                    }

                    if(tank === 0 && water <= 200){
                        jQuery('.tank-200').show();
                    }

                    if(tank === 0 && water > 200){
                        jQuery('.tank-300').show();
                    }

                }
            });
        }

    });

    jQuery("input[type='radio'][name='energy_class']").click(function () {
        let kw = jQuery(this).val();
        jQuery('#kw').val(kw);
    });

    jQuery("input[type='radio'][name='hot_water']").change(function(){
        if(parseInt(jQuery("input[type='radio'][name='hot_water']:checked").val()) === 1){
            jQuery('.calc-form-wrapper-bottom').show();
        }else{
            jQuery('.calc-form-wrapper-bottom').hide();
        }
    });

});


