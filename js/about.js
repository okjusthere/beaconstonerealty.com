$(document).ready(function () {
    $(".Static-hero h1").html(menu(local_c_id, "sub_title"))
    $(".Static-hero .Static-hero__description").html(menu(local_c_id, "remarks"))

    //视频
    wf_news_detail(13).done(function (data) {
        $(".Static-hero .video").html(data.content);
        if ($(".Static-hero .video video")&&$(".Static-hero .video video").length > 0) {
            $(".Static-hero .video video")[0].autoplay = 'true'
            $(".Static-hero .video video")[0].muted = 'false'
            $(".Static-hero .video video").get(0).play()
        }


    }); //文章详情

    wf_news_list(2, -1, 2).done(function (html) {
        $(".StaticStats ul").html(html);
    })

    wf_news_detail(18).done(function (data) {
        $(".about01bg h1").html(data.title);
        $(".about01bg .content01").html(data.description);
    }); //文章详情

    wf_news_detail(19).done(function (data) {
        $(".about02bg .grid__item img").attr("src", data.thumbnail);
        $(".about02bg .StaticFeaturedImageText__title").html(data.title);
        $(".about02bg .StaticFeaturedImageText__description-container p").html(data.description);
    }); //文章详情

    $(".about03bg h1").html(news_class(3, "title"));
    $(".about03bg .desc01").html(news_class(3, "description"));

    wf_news_list(3, -1, 5).done(function (html) {
        $(".about04bg ").html(html);
    });  //文章列表

    $(".about05bg h2").html(news_class(4, "title"));
    wf_news_list(6, -1, 6).done(function (html) {
        $(".about06bg .StaticGrid__list").html(html);
        var mySwiper = new Swiper('.about06bg .swiper-container', {
            slidesPerView: 2,
            spaceBetween: 10,
            // centeredSlides: true,
            observer: true,
            observeParents: true,
            loop: true,
            //effect:'fade',
            speed: 1000,
            autoplay: {
                delay: 5000,
                disableOnInteraction: false,
            },
            navigation: {
                nextEl: '.about06bg .swiper-button-next',
                prevEl: '.about06bg .swiper-button-prev',
            },
            breakpoints: {
                999: {
                    slidesPerView: 1,
                    spaceBetween: 0,
                },
            },
            on: {
                init: function () {
                },
                slideChangeTransitionEnd: function () {
                }
            }
        });
    });  //文章列表

    wf_news_list(10, 2, 10).done(function (html) {
        $(".DiscoverMore ul").html(html);
    });  //文章列表
    // wf_news_detail(24).done(function (data) {
    //     $(".about07bg h6").html(data.title);
    //     $(".about07bg img").attr("src", data.thumbnail);
    // }); //文章详情

    // wf_news_detail(25).done(function (data) {
    //     $(".about08bg h6").html(data.title);
    //     $(".about08bg img").attr("src", data.thumbnail);
    // }); //文章详情


    // setURL();

});