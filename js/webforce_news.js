/*文章列表
参数displays：每页显示的文章个数
参数type：要展现的样式（默认有1，2，3三种样式，0代表自定义样式）若有多个样式，用大于3的数字定义，-1表示使用后台选择的样式
参数currentpage：当前页码
参数id：文章分类ID，要读取多个分类下的文章以数组的形式传入*/
function news_inner(displays, type, currentpage, id) {
    displays = displays || 10;
    type = type || -1;
    currentpage = currentpage || 1;
    id = id || 0;
    let newslist = ""; //最终返回给前端的内容

    if (id == 0 && local_id != "") {
        id = local_id;
    }
    if (type === -1) {
        if (id > 0) {
            type = news_class(id, 'show_type');
        } else {
            newsclass_l = news_class_info.length; //文章分类个数
            if (newsclass_l > 0) {
                newsclass_l = newsclass_l - 1;
                type = news_class_info[newsclass_l]['show_type'];
            }
        }
    }

    if (local_page != "") { //当前页码不为空时，给currentpage赋值
        currentpage = local_page;
    }
    let news = getInnerNews(id, displays, currentpage);

    if (JSON.stringify(news) != "[]" && JSON.stringify(news) != "{}") {
        if (news.data.length > 0) {
            // 动态调用 newsInnerShow + 数字
            if (typeof window[`newsInnerShow${type}`] === 'function') {
                newslist = window[`newsInnerShow${type}`](news.data);
            } else {
                newslist = newsInnerShow1(news.data);
            }

            let total = news.total; //信息总条数
            let pagesize = news.pagesize; //总共有多少页
            let current_page = news.currentpage; //当前页码
            if (pagesize > 1) {
                newslist += flippingPages(total, pagesize, current_page); //翻页
            }
        } else {
            newslist = "<p style='color: #d5d7de'>暂无记录</p>";
        }
    } else {
        newslist = "<p style='color: #d5d7de'>您没有访问该信息的权限</p>";
    }

    return newslist;
}

/*文章展示形式-自定义
参数newsAry：接口返回的文章相关数据*/
function newsInnerShow0(newsAry) {
    let newsshow = "<div class='inside_news0'><ul>";
    for (let i = 0; i < newsAry.length; i++) {
        let id = newsAry[i]["id"]; //文章ID
        let title = newsAry[i]["title"]; //文章标题
        let url = newsAry[i]["url"]; //链接地址
        let keywords = newsAry[i]["keywords"]; //关键词
        let description = newsAry[i]["description"]; //文章描述
        let thumbnail = newsAry[i]["thumbnail"]; //缩略图
        let enclosure = newsAry[i]["enclosure"]; //附件
        let photo_album = newsAry[i]["photo_album"]; //图片相册
        let add_time = formatTime(newsAry[i]["add_time"]); //文章发布日期
        let target = (url.indexOf("http") != -1) ? "' target='_blank" : ""; //打开方式

        newsshow += "<li class='wow fadeInUp' data-wow-delay='" + i * .05 + "s'><a href='" + url + target + "' title='" + title + "'>" +
            "<div class='nr_box'>" +
            "<div class='imgbg'>" +
            "<div class='img'><img src='" + thumbnail + "' alt='" + title + "'></div>" +
            "</div>" +
            "<div class='txtbg'>" +
            "<div class='txt'>" +
            "<div class='h2'>" + title + "</div>" +
            "<div class='h3'>" + description + "</div>" +
            "<div class='time'><i></i>" + add_time + "</div>" +
            "</div>" +
            "</div>" +
            "</div>" +
            "</a></li>";
    }
    newsshow += "</ul><div class='clear'></div></div>";

    return newsshow;
}

/*文章展示形式一
参数newsAry：接口返回的文章相关数据*/
function newsInnerShow1(newsAry) {
    let newsshow = "";
    for (let i = 0; i < newsAry.length; i++) {
        let id = newsAry[i]["id"]; //文章ID
        let title = newsAry[i]["title"]; //文章标题
        let url = newsAry[i]["url"]; //链接地址
        let keywords = newsAry[i]["keywords"]; //关键词
        let description = newsAry[i]["description"]; //文章描述
        let thumbnail = newsAry[i]["thumbnail"]; //缩略图
        let content = newsAry[i]["content"];
        let enclosure = newsAry[i]["enclosure"]; //附件
        let photo_album = newsAry[i]["photo_album"]; //图片相册
        let add_time = formatTime(newsAry[i]["add_time"]); //文章发布日期
        let target = (url.indexOf("http") != -1) ? "' target='_blank" : ""; //打开方式

        newsshow += ` <li class="grid-listing-item" role="listitem">
                                <div class="ndcard" 
                                    id="card-0" role="region"><a
                                        href="${url}"
                                        class="ndcard__image skeleton-v2 ">
                                        <div class="responsive-image-container lazy-img">
                                           <img
                                                src="${thumbnail}"
                                                style="position:absolute;height:100%;width:100%;left:0;top:0;right:0;bottom:0;color:transparent"> 
                                        </div>
                                        <div class="results-card__tags"></div>
                                    </a>
                                    <div class="ndcard__content">
                                        <div class="results-card__wrapper u-no-decoration u-cursor-pointer safari-only">
                                            <a
                                                href="${url}"><span
                                                    class="ndcard__eyebrow skeleton-v2 ">${keywords}</span>
                                                <h3 class="ndcard__title skeleton-v2 "><span>${title}</span></h3>
                                                <div class="ndcard__content-info"><span
                                                        class="ndcard__price skeleton-v2 ">Starting Price:${description}</span>
                                                    <div class="ndcard__availability skeleton-v2 ">${newsAry[i].field.total_residences}Total Residences<span class="ndcard__marketing skeleton-v2 ">${content}</span></div>
                                                </div>
                                            </a></div>
                                    </div>
                                </div>
                            </li>`
    }

    return newsshow;
}

// /*文章展示形式二
// 参数newsAry：接口返回的文章相关数据*/
// function newsInnerShow2(newsAry) {
//     let newsshow = "";
//     for (let i = 0; i < newsAry.length; i++) {
//         let id = newsAry[i]["id"]; //文章ID
//         let title = newsAry[i]["title"]; //文章标题
//         let url = newsAry[i]["url"]; //链接地址
//         let keywords = newsAry[i]["keywords"]; //关键词
//         let description = newsAry[i]["description"]; //文章描述
//         let thumbnail = newsAry[i]["thumbnail"]; //缩略图
//         let content = newsAry[i]["content"];
//         let phone = newsAry[i]["field"]["phone"];
//         let enclosure = newsAry[i]["enclosure"]; //附件
//         let photo_album = newsAry[i]["photo_album"]; //图片相册
//         let add_time = formatTime(newsAry[i]["add_time"]); //文章发布日期
//         let target = (url.indexOf("http") != -1) ? "' target='_blank" : ""; //打开方式

//         newsshow += `<div>
//                             <div class="m-agent-item-results__card"><a
//                                     href="${url}"
//                                     class="m-agent-item-results__card-photo skeleton-v2 ">
//                                     <div class="responsive-image-container "
//                                         style="width:100%;height:100%;background-color:transparent"> <img  src="${thumbnail}"
//                                             width="300" height="100" decoding="async" data-nimg="1" class=""
//                                             loading="lazy"
//                                             style="color:transparent;width:100%;height:100%;object-fit:contain">
//                                     </div>
//                                 </a>
//                                 <div class="m-agent-item-results__card-container skeleton-v2 ">
//                                     <div class="m-agent-item-results__card-details"><a
//                                             href="${url}"
//                                             class="m-agent-item-results__card-name u-color-sir-blue js-fitty-target u-cursor-pointer u-text-align-left">${title}</a>
//                                         <div
//                                             class="m-agent-item-results__card-title u-text-uppercase u-color-dark-grey">${keywords}</div>
//                                         <div class="m-agent-item-results__card-separator"></div>
//                                         <div class="m-agent-item-results__card-advertiser">${description}</div>
//                                         <div
//                                             class="m-agent-item-results__card-address-wrapper p2 u-color-dark-grey palm--hide">
//                                             <div
//                                                 class="m-agent-item-results__card-address m-agent-item-results__card-address--main">
//                                                 ${content}</div>
//                                         </div>
//                                     </div>
//                                     <div class="m-agent-item-results__card-contact-details">
//                                         <div
//                                             class="m-agent-item-results__card-contact-details-title u-text-uppercase u-color-dark-grey palm--hide">
//                                             Contact</div>
//                                         <div class="m-agent-item-results__card-contact-phones">
//                                             <div class="phones__wrapper"><a tabindex="0"
//                                                     href="tel:${phone}">O: ${phone}</a>
//                                             </div>
//                                         </div><a
//                                             href="${url}"
//                                             class="m-agent-item-results__card-contact-btn btn u-text-uppercase u-color-sir-blue palm--hide"><span>Send
//                                                 message</span><i class="fa fa-long-arrow-right"></i></a>
//                                     </div>
//                                 </div>
//                             </div>
//                         </div>`
//         if (i == (newsAry.length / 2-1)) {
//             newsshow += `<div class="m-agents-search__results-container--marketed con02">
//                                 <div class="m-custom-html-container">
//                                     <div class="MarketYourProperty">
//                                         <div class="MarketYourProperty__item  MarketYourProperty__item--image">
//                                             <div class="responsive-image-container o-smartimage"><img
//                                                     src=""
//                                                     width="560" height="118" decoding="async" data-nimg="1"
//                                                     class="MarketYourProperty__image" loading="eager"
//                                                     style="color:transparent;position:relative;object-fit:cover">
//                                             </div>
//                                         </div>
//                                         <div
//                                             class="MarketYourProperty__item  MarketYourProperty__item--content  u-text-center  u-bg-very-light-grey">
//                                             <div class="MarketYourProperty__eyebrow  e1  u-color-dark-grey title01"> </div>
//                                             <h2 class="MarketYourProperty__title"> </h2>
//                                             <p class="MarketYourProperty__description"> </p> <a href=" "
//                                                 class="MarketYourProperty__button  btn--secondary  u-color-sir-blue  u-small-typograhy"
//                                                 rel="0"> <span class="MarketYourProperty__button-text">SELL WITH
//                                                     US</span> <i
//                                                     class="fa fa-long-arrow-right"></i>
//                                             </a>
//                                         </div>
//                                     </div> 
//                                 </div>
//                             </div>`
//         }
//     }
//     // 异步加载文章列表
//     setTimeout(() => {
//         wf_news_detail(35).done(function (data) {
//             $(".realEstateBrokerCenter02bg .con02 img").attr("src", data.thumbnail);
//             $(".realEstateBrokerCenter02bg .con02 .title01").html(data.title);
//             $(".realEstateBrokerCenter02bg .con02 .MarketYourProperty__title").html(data.description);
//             $(".realEstateBrokerCenter02bg .con02 .MarketYourProperty__description").html(data.content);
//         }); //文章详情
//     }, 0);
//     return newsshow;
// }

/*文章展示形式三
参数newsAry：接口返回的文章相关数据*/
function newsInnerShow2(newsAry) {
    let newsshow = " ";
    for (let i = 0; i < newsAry.length; i++) {
        let id = newsAry[i]["id"]; //文章ID
        let title = newsAry[i]["title"]; //文章标题
        let url = newsAry[i]["url"]; //链接地址
        let keywords = newsAry[i]["keywords"]; //关键词
        let description = newsAry[i]["description"]; //文章描述
        let thumbnail = newsAry[i]["thumbnail"]; //缩略图
        let enclosure = newsAry[i]["enclosure"]; //附件
        let photo_album = newsAry[i]["photo_album"]; //图片相册
        let real_estate_broker_email = newsAry[i]["field"]["real_estate_broker_email"]; //经纪人邮箱
        let real_estate_broker_desc = newsAry[i]["field"]["real_estate_broker_desc"]; //经纪人简介
        let phone = newsAry[i]["field"]["phone"];
        let add_time = newsAry[i]["add_time"]; //文章发布日期
        let ym = formatTime(add_time, "Y") + "-" + formatTime(add_time, "M");
        let d = formatTime(add_time, "D");
        let target = (url.indexOf("http") != -1) ? "' target='_blank" : ""; //打开方式

        newsshow +=`<section id="top" class="Hero">
            <div class="Hero__agent u-padding-top u-padding-bottom">
                <div class="container grid">
                    <div class="grid__item palm--1-1 palm-wide--1-1 lap--5-8 lap-wide--5-8 lap-large--5-8 desk--6-12 desk-wide--6-12 desk-large--6-12 default--6-12 push--desk--1-12 push--desk-wide--1-12 push--desk-large--1-12">
                        <div class="grid__item palm--1-1 palm-wide--1-1 lap--11-12"> 
                            <h1 class="Hero__agent-name">${title}</h1>
                            <div class="agent__agent-title u-text-uppercase">${keywords}</div>
                            <div class="agent__agent-title u-text-uppercase">Phone:${phone}</div>
                            <div class="agent__agent-title u-text-uppercase">Email:${real_estate_broker_email}</div>
                            <div class="agent__agent-title ">${real_estate_broker_desc}</div>
                        </div>
                    </div>
                    <div class="Hero__img-container grid__item palm--1-1 palm-wide--1-1 lap--1-1 pull--palm-1-4 lap--3-8 lap-wide--3-8 lap-large--3-8 desk--5-12 desk-wide--5-12 desk-large--5-12 default--5-12 push--desk--1-12 push--desk-wide--1-12 push--desk-large--1-12">
                        <div class="Hero__agent-image palm--1-1 lap--1-1 desk--7-8 lazy-img"><img src="${thumbnail}" width="0" height="760" decoding="async" data-nimg="1" class="" loading="eager" style="color:transparent;width:100%;height:100%;object-fit:cover"></div>

                    </div>
                </div>
            </div>
        </section>`
    }

    return newsshow;
}

/*文章展示形式三
参数newsAry：接口返回的文章相关数据*/
function newsInnerShow3(newsAry) {
    let newsshow = "<div class='inside_news3'><ul>";
    for (let i = 0; i < newsAry.length; i++) {
        let id = newsAry[i]["id"]; //文章ID
        let title = newsAry[i]["title"]; //文章标题
        let url = newsAry[i]["url"]; //链接地址
        let keywords = newsAry[i]["keywords"]; //关键词
        let description = newsAry[i]["description"]; //文章描述
        let thumbnail = newsAry[i]["thumbnail"]; //缩略图
        let enclosure = newsAry[i]["enclosure"]; //附件
        let photo_album = newsAry[i]["photo_album"]; //图片相册
        let add_time = newsAry[i]["add_time"]; //文章发布日期
        let ym = formatTime(add_time, "Y") + "-" + formatTime(add_time, "M");
        let d = formatTime(add_time, "D");
        let target = (url.indexOf("http") != -1) ? "' target='_blank" : ""; //打开方式

        newsshow += "<li class='wow fadeInUp' data-wow-delay='" + i * .05 + "s'>" +
            "<div class='date'>" +
            "<strong>" + d + "</strong>" +
            "<span>" + ym + "</span>" +
            "</div>" +
            "<div class='txt'>" +
            "<div class='h2'><a href='" + url + target + "' title='" + title + "'>" + title + "</a></div>" +
            "<div class='h3'>" + description + "</div>" +
            "</div>" +
            "</li>";
    }
    newsshow += "</ul><div class='clear'></div></div>";

    return newsshow;
}

/*文章展示形式四（图片列表常用）
参数newsAry：接口返回的文章相关数据*/
function newsInnerShow4(newsAry) {
    let newsshow = "<div class='inside_picList1'><ul>";
    for (let i = 0; i < newsAry.length; i++) {
        let id = newsAry[i]["id"]; //文章ID
        let title = newsAry[i]["title"]; //文章标题
        let url = newsAry[i]["url"]; //链接地址
        let keywords = newsAry[i]["keywords"]; //关键词
        let description = newsAry[i]["description"]; //文章描述
        let thumbnail = newsAry[i]["thumbnail"]; //缩略图
        let enclosure = newsAry[i]["enclosure"]; //附件
        let photo_album = newsAry[i]["photo_album"]; //图片相册
        let add_time = newsAry[i]["add_time"]; //文章发布日期
        let ym = formatTime(add_time, "Y") + "-" + formatTime(add_time, "M");
        let d = formatTime(add_time, "D");
        let target = (url.indexOf("http") != -1) ? "' target='_blank" : ""; //打开方式

        newsshow += "<li class='wow fadeInUp' data-wow-delay='" + i * .05 + "s'><a href='" + url + target + "' title='" + title + "'>" +
            "<div class='nr_box'>" +
            "<div class='imgbg'>" +
            "<div class='img'><img src='" + thumbnail + "' alt='" + title + "'></div>" +
            "</div>" +
            "<div class='txtbg'>" +
            "<div class='txt'>" +
            "<div class='h2'>" + title + "</div>" +
            "<div class='h3'>" + description + "</div>" +
            "</div>" +
            "</div>" +
            "</div>" +
            "</a></li>";
    }
    newsshow += "</ul><div class='clear'></div></div>";

    return newsshow;
}

/*文章展示形式五（图片相册常用）
参数newsAry：接口返回的文章相关数据*/
function newsInnerShow5(newsAry) {
    let newsshow = "<div class='inside_photo1'><ul class='baguetteBox1'>";
    for (let i = 0; i < newsAry.length; i++) {
        let id = newsAry[i]["id"]; //文章ID
        let title = newsAry[i]["title"]; //文章标题
        let url = newsAry[i]["url"]; //链接地址
        let keywords = newsAry[i]["keywords"]; //关键词
        let description = newsAry[i]["description"]; //文章描述
        let thumbnail = newsAry[i]["thumbnail"]; //缩略图
        let enclosure = newsAry[i]["enclosure"]; //附件
        let photo_album = newsAry[i]["photo_album"]; //图片相册
        let add_time = newsAry[i]["add_time"]; //文章发布日期

        newsshow += "<li class='wow fadeInUp' data-wow-delay='" + i * .05 + "s'><a href='" + thumbnail + "' title='" + title + "'>" +
            "<div class='nr_box'>" +
            "<div class='imgbg'>" +
            "<div class='img'><img src='" + thumbnail + "' alt='" + title + "'></div>" +
            "<i></i>" +
            "</div>" +
            "<div class='txtbg'>" +
            "<div class='txt'>" +
            "<div class='h2'>" + title + "</div>" +
            "</div>" +
            "</div>" +
            "</div>" +
            "</a></li>";
    }
    newsshow += "</ul><div class='clear'></div></div>";
    newsshow += "<link rel='stylesheet' href='css/baguettebox.min.css'>";
    newsshow += "<script src='js/baguettebox.min.js'></script>";
    newsshow += "<script>baguetteBox.run('.baguetteBox1', {animation: 'fadeIn',});</script>";

    return newsshow;
}

/*文章展示形式6 下载
参数newsAry：接口返回的文章相关数据*/
function newsInnerShow6(newsAry) {
    let newsshow = "<div class='inside_download1'><ul>";
    for (let i = 0; i < newsAry.length; i++) {
        let id = newsAry[i]["id"]; //文章ID
        let title = newsAry[i]["title"]; //文章标题
        let url = newsAry[i]["url"]; //链接地址
        let keywords = newsAry[i]["keywords"]; //关键词
        let description = newsAry[i]["description"]; //文章描述
        let thumbnail = newsAry[i]["thumbnail"]; //缩略图
        let enclosure = newsAry[i]["enclosure"]; //附件
        let photo_album = newsAry[i]["photo_album"]; //图片相册
        let add_time = formatTime(newsAry[i]["add_time"]); //文章发布日期
        let target = (url.indexOf("http") != -1) ? "' target='_blank" : ""; //打开方式
        let enclosure_path = "";
        let enclosure_size = 0;
        if (enclosure.length > 0) {
            enclosure_path = enclosure[0]["path"];
            enclosure_size = enclosure[0]["file_size"];
        }

        newsshow += "<li class='wow fadeInUp' data-wow-delay='" + i * .05 + "s'><i class='fa fa-book'></i>" +
            "<div class='txt'>" +
            "<div class='h2'><a download='" + enclosure_path + "' href='" + enclosure_path + "' title='" + title + "'>" + title + "</a></div>" +
            "<div class='h3'><span>大小：" + enclosure_size + "</span><span>" + add_time + "</span></div>" +
            "</div>" +
            "<div class='down_btn'><a download='" + enclosure_path + "' href='" + enclosure_path + "' target='_blank'><i class='fa fa-download'></i>立即下载</a></div>" +
            "</li>";
    }
    newsshow += "</ul><div class='clear'></div></div>";

    return newsshow;
}

/*获取文章信息
参数id：文章分类ID（为空时，显示所有文章）
参数displays：每页显示文章的个数
参数currentpage：当前页码*/
function getInnerNews(id, displays, currentpage) {
    displays = displays || 10;
    currentpage = currentpage || 1;

    let info = []; //返回的数据

    let result = webforceAjax('inner_news.php', 'POST', { "id": id, "displays": displays, "currentpage": currentpage });
    info = result.code == 200 ? result.obj : [];

    return info;
}
