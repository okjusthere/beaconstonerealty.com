$(document).ready(function () {
    wf_news_detail(50).done(function (data) {
        $("#overview .Static-hero__info h1").html(data.title);
        $("#overview .Static-hero__info .Static-hero__description").html(data.description);
        $("#overview .Static-hero__media-container .Media").html(data.content);
        if ($("#overview .Static-hero__media-container .Media video") && $("#overview .Static-hero__media-container .Media video").length > 0) {
            $("#overview .Static-hero__media-container .Media video")[0].autoplay = 'true'
            $("#overview .Static-hero__media-container .Media video")[0].muted = 'false'
            $("#overview .Static-hero__media-container .Media video").get(0).play()
        }

    }); //文章详情

    wf_news_list(6, -1, 9).done(function (html) {
        $(".realEstateBrokerCenter02bg .con01").html(html);
    })

    wf_news_detail(51).done(function (data) {
        $(".StaticFeaturedImageText .Media").html(data.content);
        $(".StaticFeaturedImageText__bg h2").html(data.title);
        $(".StaticFeaturedImageText__bg .StaticFeaturedImageText__description-container p").html(data.description);
    }); //文章详情

    wf_news_detail(52).done(function (data) {
        $(".form1 h2").html(data.title);
        $(".form1 .StaticContactForm__description").html(data.description);
    }); //文章详情

    wf_news_detail(53).done(function (data) {
        $(".form2 h2").html(data.title);
        $(".form2 .StaticContactForm__description").html(data.description);
    }); //文章详情

    wf_news_list(9, -1, 8).done(function (html) {
        $(".joinUs05bg ul").html(html);
    })

    // setURL();

});