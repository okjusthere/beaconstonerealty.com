$(document).ready(function () {
    wf_news_detail(11).done(function (data) {
        $(".Homepage .Homepage__search h1").html(data.title);
    }); //文章详情

    wf_news_detail(1).done(function (data) {
        $(".Homepage__description .AboutContent__title").html(data.title);
        $(".Homepage__description .content01").html(data.content);
        $(".Homepage__description .AboutContent__description-image img").attr("src", data.thumbnail);
    }); //文章详情

    wf_news_detail(2).done(function (data) {
        $(".AboutStory .AboutStory__title").html(data.title);
        $(".AboutStory .content01").html(data.content);
        $(".AboutStory__image  img").attr("src", data.thumbnail);
    }); //文章详情

    //视频
    wf_news_detail(3).done(function (data) {
        $(".Exclusive .Exclusive-right__title").html(data.title);
        $(".Exclusive .Exclusive-right__description").html(data.description);
        $(".Homepage-inspired .Media__video").html(data.content);
        $(".Homepage-inspired .Media  img").attr("src", data.thumbnail);
        if ($(".Homepage-inspired__video video") && $(".Homepage-inspired__video video").length > 0) {
            $(".Homepage-inspired__video video")[0].autoplay = 'true'
            $(".Homepage-inspired__video video")[0].muted = 'false'
            $(".Homepage-inspired__video video").get(0).play()
        }

    }); //文章详情

    // 房源
    $(".Exceptional-locations .Reside-Magazine__subtitle").html(news_class(1, "title"));
    $(".Exceptional-locations .Reside-Magazine__description").html(news_class(1, "description"));

    wf_news_list(5, -1, 1).done(function (html) {
        $(".Exceptional-locations ul").html(html);
        var mySwiper = new Swiper('.Exceptional-locations .swiper-container', {
            slidesPerView: 3,
            spaceBetween: 0,
            centeredSlides: true,
            observer: true,
            observeParents: true,
            loop: true,
            //effect:'fade',
            speed: 1000,
            autoplay: {
                delay: 5000,
                disableOnInteraction: false,
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

    // setURL();
    //视频
    wf_news_detail(39).done(function (data) {
        $(".banner").html(data.content);  //通栏图片

        if ($(".banner video") && $(".banner video").length > 0) {
            $(".banner video")[0].autoplay = 'true'
            $(".banner video")[0].muted = 'false'
            $(".banner video")[0].controls = false
            $(".banner video").get(0).play()
        }

    }); //文章详情

    // /*banner*/
    // var myBanner = new Swiper('.banner .swiper-container', {
    //     slidesPerView: 1,
    //     spaceBetween: 0,
    //     centeredSlides: true,
    //     observer: true,
    //     observeParents: true,
    //     loop: true,
    //     //effect:'fade',
    //     speed: 1000,
    //     autoplay: {
    //         delay: 5000,
    //         disableOnInteraction: false,
    //     },
    //     pagination: {
    //         el: '.banner .swiper-pagination',
    //         clickable: true,
    //     },
    //     navigation: {
    //         nextEl: '.banner .swiper-button-next',
    //         prevEl: '.banner .swiper-button-prev',
    //     },
    //     on: {
    //         init: function () {
    //             swiperAnimateCache(this); //隐藏动画元素
    //             this.emit('slideChangeTransitionEnd');//在初始化时触发一次slideChangeTransitionEnd事件
    //         },
    //         slideChangeTransitionEnd: function () {
    //             swiperAnimate(this); //每个slide切换结束时运行当前slide动画
    //             /*this.slides.eq(this.activeIndex).find('.ani').removeClass('ani');//动画只展示一次*/
    //         }
    //     }
    // });

    // wf_product_detail(1).done(function (data) {
    //     $(".p_detail").html(data.title);
    // }); //产品详情

    // wf_news_detail(1).done(function (data) {
    //     $(".news_d2").html(data.id);
    // }); //文章详情

    // wf_news_list(2).done(function (html) {
    //     $(".news2").html(html);
    // });  //文章列表

    // wf_news_list(2, 1, 1).done(function (html) {
    //     $(".news2").html(html);
    // });  //文章列表

    // $(".news-class").html(newsclass_list(1)); //文章分类列表

    // wf_product_list().done(function (html) {
    //     $(".product2").html(html);
    // }); //产品列表

    // wf_product_list(40, 1, 1).done(function (html) {
    //     $(".product3").html(html);
    // }); //产品列表

    // $(".product-class").html(proclass_list()); //产品分类列表
});