$(document).ready(function () {
    $(".Static-hero h1").html(menu(local_c_id, "sub_title"))
    $(".Static-hero .Static-hero__description").html(menu(local_c_id, "remarks"))

    wf_news_detail(39).done(function (data) {
        $(".Static-hero .Media__video").html(data.content);
        if ($(".Static-hero .Media__video video") && $(".Static-hero .Media__video video").length > 0) {
            $(".Static-hero .Media__video video")[0].autoplay = 'true'
            $(".Static-hero .Media__video video")[0].muted = 'false'
            $(".Static-hero .Media__video video").get(0).play()
        }

    }); //文章详情

    wf_news_detail(40).done(function (data) {
        $(".joinUs02bg .left h1").html(data.title);
        $(".joinUs02bg .left .content01").html(data.content);
    }); //文章详情

    wf_news_detail(41).done(function (data) {
        $(".joinUs02bg .right").html(data.content);
    }); //文章详情

    wf_news_detail(42).done(function (data) {
        $(".joinUs03bg .StaticStackableImage__image").attr('src', data.thumbnail);
        $(".joinUs03bg h3").html(data.title);
        $(".joinUs03bg .content").html(data.content);
    }); //文章详情

    $(".StaticCarousel h2").html(news_class(7, "title"));

    wf_news_list(7, -1, 7).done(function (html) {
        $("#certify .swiper-wrapper").html(html);
        certifySwiper = new Swiper('#certify .swiper-container', {
            watchSlidesProgress: true,
            slidesPerView: 'auto',
            centeredSlides: true,
            loop: true,
            loopedSlides: 5,
            // autoplay: true,
            navigation: {
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev',
            },
            pagination: {
                el: '.swiper-pagination',
                //clickable :true,
            },
            breakpoints: {
                999: {
                    loopedSlides: 3,
                },
            },
            on: {
                progress: function (progress) {

                    for (i = 0; i < this.slides.length; i++) {

                        var slide = this.slides.eq(i);

                        var slideProgress = this.slides[i].progress;
                        modify = 1;
                        if (Math.abs(slideProgress) > 1) {
                            modify = (Math.abs(slideProgress) - 1) * 0.3 + 1;
                        }
                        translate = slideProgress * modify * 260 + 'px';
                        scale = 1 - Math.abs(slideProgress) / 5;
                        zIndex = 999 - Math.abs(Math.round(10 * slideProgress));
                        slide.transform('translateX(' + translate + ') scale(' + scale + ')');
                        slide.css('zIndex', zIndex);
                        slide.css('opacity', 1);
                        if (Math.abs(slideProgress) > 3) {
                            slide.css('opacity', 0);

                        }
                    }
                },
                setTransition: function (transition) {
                    for (var i = 0; i < this.slides.length; i++) {
                        var slide = this.slides.eq(i)
                        slide.transition(transition);
                    }

                },
                slideChangeTransitionEnd: function () {
                    swiperAnimate(this); //每个slide切换结束时运行当前slide动画
                    // console.log(this.activeIndex);
                    this.slides.find('.container').css('opacity', 0);
                    this.slides.eq(this.activeIndex).find('.container').css('opacity', 1);

                    /*this.slides.eq(this.activeIndex).find('.ani').removeClass('ani');//动画只展示一次*/
                }
            }

        })
    });  //文章列表

    wf_news_list(8, -1, 8).done(function (html) {
        $(".joinUs05bg ul").html(html);
    })




})