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


    }
});

//展示页面信息
function newsdetail() {
    let showinfo = "";
    if (JSON.stringify(l_news_info) != "[]" && JSON.stringify(l_news_info) != "{}") {
        updateView(l_news_info.id, l_news_info.view); //如果返回的数据不为空，执行更新函数
        let view = parseInt(l_news_info.view) + 1; //阅读量

        showinfo = ` <section id="top" class="Hero">
            <div class="Hero__agent u-padding-top u-padding-bottom">
                <div class="container grid">
                    <div
                        class="grid__item palm--1-1 palm-wide--1-1 lap--5-8 lap-wide--5-8 lap-large--5-8 desk--6-12 desk-wide--6-12 desk-large--6-12 default--6-12 push--desk--1-12 push--desk-wide--1-12 push--desk-large--1-12">
                        <div class="grid__item palm--1-1 palm-wide--1-1 lap--11-12">
                            <div class="Hero__agent-address">${l_news_info.field.real_estate_broker_region}</div>
                            <h1 class="Hero__agent-name">${l_news_info.title}</h1>
                            <div class="agent__agent-title">${l_news_info.field.real_estate_broker_desc}</div>

                            <div class="Hero__cta palm--hide palm-wide--hide"><a href="#connect-with-me"
                                    class="btn btn--primary btn--white u-text-uppercase" id="button-send-me">Send
                                    message<i class="fa fa-long-arrow-right"></i></a></div>

                        </div>
                    </div>
                    <div
                        class="Hero__img-container grid__item palm--1-1 palm-wide--1-1 lap--1-1 pull--palm-1-4 lap--3-8 lap-wide--3-8 lap-large--3-8 desk--5-12 desk-wide--5-12 desk-large--5-12 default--5-12 push--desk--1-12 push--desk-wide--1-12 push--desk-large--1-12">
                        <div class="Hero__agent-img-fix"></div>
                        <div class="Hero__agent-image palm--1-1 lap--1-1 desk--7-8 lazy-img"><img
                                 src="${l_news_info.thumbnail}"
                                width="0" height="760" decoding="async" data-nimg="1" class="" loading="eager"
                                style="color:transparent;width:100%;height:100%;object-fit:contain"></div>

                    </div>
                </div>
            </div>
        </section>
        
       <!-- <section id="story" class="agent__contact u-bg-blueSir u-padding-section-80-bottom ">
            <div class="container grid u-color-white agent_content">
                <div class="grid__item   desk-large--7-12   push--desk-large--5-12">
                    <div
                        class="AgentPersonal__content grid__item lap--7-8   desk-large--10-12 agent__content--small">
                        <h2 class="agent__heading">Local Expertise. Global Connections.</h2>
                        <div class="agent__profile">
                            ${l_news_info.field.real_estate_broker_desc}
                        </div>
                    </div>
                    
                </div>
            </div>
        </section> -->
        
        <section class="GetInTouch u-bg-very-light-grey" id="connect-with-me">
            <div class="GetInTouch__container container grid">
                <div class="m-listing-contact-info agent">
                    <div class="m-listing-contact-info__agent  ">
                        <div class="m-listing-contact-info__agent-details">
                            <h2 class="m-listing-contact-info__agent-name">${l_news_info.title}</h2>
                            <div class="m-listing-contact-info__agent-designation">${l_news_info.keywords}</div>
                            <ul class="m-listing-contact-info__agent-phones" role="list">
                                <li class="m-listing-contact-info__agent-phone" role="listitem"><a
                                        href="tel:${l_news_info.field.phone}"><span>O: ${l_news_info.field.phone}</span></a></li>
                            </ul>
                            <div class="m-listing-contact-info__agent-email"><a
                                    href="mailto:${l_news_info.field.real_estate_broker_email}">${l_news_info.field.real_estate_broker_email}</a>
                            </div>
                             
                             
                            <div class="m-listing-contact-info__agent-office">
                                <div class="m-listing-contact-info__office">
                                    <div class="divider"></div>
                                    <div class="m-listing-contact-info__office-label">Office</div>
                                    <div class="m-listing-contact-info__office-name "><a></a></div>
                                    <div class="m-listing-contact-info__office-name  u-margin-top"><a
                                            href="javascript:;">${l_news_info.description}</a></div>
                                    <div class="m-listing-contact-info__office-address ">
                                        ${l_news_info.content}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="m-listing-contact-info__contact" id="listingContactInfoWrapper">
                        <div id="listingContactInfoForm" class="m-listing-contact-info__contact-anchor" role="form"
                            tabindex="-1" aria-label="Contact Agent Form"></div>
                        <h3 class="m-listing-contact-info__contact-title">Let's get in touch</h3>
                        <div class="m-listing-contact-info__contact-form">
                            <form id="GetInTouchForm">
                                <div class="grid">
                                    <div
                                        class="grid__item default--1-2 palm-wide--1-1 palm--1-1 lap-wide--1-1 lap--1-1 u-padding-right input-holder">
                                        <div class="input-container  "><label for="firstname"
                                                class="label-inactive">First Name</label><input class="o-input       "
                                                type="text" name="contacts" id="firstname" placeholder="First Name"
                                                aria-label="First Name" required="" value=""></div>
                                    </div>
                                    <div
                                        class="grid__item default--1-2 palm-wide--1-1 palm--1-1 lap-wide--1-1 lap--1-1 input-holder">
                                        <div class="input-container  "><label for="lastname" class="label-inactive">Last
                                                Name</label><input class="o-input       " type="text" name="lastname"
                                                id="lastname" placeholder="Last Name" aria-label="Last Name" required=""
                                                value=""></div>
                                    </div>
                                    <div
                                        class="grid__item default--1-2 palm-wide--1-1 palm--1-1 lap-wide--1-1 lap--1-1 u-padding-right input-holder">
                                        <div class="input-container  "><label for="email" class="label-inactive">Email
                                                Address</label><input class="o-input       " type="email" name="email"
                                                id="email" placeholder="Email Address" aria-label="Email Address"
                                                required="" value=""></div>
                                    </div>
                                    <div
                                        class="grid__item default--1-2 palm-wide--1-1 palm--1-1 lap-wide--1-1 lap--1-1 input-holder">
                                        <div class="input-container  "><label for="phone" class="label-inactive">Phone
                                                number (Optional)</label><input class="o-input       " type="text"
                                                name="phone" id="phone" placeholder="Phone number (Optional)"
                                                aria-label="Phone number (Optional)" pattern="[0-9]+" value=""></div>
                                    </div>
                                    <div class="grid__item default--1-1">
                                        <div class="input-container  input-container--textarea  "><label
                                                for="message">Message (Optional)</label><textarea class="o-textarea  "
                                                id="message" name="message" aria-label="Message (Optional)"
                                                placeholder="I'd like to discuss buying, selling, renting with you."></textarea>
                                        </div>
                                    </div>
                                    <div class="grid__item">
                                        <div class="captcha__message">
                                           
                                        </div>
                                    </div>
                                    <div class="button-submit"  id="submit" type="submit"><span>Send message</span><i class="fa fa-long-arrow-right"></i></div>
                                    <div class="disclaimer"> 
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>`
        setTimeout(() => {
            //表单隐私协议
            wf_news_detail(36).done(function (data) {
                $(".captcha__message").html(data.content);
            }); //文章详情

            wf_news_detail(37).done(function (data) {
                $(".disclaimer").html(data.content);
            }); //文章详情 
        }, 10);

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
