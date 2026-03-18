var l_news_info = getNewsDetail(local_id); //当前文章信息
//通过文章ID获取当前文章分类所绑定的栏目ID
var l_c_id = news_class(l_news_info.classid[0], "c_id");
if (l_c_id.length > 0) local_c_id = l_c_id[0];

$(document).ready(function () {
    //内页通用调用--开始
    $(".inbannerbg").html(banner_inner(5)); //内页通栏
    $(".weizhi").html(navigation()); //面包屑导航
    $(".m1_side_a").html(menu_inner()); //内页导航
    //内页通用调用--结束

    if (page_name === "downloaddetail") {
        //下载详情页
        $(".content").html(newsdetail());
    } else if (page_name === "picdetail") {
        //图片列表详情页
        $(".content").html(newsdetail());
    } else {
        //newsdetail详情页（文章默认详情页）
        $(".m1_side_b .list").html(news_list(1, 10, 100));
        $(".m1_side_b .list ul li").hover(function () {
            $(this).addClass("active");
            $(this).siblings("li").removeClass("active");
        });
        $(".m1_side_c").html(inner_contact());

        //左侧联系方式显示/隐藏
        $(".inLt_contact_close").click(function () {
            $(".m1_side_c").addClass("hiden").removeClass("shown");
        });
        $(".inLt_contact_open").click(function () {
            $(".m1_side_c").addClass("shown").removeClass("hiden");
        });

        $(".content").html(newsdetail());

        let html1 = ``;
        let photoAry = l_news_info.photo_album;
        for (let i = 0; i < photoAry.length; i++) {
            html1 += `<div class="swiper-slide"><img src="${photoAry[i]}" alt="" /></div>`;
        }
        $(".propertyCenterDetail01bg .swiper-wrapper").html(html1);
        var propertyCenterDetail01bg = new Swiper('.propertyCenterDetail01bg .swiper-container', {
            slidesPerView: 1,
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
            navigation: {
                nextEl: '.propertyCenterDetail01bg .swiper-button-next',
                prevEl: '.propertyCenterDetail01bg .swiper-button-prev',
            },
            on: {
                init: function () {
                },
                slideChangeTransitionEnd: function () {
                }
            }
        });

        $(".propertyCenterDetail02bg h2").html(l_news_info.title)
        $(".propertyCenterDetail02bg .des01").html(l_news_info.field.house_introduction)
        $(" .development_details").html(l_news_info.field.development_details)
        $(".property-details-accordion-title").click(function () {
            $(".property-details-accordion-content").slideToggle(500);
        })

        // 房产经纪人
        wf_news_detail(l_news_info.field.real_estate_agent_id).done(function (data) {
            $(".listing-details-hero img").attr("src", data.thumbnail);
            $(".listing-details-hero .m-contact-agents-widget__info-name").html(data.title);

            $("#agentcontact-anchor .m-listing-contact-info__agent-photo img").attr("src", data.thumbnail);
            $("#agentcontact-anchor .m-listing-contact-info__agent-details .m-listing-contact-info__agent-name a").html(data.title);
            $("#agentcontact-anchor .m-listing-contact-info__agent-details .m-listing-contact-info__agent-name a").attr("href", "/realEstateBrokerDetail/" + data.id);
            $("#agentcontact-anchor .m-listing-contact-info__agent-details .m-listing-contact-info__agent-link").attr("href", "/realEstateBrokerDetail/" + data.id);
            $("#agentcontact-anchor .m-listing-contact-info__agent-details .m-listing-contact-info__agent-phones a").attr("href", "tel:" + data.field.phone);
            $("#agentcontact-anchor .m-listing-contact-info__agent-details .m-listing-contact-info__agent-phones a span").html("O:" + data.field.phone);
            $("#agentcontact-anchor .m-listing-contact-info__agent-details .m-listing-contact-info__office-name font").html(data.description);
            $("#agentcontact-anchor .m-listing-contact-info__agent-details .m-listing-contact-info__office-address").html(data.content);

        }); //文章详情

        //表单隐私协议
        wf_news_detail(36).done(function (data) {
            $(".captcha__message").html(data.content);
        }); //文章详情

        wf_news_detail(37).done(function (data) {
            $(".disclaimer").html(data.content);
        }); //文章详情

        $(".m-contact-agents-widget__agent-cta").click(function () {
            window.location.href = '#agentcontact-anchor';
        })
    }
});

//展示页面信息
function newsdetail() {
    let showinfo = "";
    if (JSON.stringify(l_news_info) != "[]" && JSON.stringify(l_news_info) != "{}") {
        updateView(l_news_info.id, l_news_info.view); //如果返回的数据不为空，执行更新函数
        let view = parseInt(l_news_info.view) + 1; //阅读量

        showinfo = ` <div class="m-communty__wrapper">
            <div class="m-community-title">
                <div class="m-community-title__wrapper">
                    <h2 class="m-community-title__address">${l_news_info.keywords}</h2>
                    <h1 class="m-community-title__title">${l_news_info.title}</h1>
                </div>
            </div>
            <div class="m-community-listing-info">
                <div class="m-community-listing-info__wrapper">
                    <div class="m-community-listing-info-item">
                        <div class="m-community-listing-info-item__title">Total Residences</div>
                        <div class="m-community-listing-info-item__value">${l_news_info.field.total_residences}</div>
                    </div>
                    <div class="m-community-listing-info-item">
                        <div class="m-community-listing-info-item__title">starting at</div>
                        <div class="m-community-listing-info-item__value">
                            <div>
                                <div>${l_news_info.description}</div>
                            </div>
                        </div>
                    </div>
                    <div class="m-community-listing-info-item">
                        <div class="m-community-listing-info-item__title">development type</div>
                        <div class="m-community-listing-info-item__value">${l_news_info.field.development_type}</div>
                    </div>
                </div>
                <div class="m-community-listing-info__separator-container"><span
                        class="m-community-listing-info__separator"></span></div>
            </div>
        </div>`
    } else {
        showinfo = "<p style='color: #d5d7de'>您没有访问该信息的权限</p>";
    }

    return showinfo;
}

//更新文章阅读次数
function updateView(id, view) {
    if (id > 0) {
        webforceAjax("news_update_view.php", "POST", { "id": id, "value": view });
    }
}

/*获取文章相关信息
参数id：页面的ID*/
function getNewsDetail(id) {
    let info = webforceAjax('inner_newsdetail.php', 'POST', { id });
    info = info.code == 200 ? info.obj.data : '';
    return info;
}
