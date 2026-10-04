
function fix_u_targets() {
    if (window.location.hostname === 'localhost') {
        // log to console
        console.log('dont load to_get() in localhost');
        // return;
    }
    var ele = $("[u-target]");

    ele.each(function () {
        var item = $(this);
        var target = item.attr("u-target");
        var lang = item.attr("u-lang");
        var qid = item.attr("u-qid");
        var rest_api = `https://www.wikidata.org/w/rest.php/wikibase/v1/entities/items/${qid}/sitelinks`
        jQuery.ajax({
            url: rest_api,
            // data: params,
            type: 'GET',
            success: function (data) {
                //---
                var lang_link = data[`${lang}wiki`] ? data[`${lang}wiki`].title : '';
                //---
                if (lang_link == target) {
                    console.log(qid, target, " == ", lang_link);
                    //---
                    var new_html = `<a class="fw-boldx" target="_blank" href="https://wikidata.org/wiki/${qid}">Same</a>`;
                    //---
                    // remove calss bg-info-subtle from parent
                    item.parent().removeClass("bg-info-subtle");
                    //---
                    item.replaceWith(new_html);
                } else {
                    console.log(qid, target, " != ", lang_link);
                }
            },
            error: function (data) {
                console.log(data);
            }
        });
    });
    //---
    return true;
};

// load to_get() when document is ready
$(document).ready(function () {
    fix_u_targets();
});
