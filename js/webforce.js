var url_host = ""; //数据接口地址
// var url_host = "http://www.beaconstonerealty.com"; //数据接口地址
var api_entrance = "/application/index/"; //接口api入口

// 页面类型
const newsClassPageAry = ["download", "news", "newsclass", "photo", "piclist"]; //文章、文章分类列表页
const newsPageAry = ["downloaddetail", "newsdetail", "picdetail"]; //文章详情页
const pagePageAry = ["page"]; //最终页
const productClassPageAry = ["proclass", "product"]; //产品、产品分类列表页
const productPageAry = ["prodetail"]; //产品详情页
const searchPageAry = ['search']; //搜索页

/*------------------------------全站调用 开始------------------------------*/
var global = getGlobal();
var web_control = global.web_control; //获取网站状态信息
var page_name = getParameter("route_page"); //获取当前页面名称
var web_info = global.web_info; //网站信息
var web_code = global.web_code; //获取嵌入代码
var pic_info = global.pic_info; //获取"图片管理"中所有图片的信息
var menu_info = global.menu_info; //导航栏信息
var news_class_info = global.news_class_info; //文章分类信息
var product_class_info = global.product_class_info; //产品分类信息
var customer_service = global.customer_service; //右侧客服信息
var links_info = global.links_info; //获取友情链接信息
var links_class_info = global.links_class_info; //获取友情链接分类信息

/*------------------------------全站调用 结束------------------------------*/

var local_path_name = window.location.pathname; //当前url的路径
var local_id = getParameter("id"); //当前页面ID
var local_c_id = getMenuID(local_path_name); //当前菜单栏ID-根据菜单url获取对应的菜单id
var local_page = getParameter("page"); //当前页码
var local_k = getParameter("k"); //搜索关键词
var local_t = getParameter("t"); //搜索类型{1/产品，2/文章}
var local_site = getParameter("site"); //是否更新网站地图
var local_attr = getParameter("attr"); //获取当前连接中的产品属性信息
if (!isEmpty(url_host)) {
    local_id = getUrlParameter("id");
    local_c_id = getUrlParameter("c_id");
}

webSwitch(); //开关
// have_permission(); //判断栏目有没有访问权限

/*判断栏目是否有访问权限*/
function have_permission() {
    if (local_c_id > 0) {
        let url = menu(local_c_id, "url");
        if (url == "nopermission" && page_name != "nopermission") {
            window.location.href = "/nopermission.html";
        }
    }
}

/*头部代码*/
function header() {
    let show_header = "";
    show_header += `   <div class="Nav-desktop__header-container" >
        <div class="Nav-desktop__header u-bg-blueSir Nav-desktop__header--main" ><a href="/"
                aria-label="Sotheby's home page" class="Nav-desktop__logo" 
                _mstaria-label="337766"><img class="Nav-desktop__logo-dynamic" 
                    
                   src="${pic(1, 'path')}" ></a>
            <div class="Nav-desktop__menu-container m11_head" >
                <div class='h_search_btn'></div> 
                <div class='h_search_box'><div class='h_search_close1'></div><div class='h_searchbg'><div class='h_search_close h_search_close2'></div><div class='h_search'><form id='infosearch2' onkeydown=\"kdSubmit('infosearch2')\"><select id='search_type2' hidden><option value='1' selected>产品</option><option value='2'>文章</option></select><input id='search_keywords2' type='text' placeholder='请输入关键词'><input id='search2' type='button' value='搜索'></form></div></div></div>

                ${menu_header()}
                <ul  class="Nav-desktop__menu">
      
                    <li  class="Nav-desktop__menu-item">
                        <div  class="Nav-desktop__menu-link"><a 
                                href="/sale/38"
                                class="Nav-desktop__button Nav-desktop__typography btn--secondary u-text-uppercase u-color-white btn--sir-blue">sell
                                with us</a></div>
                    </li>
                </ul>
                ${menu_mobile()}
            </div>
        </div>
    </div>`
    // show_header += "<div class='m11_head_height'></div>";
    // show_header += "<div class='m11_headbg'><div class='wapper1'><div class='m11_head'>";
    // show_header += logo(1);
    // show_header += "<div class='rt'>";
    // show_header += menu_header();
    // show_header += "<div class='h_search_btn'></div>";
    // show_header += "<div class='h_search_box'><div class='h_search_close1'></div><div class='h_searchbg'><div class='h_search_close h_search_close2'></div><div class='h_search'><form id='infosearch2' onkeydown=\"kdSubmit('infosearch2')\"><select id='search_type2' hidden><option value='1' selected>产品</option><option value='2'>文章</option></select><input id='search_keywords2' type='text' placeholder='请输入关键词'><input id='search2' type='button' value='搜索'></form></div></div></div>";
    // show_header += menu_mobile();
    // show_header += "</div>";
    // show_header += "</div></div></div>";

    return show_header;
}

/*底部代码*/
function footer() {
    let show_footer = "<a href='javascript:scroll(0,0);' class='go_top'><i class='fa fa-angle-up'></i></a>";
    // show_footer += menu_footer();
    // show_footer += foot_info();
    let myDate = new Date();

    show_footer += `<div id="FooterContainer" class="Footer__container-grid container grid">
        <div class="Footer__content">
            ${menu_footer()}
        </div>
        <div class="Footer__networks">
            <ul id="FooterLinks" class="Networks Footer__networks-item">

                <li class="Networks__item"><a href=" " target="_blank"
                        class="Networks__link u-font-mercury-italic">Instagram</a>
                </li>
                <li class="Networks__item"><a href=" " target="_blank"
                        class="Networks__link u-font-mercury-italic">TikTok</a></li>
                <li class="Networks__item"><a href=" " target="_blank"
                        class="Networks__link u-font-mercury-italic">LinkedIn</a>
                </li>
                <li class="Networks__item"><a href=" "
                        target="_blank" class="Networks__link u-font-mercury-italic">YouTube</a></li>
                <li class="Networks__item"><a href=" " target="_blank"
                        class="Networks__link u-font-mercury-italic">X</a></li>
                <li class="Networks__item"><a href=" " target="_blank"
                        class="Networks__link u-font-mercury-italic">Facebook</a>
                </li>
                <li class="Networks__item"><a href=" " target="_blank"
                        class="Networks__link u-font-mercury-italic">Pinterest</a></li>
                <li class="Networks__item"><a href=" " target="_blank" 
                        class="Networks__link u-font-mercury-italic">Red Note</a></li>
            </ul>
        </div>
        <div>
            <div class="Footer__copyright u-color-black p3">
               Copyright &copy; 2022-${myDate.getFullYear()}</span> ${web_info.company} Rights Reserved.
            </div>
            <p class="Footer__disclaimer2 p3 u-color-dark-grey ">

            </p>
        </div>
    </div>`
    wf_news_detail(10).done(function (data) {
        $(".footer .Footer__disclaimer2").html(data.content);
    }); //文章详情
    return show_footer;
}

/*网站LOGO
 *id：图片位置ID
 */
function logo(id) {
    let logo = "";
    let url = window.location.protocol;  //获取 URL 的协议部分
    url = url + "//" + window.location.host;  //获取 URL 的主机部分
    logo = "<div class='logo'><a href='" + url + "'><img src='" + pic(id, 'path') + "'></a></div>";
    return logo;
}

/*根据返回的值，生成菜单栏--pc端菜单栏
参数child：栏目二级数据带入循环用
参数level：菜单的层级数，1是一级，2是二级...*/
function menu_header(child, level) {
    child = child || [];
    level = level || 1;

    let columnlist;

    if (child.length > 0) {
        level = level + 1;
        columnlist = child;
    } else {
        columnlist = menu();
    }
    let ret_menu = "";
    if (level == 1) {
        ret_menu += "<div class='menu'>";
    }
    ret_menu += "<ul>";
    if (columnlist.length > 0) {
        for (let i = 0; i < columnlist.length; i++) {
            let c_id = columnlist[i]["id"];
            let title = columnlist[i]["title"];
            let sub_title = columnlist[i]["sub_title"];
            let url = columnlist[i]["url"];
            let type = columnlist[i]["type"];
            let link_id = columnlist[i]["link_id"];
            let children = columnlist[i]["children"];

            let tag = "";  //打开方式
            if (url.indexOf("http") != -1) {
                tag = " target='_blank'";
            }

            let strClass = ""; //有二级时显示class
            if (children.length > 0) {
                strClass = " menu_down";
            }
            let strHover = ""; //选中状态的class
            if (local_path_name == url) {
                strHover = " hover";
            } else if ((local_path_name == "/" || local_path_name == "") && (title == "首页" || title == "网站首页" || title == "Home")) {
                strHover = " hover";
            }

            ret_menu += "<li class='level_" + level + strClass + strHover + "'><a href='" + url + "' title='" + title + "'" + tag + ">" + title + "</a>";
            if (children.length > 0) {
                ret_menu += menu_header(children, level);
            }
            ret_menu += "</li>";
        }
    }
    ret_menu += "</ul>";
    if (level == 1) {
        ret_menu += "</div>";
    }

    return ret_menu;
}

/*根据返回的值，生成菜单栏--手机端菜单栏
参数child：栏目二级数据带入循环用
参数level：菜单的层级数，1是一级，2是二级...*/
function menu_mobile(child, level) {
    child = child || [];
    level = level || 1;

    let columnlist;

    if (child.length > 0) {
        level = level + 1;
        columnlist = child;
    } else {
        columnlist = menu();
    }
    let ret_menu = "";
    if (level == 1) {
        ret_menu += "<div class='nav_open'><i></i></div>" +
            "<div class='nav_box'>" +
            "<div class='nav_close nav_close1'></div>" +
            "<div class='navbg'>" +
            "<div class='nav'>" +
            "<div class='nav_close nav_close2'></div>" +
            "<div class='nav_list' id='nav_list'>"
    }
    ret_menu += "<ul>";
    if (columnlist.length > 0) {
        for (let i = 0; i < columnlist.length; i++) {
            let c_id = columnlist[i]["id"];
            let title = columnlist[i]["title"];
            let sub_title = columnlist[i]["sub_title"];
            let url = columnlist[i]["url"];
            let type = columnlist[i]["type"];
            let link_id = columnlist[i]["link_id"];
            let children = columnlist[i]["children"];

            let tag = "";  //打开方式
            if (url.indexOf("http") != -1) {
                tag = " target='_blank'";
            }

            let strArrow = ""; //当有二级的时候，显示向下的箭头
            if (children.length > 0) {
                strArrow = "<i></i>";
            }

            ret_menu += "<li><span><a href='" + url + "' title='" + title + "'" + tag + ">" + title + "</a>" + strArrow + "</span>";
            if (children.length > 0) {
                ret_menu += menu_mobile(children, level);
            }
            ret_menu += "</li>";
        }
    }
    ret_menu += "</ul>";
    if (level == 1) {
        ret_menu += "</div>" +
            "</div>" +
            "</div>" +
            "</div>";
    }
    return ret_menu;
}

/*通栏图片
参数id：图片位置ID
参数type：通栏图片展现形式*/
function banner(id, type) {
    let banner_show = "";
    let picAry = pic(id); //获取通栏图片信息

    if (picAry.length > 0) {
        banner_show = "<div class='swiper-container'>";
        banner_show += "<div class='swiper-wrapper'>";
        for (let i = 0; i < picAry.length; i++) {
            let name = picAry[i]["name"]; //图片名称
            let path = picAry[i]["path"]; //图片路径
            let url = picAry[i]["url"]; //链接地址
            let remarks = picAry[i]["remarks"]; //备注信息

            banner_show += "<div class='swiper-slide'>" +
                "<img src='" + path + "' alt='" + name + "'>" +
                "<div class='txtbg'>" +
                "<div class='txt'>" +
                "<div class='h2'>" + name + "</div>" +
                "<div class='h3'>" + remarks + "</div>" +
                "</div>" +
                "</div>" +
                "</div>";
        }
        banner_show += "</div>";
        banner_show += "<div class='swiper-pagination'></div>";
        banner_show += "<div class='swiper-button-white swiper-button-next'></div>";
        banner_show += "<div class='swiper-button-white swiper-button-prev'></div>";
        banner_show += "</div>";
    }
    return banner_show;
}

/*产品展示
参数id：产品分类ID（为空时，显示所有产品）
参数top：显示产品的个数，-1表示显示所有相关产品
参数type：产品列表展现形式*/
function product_list(id, top, type) {
    top = top || -1;
    type = type || 1;

    let prolist = "";
    let pro = getProductList(id, top);
    if (pro.length > 0) {
        // 动态调用 proShow + 数字
        if (typeof window[`proShow${type}`] === 'function') {
            prolist = window[`proShow${type}`](pro);
        } else {
            prolist = proShow1(pro);
        }
    }
    return prolist;
}

/*产品展示形式一
参数proAry：产品数据*/
function proShow1(proAry) {
    let proshow = "<div class='index_product1'><ul>";

    for (let i = 0; i < proAry.length; i++) {
        let id = proAry[i]["id"]; //产品ID
        let title = proAry[i]["title"]; //产品名
        let specifications = proAry[i]["specifications"]; //产品规格
        let origin = proAry[i]["origin"]; //产品产地
        let price = proAry[i]["price"]; //产品价格
        let keywords = proAry[i]["keywords"]; //关键词
        let description = proAry[i]["description"]; //产品描述
        let thumbnail = proAry[i]["thumbnail"]; //缩略图
        let photo_album = proAry[i]["photo_album"]; //图片相册
        let add_time = formatTime(proAry[i]["add_time"]); //产品发布日期
        let url = proAry[i]["url"]; //伪静态链接

        proshow += "<li><a href='" + url + "' title='" + title + "'>" +
            "<div class='nr_box'>" +
            "<div class='imgbg'>" +
            "<div class='img lazy-img' ><img src='" + thumbnail + "' alt='" + title + "'/></div>" +
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
    proshow += "</ul><div class='clear'></div></div>";

    return proshow;
}

/*产品分类展示
参数id：产品分类ID（为0时，显示所有产品分类）
参数top：显示产品分类的个数，-1表示显示所有相关产品分类
参数type：产品分类列表展现形式*/
function proclass_list(id, top, type) {
    id = id || 0;
    top = top || -1;
    type = type || 1;

    let proclasslist = "";
    let proclass = product_class(id, '', top);
    if (proclass.length > 0) {
        // 动态调用 proClassShow + 数字
        if (typeof window[`proClassShow${type}`] === 'function') {
            proclasslist = window[`proClassShow${type}`](proclass);
        } else {
            proclasslist = proClassShow1(proclass);
        }
    }
    return proclasslist;
}

/*产品分类展示形式一
参数proClassAry：产品分类数据*/
function proClassShow1(proClassAry) {
    let proclassshow = "<div class='index_proclass1'><ul>";

    for (let i = 0; i < proClassAry.length; i++) {
        let id = proClassAry[i]["id"]; //产品分类ID
        let title = proClassAry[i]["title"]; //产品分类标题
        let url = proClassAry[i]["url"]; //链接
        let description = proClassAry[i]["description"]; //产品分类描述
        let thumbnail = proClassAry[i]["thumbnail"]; //缩略图
        let banner = proClassAry[i]["banner"]; //通栏图片
        let add_time = formatTime(proClassAry[i]["add_time"]); //产品分类添加日期

        proclassshow += "<li><a href='" + url + "' title='" + title + "'>" + title + "</a></li>";
    }
    proclassshow += "</ul><div class='clear'></div></div>";

    return proclassshow;
}

/*文章展示
参数id：文章分类ID（为空时，显示所有文章）
参数top：显示文章的个数，-1表示显示所有相关文章
参数type：文章列表展现形式*/
function news_list(id, top, type) {
    top = top || -1;
    type = type || 1;

    let newslist = "";
    let news = getNewsList(id, top);
    if (!isEmpty(news)) {
        // 动态调用 newsShow + 数字
        if (typeof window[`newsShow${type}`] === 'function') {
            newslist = window[`newsShow${type}`](news);
        } else {
            newslist = newsShow1(news);
        }
    }
    return newslist;
}

/*文章展示形式一
参数newsAry：文章数据*/
function newsShow1(newsAry) {
    let newsshow = "<div class='swiper-container'><div class='swiper-wrapper'>";

    for (let i = 0; i < newsAry.length; i++) {
        let id = newsAry[i]["id"]; //文章ID
        let title = newsAry[i]["title"]; //文章标题
        let url = newsAry[i]["url"]; //链接地址
        let keywords = newsAry[i]["keywords"]; //关键词
        let description = newsAry[i]["description"]; //文章描述
        let thumbnail = newsAry[i]["thumbnail"]; //缩略图
        let enclosure = newsAry[i]["enclosure"]; //附件
        let house_introduction = newsAry[i]["field"]["house_introduction"]; //附件
        let photo_album = newsAry[i]["photo_album"]; //图片相册
        let add_time = formatTime(newsAry[i]["add_time"]); //文章发布日期

        newsshow += ` <div class="swiper-slide"><li class="Exceptional-locations__item"><a
                            href="${url}" target="_self"
                            class="Article-magazine u-text-center">
                            <div class="Article-magazine__container " ><img   src="${thumbnail}" ></div>
                            <div class="header5 Article-magazine__title u-color-sir-blue"> ${title} </div>
                            <div class="Article-magazine__text p2 u-color-dark-grey"> ${house_introduction} </div>
                        </a></li></div>`;
    }
    newsshow += "</div><div class='swiper-pagination'></div></div>";

    return newsshow;
}

/*文章展示形式二
参数newsAry：文章数据*/
function newsShow2(newsAry) {
    let newsshow = "";

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

        newsshow += `<li
                            class="grid__item StaticStats__list-item desk--3-12 lap--4-8 palm--1-1 StaticStats__list-item--4">
                            <div class="StaticStatsItem StaticStats__stat">
                                <h3 class="StaticStatsItem__eyebrow u-text-uppercase u-color-grey-dark">${title}
                                </h3>
                                <h4 class="StaticStatsItem__value n1 u-color-black">${keywords}</h4>
                                <p class="StaticStatsItem__label h6 u-color-black">${description}</p>
                            </div>
                        </li>`
    }

    return newsshow;
}

/*文章展示形式三
参数newsAry：文章数据*/
function newsShow3(newsAry) {
    let newsshow = "<div class='index_news3'><ul>";

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

        newsshow += "<li>" +
            "<div class='nr_box'>" +
            "<div class='date'>" +
            "<strong>" + d + "</strong>" +
            "<span>" + ym + "</span>" +
            "</div>" +
            "<div class='txt'>" +
            "<div class='h2'><a href='" + url + "' title='" + title + "'>" + title + "</a></div>" +
            "<div class='h3'>" + description + "</div>" +
            "</div>" +
            "</div>" +
            "</li>";
    }
    newsshow += "</ul><div class='clear'></div></div>";


    return newsshow;
}

/*文章展示形式四（图片列表常用）
参数newsAry：接口返回的文章相关数据*/
function newsShow4(newsAry) {
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
        let add_time = formatTime(newsAry[i]["add_time"]); //文章发布日期

        newsshow += "<li><a href='" + url + "' title='" + title + "'>" +
            "<div class='nr_box'>" +
            "<div class='imgbg'>" +
            "<div class='img lazy-img' ><img src='" + thumbnail + "' alt='" + title + "'></div>" +
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

/*文章展示形式四（图片列表常用）
参数newsAry：接口返回的文章相关数据*/
function newsShow5(newsAry) {
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

        newsshow += `<div class="StaticStackableImage__container container grid">
                        <div class="grid__item desk--5-11  lap--4-8 palm--1-1">
                            <div class="StaticStackableImage__top-image-container lazy-img" ><img
                                    class="StaticStackableImage__image"
                                    src="${thumbnail}" ></div>
                        </div>
                        <div
                            class="StaticStackableImage__top-text-container grid__item push--desk--1-12 desk--5-12 lap--4-8 palm--1-1">
                            <h2 class="StaticStackableImage__top-title u-color-dark-blue">${title}</h2>
                            <p class="StaticStackableImage__top-description u-color-dark-grey">${content}</p>
                        </div>
                    </div>`
    }

    return newsshow;
}

/*文章展示形式四（图片列表常用）
参数newsAry：接口返回的文章相关数据*/
function newsShow6(newsAry) {
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

        newsshow += ` <div class="swiper-slide">
                            <div class="grid__item StaticGrid__article palm--1-1 StaticGrid__article--2">
                            <article class="GridTile u-text-left StaticGridArticle u-text-left"><a href="${url}"
                                    target="_self">
                                    <div class="GridTile__image GridTile__image--landscape" ><img
                                           src="${thumbnail}" ></div>
                                    <div class="GridTile__content-container">
                                        <div class=""></div>
                                        <div class="GridTile__heading-container">
                                            <h6 class="GridTile__heading header5 u-color-white">${title}</h6>
                                        </div>
                                        <div class="">
                                            <ul style="list-style-type:none;margin-left:0;">
                                                <li class="p2 u-color-white">${description}</li>
                                            </ul>
                                        </div>
                                        <div class=""></div>
                                        <div class="GridTile__btn">
                                            <div
                                                class="btn btn--with-arrow u-text-uppercase u-no-padding u-color-white">
                                                <span class="btn__container"><span class="btn__text">Meet the
                                                        team</span> <i
                                                        class="fa fa-long-arrow-right"></i></span>
                                            </div>
                                        </div>
                                    </div>
                                </a></article>
                        </div>
                    </div>`
    }

    return newsshow;
}
// function newsShow6(newsAry) {
//     let newsshow = "";
//     for (let i = 0; i < newsAry.length; i++) {
//         let id = newsAry[i]["id"]; //文章ID
//         let title = newsAry[i]["title"]; //文章标题
//         let url = newsAry[i]["url"]; //链接地址
//         let keywords = newsAry[i]["keywords"]; //关键词
//         let description = newsAry[i]["description"]; //文章描述
//         let thumbnail = newsAry[i]["thumbnail"]; //缩略图
//         let content = newsAry[i]["content"];
//         let enclosure = newsAry[i]["enclosure"]; //附件
//         let photo_album = newsAry[i]["photo_album"]; //图片相册
//         let add_time = formatTime(newsAry[i]["add_time"]); //文章发布日期

//         newsshow += `<li class="grid__item StaticGrid__article desk--6-12 lap--4-8 palm--1-1 StaticGrid__article--2">
//                             <article class="GridTile u-text-left StaticGridArticle u-text-left"><a href="${url}"
//                                     target="_self">
//                                     <div class="GridTile__image GridTile__image--landscape"><img
//                                            src="${thumbnail}" ></div>
//                                     <div class="GridTile__content-container">
//                                         <div class=""></div>
//                                         <div class="GridTile__heading-container">
//                                             <h6 class="GridTile__heading header5 u-color-white">${title}</h6>
//                                         </div>
//                                         <div class="">
//                                             <ul style="list-style-type:none;margin-left:0;">
//                                                 <li class="p2 u-color-white">${description}</li>
//                                             </ul>
//                                         </div>
//                                         <div class=""></div>
//                                         <div class="GridTile__btn">
//                                             <div
//                                                 class="btn btn--with-arrow u-text-uppercase u-no-padding u-color-white">
//                                                 <span class="btn__container"><span class="btn__text">Meet the
//                                                         team</span> <i
//                                                         class="fa fa-long-arrow-right"></i></span>
//                                             </div>
//                                         </div>
//                                     </div>
//                                 </a></article>
//                         </li>`
//     }

//     return newsshow;
// }

/*文章展示形式四（图片列表常用）
参数newsAry：接口返回的文章相关数据*/
function newsShow7(newsAry) {
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

        newsshow += `  <div class="swiper-slide swiper-slide-prev " data-swiper-slide-index="${i}" >
                                                <img
                                                    src="${thumbnail}">
                                               <div  class="container" style="opacity: 0;">
                                    <div 
                                        class="MediaCarousel__main-info grid grid__item palm--1-1 desk--10-12 push--desk--1-12 pull--desk--1-12" >
                                        <div 
                                            class="MediaCarousel__info grid__item desk--6-10 lap--5-8 palm--1-1">
                                            <div >
                                                <div 
                                                    class="MediaCarousel__info-parent e1 u-color-grey">
                                                    ${description}
                                                </div>
                                                <div 
                                                    class="MediaCarousel__info-name h6 u-color-white">${title}</div>
                                                <p 
                                                    class="MediaCarousel__description u-color-grey p2">${content}</p>
                                            </div>
                                        </div> 
                                    </div>
                                </div>
                                            </div>`
    }

    return newsshow;
}

/*文章展示形式四（图片列表常用）
参数newsAry：接口返回的文章相关数据*/
function newsShow8(newsAry) {
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

        let widthClass = i == newsAry.length - 1 ? " " : "desk--6-12";

        newsshow += `<li class="grid__item StaticGrid__article ${widthClass} lap--4-8 palm--1-1 StaticGrid__article--2"
                            >
                            <article class="GridTile u-text-left StaticGridArticle u-text-left" 
                                ><a href="${url}" target="_self" >
                                    <div class="GridTile__image GridTile__image--landscape lazy-img" ><img
                                           src="${thumbnail}"
                                            ></div>
                                    <div class="GridTile__content-container" >
                                        <div  class=""></div>
                                        <div class="GridTile__heading-container" >
                                            <h6 class="GridTile__heading header5 u-color-white" >${title}</h6>
                                        </div>
                                        <div  class=""></div>
                                        <div  class=""></div>
                                    </div>
                                </a></article>
                        </li>`
    }

    return newsshow;
}

/*文章展示形式四（图片列表常用）
参数newsAry：接口返回的文章相关数据*/
function newsShow9(newsAry) {
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
        let phone = newsAry[i]["field"]["phone"]; //附件
        let real_estate_broker_email = newsAry[i]["field"]["real_estate_broker_email"]; //附件
        let photo_album = newsAry[i]["photo_album"]; //图片相册
        let add_time = formatTime(newsAry[i]["add_time"]); //文章发布日期

        let widthClass = i == newsAry.length - 1 ? " " : "desk--6-12";

        newsshow += `<div>
                            <div class="m-agent-item-results__card"><a href="${url}" class="m-agent-item-results__card-photo skeleton-v2 ">
                                    <div class="responsive-image-container lazy-img"  style="width:100%;height:100%;background-color:transparent">
                                     <img src="${thumbnail}" width="300" height="100" decoding="async" data-nimg="1" class="" loading="lazy" style="color:transparent;width:100%;height:100%;object-fit:contain">
                                    </div>
                                </a>
                                <div class="m-agent-item-results__card-container skeleton-v2 ">
                                    <div class="m-agent-item-results__card-details"><a href="${url}" class="m-agent-item-results__card-name u-color-sir-blue js-fitty-target u-cursor-pointer u-text-align-left">${title}</a>
                                        <div class="m-agent-item-results__card-title u-text-uppercase u-color-dark-grey">${keywords}</div>
                                        <div class="m-agent-item-results__card-separator"></div>
                                        <div class="m-agent-item-results__card-advertiser">${description}</div>
                                        <div class="m-agent-item-results__card-address-wrapper p2 u-color-dark-grey palm--hide">
                                            <div class="m-agent-item-results__card-address m-agent-item-results__card-address--main">
                                            ${content}</div>
                                        </div>
                                    </div>
                                    <div class="m-agent-item-results__card-contact-details">
                                        <div class="m-agent-item-results__card-contact-details-title u-text-uppercase u-color-dark-grey palm--hide">
                                            Contact</div>
                                        <div class="m-agent-item-results__card-contact-phones">
                                            <div class="phones__wrapper"><a tabindex="0"  href="tel:${phone}">O: ${phone}</a>
                                            </div>
                                            <div class="phones__wrapper"><a tabindex="0"  href="mailto:${real_estate_broker_email}">${real_estate_broker_email}</a>
                                            </div>
                                        </div><a href="${url}" class="m-agent-item-results__card-contact-btn btn u-text-uppercase u-color-sir-blue palm--hide"><span>Send
                                                message</span><i class="fa fa-long-arrow-right"></i></a>
                                    </div>
                                </div>
                            </div>
                        </div>`
    }

    return newsshow;
}

/*文章展示形式四（图片列表常用）
参数newsAry：接口返回的文章相关数据*/
function newsShow10(newsAry) {
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
        let phone = newsAry[i]["field"]["phone"]; //附件
        let photo_album = newsAry[i]["photo_album"]; //图片相册
        let add_time = formatTime(newsAry[i]["add_time"]); //文章发布日期

        let widthClass = i == newsAry.length - 1 ? " " : "desk--6-12";

        newsshow += `<li
                            class="grid__item StaticGrid__article desk--6-12 lap--4-8 palm--1-1 StaticGrid__article--2 about07bg">
                            <article class="GridTile u-text-left StaticGridArticle u-text-left"><a href="${url}"
                                    target="_self">
                                    <div class="GridTile__image GridTile__image--landscape lazy-img" ><img src="${thumbnail}" >
                                    </div>
                                    <div class="GridTile__content-container">
                                        <div class=""></div>
                                        <div class="GridTile__heading-container">
                                            <h6 class="GridTile__heading header5 u-color-sir-blue">${title}</h6>
                                        </div>
                                        <div class=""></div>
                                        <div class=""></div>
                                        <div class="GridTile__btn">
                                            <div
                                                class="btn btn--with-arrow u-text-uppercase u-no-padding u-color-sir-blue">
                                                <span class="btn__container"><span class="btn__text">${keywords}</span> <i
                                                        class="fa fa-long-arrow-right"></i></span>
                                            </div>
                                        </div>
                                    </div>
                                </a></article>
                        </li>`
    }

    return newsshow;
}

/*文章展示形式100 新闻页热门新闻
参数newsAry：文章数据*/
function newsShow100(newsAry) {
    let newsshow = "<ul>";

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
        let nclass = "";
        if (i == 0) {
            nclass = "active";
        }
        let num = i + 1;

        newsshow += "<li class='" + nclass + "'><a href='" + url + "' title='" + title + "'>" +
            "<div class='nr_box'>" +
            "<div class='imgbg'>" +
            "<div class='img lazy-img' ><img src='" + thumbnail + "' alt='" + title + "'></div>" +
            "</div>" +
            "<div class='txtbg'>" +
            "<div class='txt'>" +
            "<div class='h2'><em>" + num + "</em><span>" + title + "</span></div>" +
            "</div>" +
            "</div>" +
            "</div>" +
            "</a></li>";
    }
    newsshow += "</ul><div class='clear'></div>";

    return newsshow;
}

/*文章分类展示
参数id：文章分类ID（为0时，显示所有文章分类）
参数top：显示文章分类的个数，-1表示显示所有相关文章分类
参数type：文章分类列表展现形式*/
function newsclass_list(id, top, type) {
    id = id || 0;
    top = top || -1;
    type = type || 1;

    let newsclasslist = "";
    let newsclass = news_class(id, '', top);
    if (newsclass.length > 0) {
        // 动态调用 newsClassShow + 数字
        if (typeof window[`newsClassShow${type}`] === 'function') {
            newsclasslist = window[`newsClassShow${type}`](newsclass);
        } else {
            newsclasslist = newsClassShow1(newsclass);
        }
    }
    return newsclasslist;
}

/*文章分类展示形式一
参数newsClassAry：文章分类数据*/
function newsClassShow1(newsClassAry) {
    let newsclassshow = "<div class='index_newsclass1'><ul>";

    for (let i = 0; i < newsClassAry.length; i++) {
        let id = newsClassAry[i]["id"]; //文章分类ID
        let title = newsClassAry[i]["title"]; //文章分类标题
        let url = newsClassAry[i]["url"]; //跳转链接
        let description = newsClassAry[i]["description"]; //文章分类描述
        let thumbnail = newsClassAry[i]["thumbnail"]; //缩略图
        let banner = newsClassAry[i]["banner"]; //通栏图片
        let add_time = formatTime(newsClassAry[i]["add_time"]); //文章分类添加日期

        newsclassshow += "<li><a href='" + url + "' title='" + title + "'>" + title + "</a></li>";
        newsclassshow += `<div id="news-list-${id}" class="news-list-container">加载中...</div>`;
    }
    newsclassshow += "</ul><div class='clear'></div></div>";


    // 异步加载文章列表
    setTimeout(() => {
        for (let i = 0; i < newsClassAry.length; i++) {
            let id = newsClassAry[i]["id"];
            wf_news_list(id).done(function (html) {
                $(`#news-list-${id}`).html(html);
            });
        }
    }, 0);

    return newsclassshow;
}

//网站信息--在网站底部展示
function foot_info() {
    let myDate = new Date();
    let footinfo = "<div class='f_info'>";
    footinfo += "<p>";
    footinfo += !isEmpty(web_info.company) ? "<span style='font-family: Arial, Helvetica, sans-serif;'>Copyright &copy; 2022-" + myDate.getFullYear() + "</span> " + web_info.company + " Rights Reserved." : "";
    footinfo += !isEmpty(web_info.icp) ? "&nbsp;&nbsp;备案号：<a href='https://beian.miit.gov.cn/' target='_blank'>" + web_info.icp + "</a>" : "";
    footinfo += !isEmpty(web_info.icp_police) ? "&nbsp;&nbsp;公安备案号：" + web_info.icp_police : "";
    footinfo += "</p>";
    footinfo += "<p>";
    footinfo += !isEmpty(web_info.address) ? "地址：" + web_info.address : "";
    footinfo += !isEmpty(web_info.phone) ? "&nbsp;&nbsp;电话：" + web_info.phone : "";
    footinfo += !isEmpty(web_info.mobile) ? "&nbsp;&nbsp;手机：" + web_info.mobile : "";
    footinfo += !isEmpty(web_info.email) ? "&nbsp;&nbsp;邮箱：" + web_info.email : "";
    footinfo += !isEmpty(web_info.contact) ? "&nbsp;&nbsp;联系人：" + web_info.contact : "";
    footinfo += !isEmpty(web_info.qq) ? "&nbsp;&nbsp;QQ：" + web_info.qq : "";
    footinfo += !isEmpty(web_info.wechat) ? "&nbsp;&nbsp;微信号：" + web_info.wechat : "";
    footinfo += "</p>";
    footinfo += !isEmpty(web_code) ? "<p>" + web_code[0]["code"] + "</p>" : "";
    footinfo += links() != "" ? "<p>友情链接：" + links() + "</p>" : "";
    footinfo += "</div>";

    footinfo += "<div class='f_info2'>";
    footinfo += "<p>";
    footinfo += !isEmpty(web_info.company) ? "<span>版权所有 &copy;" + myDate.getFullYear() + "</span> " + web_info.company : "";
    footinfo += "</p>";
    footinfo += "<p>";
    footinfo += !isEmpty(web_info.icp) ? "备案号：<a href='https://beian.miit.gov.cn/' target='_blank'>" + web_info.icp + "</a>" : "";
    footinfo += "</p>";
    footinfo += "<p>";
    footinfo += !isEmpty(web_info.icp_police) ? "公安备案号：" + web_info.icp_police : "";
    footinfo += "</p>";
    footinfo += "</div>";

    footinfo += "<div class='m1_fheight'></div>" +
        "<div class='m1_side_kefu_box'>" +
        "<div class='m1_side_kefu'>" +
        "<ul>";
    for (let i = 0; i < customer_service.length; i++) {
        if (customer_service[i]["key"] == "mobile") {
            footinfo += "<li><a href='tel:" + customer_service[i]["value"] + "'><div class='ico'><img src='/images/ico_phone1.png' class='img1'><img src='/images/ico_phone1_hover.png' class='img2'></div><div class='h2'>电话咨询</div></a><div class='boxbg box1'><div class='box'><div class='h3'><img src='/images/ico_phone1.png'>" + customer_service[i]["value"] + "</div></div></div></li>";
        } else if (customer_service[i]["key"] == "qq") {
            footinfo += "<li><a href='mqqwpa://im/chat?chat_type=wpa&uin=" + customer_service[i]["value"] + "&version=1&src_type=web'><div class='ico'><img src='/images/ico_qq1.png' class='img1'><img src='/images/ico_qq1_hover.png' class='img2'></div><div class='h2'>QQ客服</div></a><div class='boxbg box1'><div class='box'><div class='h3'><img src='/images/ico_qq1.png'>" + customer_service[i]["value"] + "</div></div></div></li>";
        } else if (customer_service[i]["key"] == "wechat") {
            footinfo += "<li><a href='javascript:;'><div class='ico'><img src='/images/ico_wechat1.png' class='img1'><img src='/images/ico_wechat1_hover.png' class='img2'></div><div class='h2'>微信客服</div></a><div class='boxbg box2'><div class='box'><div class='ewm'><div class='h4'>微信扫码加好友</div><img src='" + customer_service[i]["value"] + "'></div></div></div></li>";
        }
    }
    footinfo += "<li class='gotop'><a href='javascript:scroll(0,0);'><div class='ico'><div class='ico'><img src='/images/ico_gotop1.png' class='img1'><img src='/images/ico_gotop1_hover.png' class='img2'></div></div><div class='h2'>返回顶部</div></a></li>";
    footinfo += "</ul>" +
        "<div class='clear'></div>" +
        "</div>" +
        "<div class='m1_side_kefu_btn'><i class='m1_side_kefu_open'></i><i class='m1_side_kefu_close'></i></div>" +
        "</div>";

    footinfo += "<div class='m1_side_kefu2_box' id='scrollsidebar'>" +
        "<div class='m1_side_kefu2'>" +
        "<ul>";
    for (let i = 0; i < customer_service.length; i++) {
        if (customer_service[i]["key"] == "mobile" && customer_service[i]["state"]) {
            footinfo += "<li><a href='tel:" + customer_service[i]["value"] + "'><div class='ico'><i class='fa fa-phone'></i></div></a><div class='boxbg box1'><div class='box'><div class='h3'><i class='fa fa-phone'></i>" + customer_service[i]["value"] + "</div></div></div></li>";
        } else if (customer_service[i]["key"] == "qq" && customer_service[i]["state"]) {
            footinfo += "<li><a href='tencent://message/?uin=" + customer_service[i]["value"] + "&Site=www.webforce.com.cn&Menu=yes'><div class='ico'><i class='fa fa-qq'></i></div></a><div class='boxbg box1'><div class='box'><div class='h3'><i class='fa fa-qq'></i>" + customer_service[i]["value"] + "</div></div></div></li>";
        } else if (customer_service[i]["key"] == "wechat" && customer_service[i]["state"]) {
            footinfo += "<li><a href='javascript:;'><div class='ico'><i class='fa fa-wechat'></i></div></a><div class='boxbg box2'><div class='box'><div class='ewm'><div class='h4'>微信扫码加好友</div><img src='" + customer_service[i]["value"] + "'></div></div></div></li>";
        } else if (customer_service[i]["key"] == "facebook" && customer_service[i]["state"]) {
            footinfo += "<li><a href='" + customer_service[i]["value"] + "' target='_blank'><div class='ico'><i class='fa fa-facebook'></i></div></a></li>";
        } else if (customer_service[i]["key"] == "twitter" && customer_service[i]["state"]) {
            footinfo += "<li><a href='" + customer_service[i]["value"] + "' target='_blank'><div class='ico'><i class='fa fa-twitter'></i></div></a></li>";
        } else if (customer_service[i]["key"] == "linkedin" && customer_service[i]["state"]) {
            footinfo += "<li><a href='" + customer_service[i]["value"] + "' target='_blank'><div class='ico'><i class='fa fa-linkedin'></i></div></a></li>";
        } else if (customer_service[i]["key"] == "instagram" && customer_service[i]["state"]) {
            footinfo += "<li><a href='" + customer_service[i]["value"] + "' target='_blank'><div class='ico'><i class='fa fa-instagram'></i></div></a></li>";
        } else if (customer_service[i]["key"] == "youtube" && customer_service[i]["state"]) {
            footinfo += "<li><a href='" + customer_service[i]["value"] + "' target='_blank'><div class='ico'><i class='fa fa-youtube-play'></i></div></a></li>";
        }
    }
    footinfo += "</ul>" +
        "<div class='clear'></div>" +
        "</div>" +
        "</div>";

    return footinfo;
}

/*底部导航
参数id：父级栏目ID
参数child：栏目二级数据带入循环用
参数level：菜单的层级数，1是一级，2是二级...*/
function menu_footer(id, child, level) {
    id = id || 0;
    child = child || [];
    level = level || 1;

    let columnlist;

    if (child.length > 0) {
        level = level + 1;
        columnlist = child;
    } else {
        columnlist = menu(id);
    }

    let ret_menu = "";
    if (level == 1) { //一级栏目时才需要div包裹
        ret_menu = "<div class='f_nav'>";
    }
    ret_menu += "<ul>";
    if (columnlist.length > 0) {
        for (let i = 0; i < columnlist.length; i++) {
            let c_id = columnlist[i]["id"]; //栏目ID
            let title = columnlist[i]["title"]; //栏目标题
            let sub_title = columnlist[i]["sub_title"]; //栏目副标题
            let url = columnlist[i]["url"]; //链接地址--前半部分
            let type = columnlist[i]["type"]; //栏目类别
            let link_id = columnlist[i]["link_id"]; //相关栏目ID
            let children = columnlist[i]["children"]; //子栏目

            let tag = "";  //打开方式
            if (url.indexOf("http") != -1) {
                tag = " target='_blank'";
            }

            ret_menu += "<li><a href='" + url + "' title='" + title + "'" + tag + ">" + title + "</a>";
            if (children.length > 0) {
                ret_menu += menu_footer(id, children, level);
            }
            ret_menu += "</li>";
        }
    }
    ret_menu += "</ul>";
    if (level == 1) { //一级栏目时才需要div包裹
        ret_menu += "</div>";
    }

    return ret_menu;
}

/*友情链接
* @param int top 显示多少个友情链接，为0的时候显示全部友情链接*/
function links(top) {
    top = top || 0;
    let linksShow = "";
    if (links_info.length > 0) {
        linksShow += "<ul>";
        let len = top > 0 && links_info.length > top ? top : links_info.length; //判断for循环次数
        for (let i = 0; i < len; i++) {
            let row = links_info[i];
            let title = row.title; //公司名称
            let url = row.url; //网址
            let thumbnail = row.thumbnail; //友链缩略图

            if (url.indexOf("http:") == -1) {
                url = "http://" + url;
            }
            linksShow += "<li>";
            linksShow += "<img src='" + thumbnail + "' alt='" + title + "' style='max-width: 60px;'/>";
            linksShow += "<a href='" + url + "' title='" + title + "' target='_blank'>" + title + "</a>";
            linksShow += "</li>";
        }
        linksShow += "</ul>";
    }
    return linksShow;
}

/*友情链接分类*/
function linksclass() {
    let linksClassShow = "";
    if (links_class_info.length > 0) {
        linksClassShow += "<ul>";
        for (let i = 0; i < links_class_info.length; i++) {
            let row = links_class_info[i];
            let id = row["id"]; //友链分类id
            let title = row["title"]; //友链分类名称
            let thumbnail = row["thumbnail"]; //友链分类缩略图

            linksClassShow += "<li>";
            linksClassShow += "<img src='" + thumbnail + "' alt='" + title + "' style='max-width: 60px;'/>" + title;
            linksClassShow += "</li>";
        }
        linksClassShow += "</ul>";
    }
    return linksClassShow;
}

// ------------------------------通用资源 开始------------------------------
// SVG图标定义
const icons = {
    success: `<svg viewBox="0 0 24 24" class="modal-icon">
                <path fill="currentColor" d="M12,2A10,10 0 0,1 22,12A10,10 0 0,1 12,22A10,10 0 0,1 2,12A10,10 0 0,1 12,2M11,16.5L18,9.5L16.59,8.09L11,13.67L7.91,10.59L6.5,12L11,16.5Z"/>
            </svg>`,
    error: `<svg viewBox="0 0 24 24" class="modal-icon">
                <path fill="currentColor" d="M12,2C17.53,2 22,6.47 22,12C22,17.53 17.53,22 12,22C6.47,22 2,17.53 2,12C2,6.47 6.47,2 12,2M15.59,7L12,10.59L8.41,7L7,8.41L10.59,12L7,15.59L8.41,17L12,13.41L15.59,17L17,15.59L13.41,12L17,8.41L15.59,7Z"/>
            </svg>`,
    info: `<svg viewBox="0 0 24 24" class="modal-icon">
                <path fill="currentColor" d="M13,9H11V7H13M13,17H11V11H13M12,2A10,10 0 0,0 2,12A10,10 0 0,0 12,22A10,10 0 0,0 22,12A10,10 0 0,0 12,2Z"/>
            </svg>`,
    warning: `<svg viewBox="0 0 24 24" class="modal-icon">
                <path fill="currentColor" d="M13,14H11V10H13M13,18H11V16H13M1,21H23L12,2L1,21Z"/>
            </svg>`,
    close: `<svg viewBox="0 0 24 24">
                <path fill="currentColor" d="M19,6.41L17.59,5L12,10.59L6.41,5L5,6.41L10.59,12L5,17.59L6.41,19L12,13.41L17.59,19L19,17.59L13.41,12L19,6.41Z"/>
            </svg>`
};

// 当前显示的模态框
let currentModal = null;

/**
 * 显示模态框
 * @param {string} type - 消息类型: 'success', 'error', 'info', 'warning'
 * @param {string} title - 消息标题
 * @param {string} content - 消息内容（可选）
 * @param {number} duration - 显示时长(毫秒)，默认3000ms
 * @param {function} callback - 消失后的回调函数（可选）
 */
function showModal(type, title, content = '', duration = 3000, callback = null) {
    // 如果已经有模态框在显示，先关闭它
    if (currentModal) {
        closeModal();
    }

    // 创建遮罩层
    const overlay = document.createElement('div');
    overlay.className = 'modal-overlay';
    overlay.id = 'modalOverlay';

    // 检查是否有内容
    const hasContent = content && content.trim().length > 0;

    // 创建模态框
    const modalBox = document.createElement('div');
    modalBox.className = `modal-box ${type} ${hasContent ? '' : 'compact'}`;
    modalBox.id = 'modalBox';

    // 构建模态框内容
    let modalContent = `
                <div class="modal-header ${hasContent ? '' : 'compact'}">
                    ${icons[type]}
                    <div class="modal-title">${title}</div>
                    <button class="modal-close" onclick="closeModal()">
                        ${icons.close}
                    </button>
                </div>
            `;

    // 如果有内容，添加内容区域
    if (hasContent) {
        modalContent += `
                    <div class="modal-content">${content}</div>
                `;
    }

    // 添加进度条
    modalContent += `<div class="modal-progress" id="modalProgress"></div>`;

    modalBox.innerHTML = modalContent;
    overlay.appendChild(modalBox);
    document.body.appendChild(overlay);

    // 设置进度条动画 - 使用JavaScript动态设置动画时间
    const progressBar = document.getElementById('modalProgress');
    if (progressBar) {
        // 移除默认动画，设置自定义时长的动画
        progressBar.style.animation = 'none';
        // 强制重绘
        void progressBar.offsetWidth;
        // 设置新的动画
        progressBar.style.animation = `progressDefault ${duration}ms linear forwards`;
    }

    // 保存当前模态框引用
    currentModal = {
        overlay: overlay,
        timer: setTimeout(() => closeModal(callback), duration),
        callback: callback
    };

    // 点击遮罩层也可以关闭
    overlay.addEventListener('click', function (e) {
        if (e.target === overlay) {
            closeModal(callback);
        }
    });
}

/**
 * 关闭模态框
 * @param {function} callback - 回调函数
 */
function closeModal(callback = null) {
    if (!currentModal) return;

    const modalBox = document.getElementById('modalBox');
    if (modalBox) {
        modalBox.classList.add('hiding');
    }

    // 清除定时器
    clearTimeout(currentModal.timer);

    // 保存回调函数
    const finalCallback = callback || currentModal.callback;

    // 动画结束后移除元素并执行回调
    setTimeout(() => {
        if (currentModal.overlay.parentNode) {
            currentModal.overlay.parentNode.removeChild(currentModal.overlay);
        }

        // 执行回调函数
        if (finalCallback && typeof finalCallback === 'function') {
            finalCallback();
        }

        currentModal = null;
    }, 300);
}

//提示框2
//a是类型（success，error，warning）
//b是内容
function showPopup2(a, b) {
    let Popup2Element = document.createElement("div"); // 创建一个<div>元素，用于显示弹出框
    Popup2Element.classList.add("modal2"); // 添加CSS类名
    Popup2Element.innerHTML = "<div class='popup_tip2_box ts_box'><div class='popup_tip2bg'><div class='popup_tip2'><div class='popup_tip2-body'><div class='ts'><div class='h2'>提示</div><div class='h3'><i></i><span id='ts_txt'>" + b + "</span></div><div class='ts_btn'><a href='javascript:;' id='ts_true'>确定</a><a href='javascript:;' class='bg2' id='ts_false' onclick='closePopup2()'>取消</a></div></div></div></div></div></div>";
    document.body.appendChild(Popup2Element); // 将弹出框<div>元素追加到<body>末尾

    if (a == "success") {
        $(".popup_tip2_box").addClass("success").find(".popup_tip2").addClass("zoomIn");
    }
    if (a == "error") {
        $(".popup_tip2_box").addClass("error").find(".popup_tip2").addClass("zoomIn");
    }
    if (a == "warning") {
        $(".popup_tip2_box").addClass("warning").find(".popup_tip2").addClass("zoomIn");
    }

}

function closePopup2() {
    let modalElement = document.querySelector(".modal2");
    modalElement.parentNode.removeChild(modalElement); // 移除弹出框<div>元素
    $(".popup_tip2").removeClass("zoomIn");
}


/*加载*/
function showLoader() {
    let show_loader = "<div class='loading_close1'></div><div class='loadingbg'><div class='loading'><div class='loading_logo'><img src='" + pic(6, "path") + "'><div class='loadEffect'><div><span></span></div><div><span></span></div><div><span></span></div><div><span></span></div></div></div><div class='loading_txt'>欢迎访问<span class='company'>" + web_info.company + "</span>官网！</div></div></div>";

    return show_loader;
}


/*内页联系方式*/
function inner_contact() {
    let show_incontact = "<div class='inLt_contact'><div class='h2'><span>服务热线</span><strong class='phone'>" + web_info.phone + "</strong></div>" +
        "<div class='h3'><span class='company'>" + web_info.company + "</span></div>" +
        "<ul>" +
        "<li>联系人：<span class='contact'>" + web_info.contact + "</span></li>" +
        "<li>手机：<span class='mobile'>" + web_info.mobile + "</span></li>" +
        "<li>邮箱：<span class='email'>" + web_info.mobile + "</span></li>" +
        "<li>QQ：<span class='qq'>" + web_info.qq + "</span></li>" +
        "<li>微信：<span class='wechat'>" + web_info.wechat + "</span></li>" +
        "<li>地址：<span class='address'>" + web_info.address + "</span></li>" +
        "</ul></div><div class='inLt_contact_btn'><span class='inLt_contact_close'>点击收起</span><span class='inLt_contact_open'>联系方式</span></div>";

    return show_incontact;
}


// 键盘回车事件
function kdSubmit(formId) {
    // 检查是否按下了回车键 (key code 13)
    if (event.keyCode === 13) {
        // 阻止默认的提交事件（如果需要）
        event.preventDefault();

        // 触发特定表单的提交事件
        switch (formId) {
            case "infosearch":
                searchForm()
                break;
            case "infosearch2":
                searchForm2()
                break;
        }
    }
}

// 加密函数
function encryptParam(data) {
    var secretKey = CryptoJS.enc.Utf8.parse('fMce8fVH5tGOEK7CicKC23MQM0n0pZaM'); // 必须是32字节长（256位）
    var iv = CryptoJS.lib.WordArray.random(16); // 生成16字节长的IV

    var encrypted = CryptoJS.AES.encrypt(data, secretKey, {
        iv: iv,
        mode: CryptoJS.mode.CBC,
        padding: CryptoJS.pad.Pkcs7
    });

    // 将IV和密文转换为Base64字符串以便传输
    var ivBase64 = CryptoJS.enc.Base64.stringify(iv);
    var ciphertextBase64 = encrypted.ciphertext.toString(CryptoJS.enc.Base64);

    return { iv: ivBase64, ciphertext: ciphertextBase64 }
}

//脚本加载完执行
$(document).ready(function () {
    //头部模块加载
    $(".header").html(header());

    //底部模块加载
    $(".footer").html(footer());

    // 当点击导航栏子栏目时，若子栏目被选中，父级也被选中
    $(".menu .level_1 ul .level_2").each(function () {
        if ($(this).hasClass('hover')) {
            $(this).parent().parent().addClass('hover');
        }
    });

    // 当点击内页产品分类子分类时，若子分类被选中，该栏目直接展开
    $(".m1_side_a .level_1 ul .level_2").each(function () {
        if ($(this).hasClass('hover')) {
            $(this).parent().css('display', 'block');
            $(this).parents(".level_1").addClass("active");
        }
    });
    $(".m1_side_a .level_1 ul .level_2 ul .level_3").each(function () {
        if ($(this).hasClass('hover')) {
            $(this).parent().css('display', 'block');
            $(this).parent().parent().parent().css('display', 'block');
            $(this).parents(".level_2").addClass("active");
            $(this).parents(".level_1").addClass("active");
        }
    });

    //执行流量统计
    if (page_name !== 'traffic_statistics') {
        trafficStatistics();
    }
});
// ------------------------------通用资源 结束------------------------------

// ------------------------------内页相关function 开始------------------------------
/*内页菜单栏（内页导航）*/
function menu_inner() {
    let menu_list = "";
    let parent_id = 0;  //父级栏目ID
    let column_type = 0; //栏目类型
    let columnAry = []; //子栏目数组
    if (local_c_id > 0) {  //判断的当前栏目类型是不是产品列表或产品分类，是的话内页菜单栏直接像是产品分类
        parent_id = menu(local_c_id, "parentid");
        column_type = menu(local_c_id, "type");
        columnAry = menu(local_c_id);
    }

    if ((parent_id > 0 || columnAry.length > 0) && column_type != "9" && column_type != "10" && page_name !== "product" && page_name !== "prodetail" && page_name !== "search") {
        menu_list = menu_navigate_inner();
    } else if (page_name == "search" || page_name == "shoppingcart") {
        menu_list = "";
    } else {
        menu_list = proclass_navigate_inner();
    }

    return menu_list;
}

/*内页菜单栏--导航栏
参数child：栏目二级数据带入循环用
参数level：菜单的层级数，1是一级，2是二级...*/
function menu_navigate_inner(child, level) {
    child = child || [];
    level = level || 1;

    let columnlist;
    let topid = getMenuParentID(local_c_id); //获取当前栏目的顶级id
    if (child.length > 0) {
        level = level + 1;
        columnlist = child;
    } else {
        columnlist = menu(getMenuParentID(local_c_id));
    }
    let ret_menu = "";

    ret_menu += "<ul>";
    if (level == 1) {
        ret_menu += "<li><h2>" + menu(topid, "title") + "</h2></li>";
    }
    if (columnlist.length > 0) {
        for (let i = 0; i < columnlist.length; i++) {
            let c_id = columnlist[i]["id"];
            let title = columnlist[i]["title"];
            let sub_title = columnlist[i]["sub_title"];
            let url = columnlist[i]["url"];
            let type = columnlist[i]["type"];
            let link_id = columnlist[i]["link_id"];
            let children = columnlist[i]["children"];

            let tag = "";  //打开方式
            if (url.indexOf("http") != -1) {
                tag = " target='_blank'";
            }

            let strArrow = ""; //当有二级的时候，显示向下的箭头
            if (children.length > 0) {
                strArrow = "<i></i>";
            }

            let strClass = "";
            if (children.length > 0) {
                strClass = " menu_down";
            }
            let strHover = ""; //选中状态的class
            if (local_path_name == url) {
                strHover = " hover";
            } else if ((local_path_name == "/" || local_path_name == "") && (title == "首页" || title == "网站首页" || title == "Home")) {
                strHover = " hover";
            }

            ret_menu += "<li class='level_" + level + strClass + strHover + "'><span><a href='" + url + "' title='" + title + "'" + tag + ">" + title + "</a>" + strArrow + "</span>";
            if (children.length > 0) {
                ret_menu += menu_navigate_inner(children, level);
            }
            ret_menu += "</li>";
        }
    }
    ret_menu += "</ul>";

    return ret_menu;
}

//找到当前栏目的父级id
function getMenuParentID(id) {
    let get_id = id; //栏目的顶级父级ID
    let parent_id = menu(id, "parentid"); //获取当前栏目父级ID
    if (parent_id > 0) {
        get_id = parent_id;
    }
    do {
        parent_id = menu(parent_id, "parentid");
        if (parent_id > 0) {
            get_id = parent_id;
        }
    } while (parent_id > 0)

    return get_id;
}

//根据当前url路径，找到对应的菜单栏ID
function getMenuID(url) {
    if (getUrlParameter("c_id") > 0) return getUrlParameter("c_id");
    if (isEmpty(url)) return 0;
    if (!menu_info || !Array.isArray(menu_info)) return 0;
    try {
        return getMenuCId(url, menu_info);
    } catch (error) {
        return 0;
    }
}

//递归-从最底层开始匹配对应栏目
function getMenuCId(url, menu_info) {
    var c_id = 0;
    if (!isEmpty(url) && !isEmpty(menu_info)) {
        for (let i = 0; i < menu_info.length && c_id == 0; i++) {
            if (menu_info[i].children.length > 0) {
                c_id = getMenuCId(url, menu_info[i].children);
            }

            if (c_id == 0 && url == menu_info[i].url) {
                c_id = menu_info[i].id;
            }
        }
    }
    return c_id;
}


/*内页菜单栏--产品分类
参数child：产品二级数据带入循环用
参数level：菜单的层级数，1是一级，2是二级...*/
function proclass_navigate_inner(child, level) {
    child = child || [];
    level = level || 1;

    let proclassArry;

    if (child.length > 0) {
        level = level + 1;
        proclassArry = child;
    } else {
        proclassArry = product_class();
    }

    //当在产品详情页时，获取当前产品对应的分类id
    let local_proclassid = 0;
    if (productPageAry.indexOf(page_name) > -1) {
        local_proclassid = l_product_info.classid[0];
    }

    let proclass_list = ""; //返回的产品分类列表

    proclass_list += "<ul>";
    if (level == 1) {
        proclass_list += "<li><h2><strong>产品分类</strong><span>PRODUCTS</span></h2></li>";
    }
    if (proclassArry.length > 0) {
        for (let i = 0; i < proclassArry.length; i++) {
            let proclass_id = proclassArry[i]["id"];
            let title = proclassArry[i]["title"];
            let description = proclassArry[i]["description"];
            let thumbnail = proclassArry[i]["thumbnail"];
            let banner = proclassArry[i]["banner"];
            let children = proclassArry[i]["children"];
            let url = proclassArry[i]["url"];

            let strArrow = ""; //当有二级的时候，显示向下的箭头
            if (children.length > 0) {
                strArrow = "<i></i>";
            }

            let strClass = "";
            if (children.length > 0) {
                strClass = " menu_down";
            }
            let strHover = ""; //选中状态的class
            if (local_id == proclass_id && productPageAry.indexOf(page_name) == -1) {
                strHover = " hover";
            }
            if (local_proclassid > 0 && local_proclassid == proclass_id) {
                strHover = " hover";
            }

            proclass_list += "<li class='level_" + level + strClass + strHover + "'><span><a href='" + url + "' title='" + title + "'>" + title + "</a>" + strArrow + "</span>";
            if (children.length > 0) {
                proclass_list += proclass_navigate_inner(children, level);
            }
            proclass_list += "</li>";
        }
    }
    proclass_list += "</ul>";

    return proclass_list;
}

/*内页通栏
参数id：图片管理中，图片位置ID*/
function banner_inner(id) {
    let inbanner = "";
    let picAry = []; //图片信息

    if (local_c_id > 0) {
        picAry = menu(local_c_id, "banner");
        if (picAry.length > 0) {
            inbanner = bannerInnerShow(picAry, menu(local_c_id, "title"));
        }
    } else if (pagePageAry.indexOf(page_name) > -1) {
        picAry = l_page_info.photo_album;
        if (picAry.length > 0) {
            inbanner = bannerInnerShow(picAry, l_page_info.title);
        }
    } else if (newsClassPageAry.indexOf(page_name) > -1) {
        picAry = news_class(local_id, "banner");
        if (picAry.length > 0) {
            inbanner = bannerInnerShow(picAry, news_class(local_id, "title"));
        }
    } else if (newsPageAry.indexOf(page_name) > -1) {
        picAry = news_class(l_news_info.classid[0], "banner");
        if (picAry.length > 0) {
            inbanner = bannerInnerShow(picAry, news_class(local_id, "title"));
        }
    } else if (productClassPageAry.indexOf(page_name) > -1) {
        picAry = product_class(local_id, "banner");
        if (picAry.length > 0) {
            inbanner = bannerInnerShow(picAry, product_class(local_id, "title"));
        }
    } else if (productPageAry.indexOf(page_name) > -1) {
        picAry = product_class(l_product_info.classid[0], "banner");
        if (picAry.length > 0) {
            inbanner = bannerInnerShow(picAry, product_class(local_id, "title"));
        }
    }

    if (picAry.length == 0) {
        picAry = pic(id); //获取内页通栏图片信息-图片管理
        if (picAry.length > 0) {
            inbanner += "<div class='inbanner'>";
            picAry.forEach((e) => {
                inbanner += "<img src='" + e.path + "' alt='" + e.name + "'>";
            });
            inbanner += "</div>";
        }
    }

    return inbanner;
}

/*内页通栏展示
* @param array picAry 内页通栏图片信息
* @param string title 内页通栏标题*/
function bannerInnerShow(picAry, title) {
    let showBanner = "";
    showBanner += "<div class='inbanner'>";
    picAry.forEach((e) => {
        showBanner += "<img src='" + e + "' alt='" + title + "'>";
    });
    showBanner += "</div>";
    return showBanner;
}

/*当前内页名称*/
function title_inner() {
    let this_title = "";

    if (local_c_id > 0) {
        this_title = menu(local_c_id, "title");
    } else if (pagePageAry.indexOf(page_name) > -1) {
        this_title = l_page_info.title;
    } else if (newsClassPageAry.indexOf(page_name) > -1) {
        this_title = news_class(local_id, "title");
    } else if (newsPageAry.indexOf(page_name) > -1 || pagePageAry.indexOf(page_name) > -1) {
        this_title = l_news_info.title;
    } else if (productClassPageAry.indexOf(page_name) > -1) {
        if (local_id > 0) {
            this_title = product_class(local_id, "title");
        } else {
            this_title = '产品中心';
        }
    } else if (productPageAry.indexOf(page_name) > -1) {
        this_title = l_product_info.title;
    }

    return this_title;
}

/*面包屑导航*/
function navigation() {
    let nav = "位置：<a href='http://" + window.location.host + "'>首页</a>";
    if (page_name != "/" && page_name != "index") {
        if (!isEmpty(local_c_id) && local_c_id > 0) {
            let parent_id = menu(local_c_id, "parentid"); //获取当前栏目的父级栏目ID
            let local_nav = " &gt; " + menu(local_c_id, "title"); //获取当前栏目名称
            let nav_parent = ""; //父级面包屑
            do {
                if (parent_id > 0) {
                    nav_parent = " &gt; " + menu(parent_id, "title") + nav_parent;
                    parent_id = menu(parent_id, "parentid");
                }
            } while (parent_id > 0)
            nav = nav + nav_parent + local_nav;
        } else {
            if (newsClassPageAry.indexOf(page_name) > -1) {
                let parent_id = news_class(local_id, "parentid"); //获取当前文章分类父级ID；
                let local_nav = " &gt; " + news_class(local_id, "title"); //获取当前文章分类名
                let nav_parent = ""; //父级面包屑
                do {
                    if (parent_id > 0) {
                        nav_parent = " &gt; " + news_class(parent_id, "title") + nav_parent;
                        parent_id = news_class(parent_id, "parentid");
                    }
                } while (parent_id > 0)
                nav = nav + nav_parent + local_nav;
            }

            if (newsPageAry.indexOf(page_name) > -1) {
                let class_id = l_news_info.classid[0]; //获取当前文章分类ID（这里考虑到一篇文章可能对应多个分类，所以只取第一个分类的ID）
                let nav_newsclass = ""; //父级（文章分类）面包屑
                do {
                    if (class_id > 0) {
                        nav_newsclass = " &gt; " + news_class(class_id, "title") + nav_newsclass;
                        class_id = news_class(class_id, "parentid"); //获取当前文章分类的父级ID
                    }
                } while (class_id > 0)
                nav = nav + nav_newsclass;
            }

            if (pagePageAry.indexOf(page_name) > -1) {
                let local_nav = " &gt; " + l_news_info.title; //当在最终页时，获取当前页面（文章）名称
                nav = nav + local_nav;
            }

            if (productClassPageAry.indexOf(page_name) > -1) {
                if (local_id != "" && local_id > 0) {
                    let parent_id = product_class(local_id, "parentid"); //获取当前产品分类父级ID；
                    let local_nav = " &gt; " + product_class(local_id, "title"); //获取当前产品分类名
                    let nav_parent = ""; //父级面包屑
                    do {
                        if (parent_id > 0) {
                            nav_parent = " &gt; " + product_class(parent_id, "title") + nav_parent;
                            parent_id = product_class(parent_id, "parentid");
                        }
                    } while (parent_id > 0)
                    nav = nav + nav_parent + local_nav;
                } else {
                    nav = nav + " &gt; 产品中心";
                }
            }

            if (productPageAry.indexOf(page_name) > -1) {
                let class_id = l_product_info.classid[0]; //获取当前产品分类ID（这里考虑到一个产品可能对应多个分类，所以只取第一个分类的ID）
                let nav_proclass = ""; //父级（产品分类）面包屑
                do {
                    if (class_id > 0) {
                        nav_proclass = " &gt; " + product_class(class_id, "title") + nav_proclass;
                        class_id = product_class(class_id, "parentid"); //获取当前产品分类的父级ID
                    }
                } while (class_id > 0)
                nav = nav + nav_proclass;
            }

            if (searchPageAry.indexOf(page_name) > -1) {
                nav = nav + " &gt; 搜索结果";
            }
        }

        return nav;
    } else {
        return "";
    }
}

// ------------------------------内页相关function 结束------------------------------

// ------------------------------自定义常用function 开始------------------------------
/*判断值是否为空
* @param any value 需要判断是否为空的参数*/
function isEmpty(value) {
    let result = false;
    if (typeof value == 'undefined') {
        result = true;
    } else if (!value) {
        result = true;
    } else if (typeof value == 'string') {
        if (value == "" || value == null || value == 'null') result = true;
    } else if (Array.isArray(value)) {
        if (value.length == 0) result = true;
    }
    return result;
}

/*网站开关*/
function webSwitch() {
    if (web_control.state === "2" && local_path_name !== "/close.html") {
        window.location.href = "/close.html";
    } else if (web_control.state === "1" && local_path_name === "/close.html") {
        window.location.href = "/";
    }
}

//获取当前页面名称-无视目录层级
function getPageName() {
    let pageName = window.location.pathname;

    if (pageName.indexOf(".html") !== -1) {
        pageName = pageName.substring(pageName.lastIndexOf('/') + 1).split('.')[0]; //当页面名存在'.html'时，去除页面名称前面的斜杠'/'和后面的'.html'
    } else if (pageName.charAt(0) === '/') {
        pageName = pageName.substring(1);
    }

    return pageName;
}

//获取meta标签参数
function getParameter(key) {//key参数的关键字
    let metaTag = $('meta[name="' + key + '"]');
    return metaTag.length > 0 ? metaTag.attr('content') : '';
}

//获取链接中指定变量的参数
function getUrlParameter(keys) {
    let url = window.location.href;

    // 处理哈希(#)号，只取#前面的部分
    const hashIndex = url.indexOf('#');
    if (hashIndex !== -1) {
        url = url.substring(0, hashIndex);
    }

    // 获取问号后面的查询参数部分
    const queryIndex = url.indexOf('?');
    if (queryIndex === -1) {
        return "";
    }

    const queryString = url.substring(queryIndex + 1);

    // 使用URLSearchParams API（现代浏览器推荐）
    if (window.URLSearchParams) {
        const urlParams = new URLSearchParams(queryString);
        return urlParams.get(keys) || "";
    }

    // 兼容旧浏览器的正则方法
    const reg = new RegExp("(^|&)" + keys + "=([^&]*)(&|$)");
    const match = queryString.match(reg);

    if (match !== null) {
        return decodeURIComponent(match[2]);
    }

    return "";
}

/*格式化时间
参数time：时间戳
参数type：时间展现形式{YMD=>年-月-日，YMD hms=>年-月-日 时:分:秒}（说明：YMD hms可以单独拿取）*/
function formatTime(time, type) {
    type = type || "YMD";

    //padStart()方法的Polyfill
    if (!String.prototype.padStart) {
        String.prototype.padStart = function (targetLength, padString) {
            // 截断数字或将非数字转换为0
            targetLength = targetLength >> 0;
            padString = String((typeof padString !== 'undefined' ? padString : ' '));
            if (this.length > targetLength || padString === '') {
                return String(this);
            }
            targetLength = targetLength - this.length;
            if (targetLength > padString.length) {
                // 添加到初始值以确保长度足够
                padString += padString.repeat(targetLength / padString.length);
            }
            return padString.slice(0, targetLength) + String(this);
        };
    }

    let timeshow = "";
    let date = new Date(time * 1000);
    let year = date.getFullYear();
    let month = date.getMonth() + 1;
    let day = date.getDate();
    let hour = date.getHours();
    let minute = date.getMinutes();
    let second = date.getSeconds();
    switch (type) {
        case "YMD hms":
            timeshow = year + "-" + month.toString().padStart(2, "0") + "-" + day.toString().padStart(2, "0")
                + " " + hour.toString().padStart(2, "0") + ":" + minute.toString().padStart(2, "0")
                + ":" + second.toString().padStart(2, "0")
            break;
        case "YMD":
            timeshow = year + "-" + month.toString().padStart(2, "0") + "-" + day.toString().padStart(2, "0")
            break;
        case "Y":
            timeshow = year.toString()
            break;
        case "M":
            timeshow = month.toString().padStart(2, "0")
            break;
        case "D":
            timeshow = day.toString().padStart(2, "0")
            break;
        case "h":
            timeshow = hour.toString().padStart(2, "0")
            break;
        case "m":
            timeshow = minute.toString().padStart(2, "0")
            break;
        case "s":
            timeshow = second.toString().padStart(2, "0")
            break;
    }
    return timeshow;
}

/*翻页
参数total：总记录数
参数pagesize：一共有多少页
参数currentpage：当前页码*/
function flippingPages(total, pagesize, currentpage) {
    let page = "";
    let url = "";

    let static_url = window.location.pathname; //获取当前伪静态链接

    if (static_url == "index" || static_url == "/") {
        url = static_url + "?page=";
    } else {
        let param = "";
        param += isEmpty(local_t) ? "" : "&t=" + local_t;
        param += isEmpty(local_k) ? "" : "&k=" + local_k;
        param += isEmpty(local_attr) ? "" : "&attr=" + local_attr;
        param += "&page=";
        param = param.replace('&', '?'); //将第一个"&"转换为"?"
        url = static_url + param;
    }
    // else if (static_url == "search") {
    //         url = static_url + "?t=" + local_t + "&k=" + local_k + "&page=";
    //     }
    if (pagesize > 0) {
        page += "<div class='page'>";
        if (currentpage > 1) {
            page += "<a href='" + url + (currentpage - 1) + "'>&lt; 上一页</a>";
        } else {
            page += "<span class='disabled'>&lt; 上一页</span>";
        }
        if (pagesize > 5) {
            //第一页
            page += currentpage == 1 ? "<span class='current'>1</span>" : "<a href='" + url + "1'>1</a>";
            if (currentpage >= 5) {
                page += "<span class='ellipsis'>...</span>";
                let startpage = 0;  //开始页码
                let endpage = 0;  //结束页码
                let showend = "";
                if (pagesize - 1 > parseInt(currentpage) + 2) {
                    startpage = currentpage - 2;
                    endpage = parseInt(currentpage) + 2; //这里注意，js里面要想计算加法，要将运算值做类型转换（用parseInt()或parseFloat()处理），不然加号会被看成连接符

                    showend = "<span class='ellipsis'>...</span>"; //到没有到最后一页时，输出省略号...
                } else {
                    startpage = pagesize - 4;
                    endpage = pagesize - 1;
                }

                //将页码循环输出
                for (let i = startpage; i <= endpage; i++) {
                    page += (i == currentpage) ? "<span class='current'>" + i + "</span>" : "<a href='" + url + i + "'>" + i + "</a>";
                }
                page += showend;
            } else {
                //将页码循环输出--当当前页码小于5时
                for (let i = 2; i < 6; i++) {
                    page += i == currentpage ? "<span class='current'>" + i + "</span>" : "<a href='" + url + i + "'>" + i + "</a>";
                }
                page += "<span class='ellipsis'>...</span>";
            }
            //最后一页
            page += currentpage == pagesize ? "<span class='current'>" + pagesize + "</span>" : "<a href='" + url + pagesize + "'>" + pagesize + "</a>";
        } else {
            for (let i = 0; i < pagesize; i++) {
                let c_page = i + 1;
                if (c_page == currentpage) {
                    page += "<span class='current'>" + c_page + "</span>";
                } else {
                    page += "<a href='" + url + c_page + "'>" + c_page + "</a>";
                }
            }
        }
        if (currentpage < pagesize) {
            page += "<a href='" + url + (parseInt(currentpage) + 1) + "'>下一页 &gt;</a>";
        } else {
            page += "<span class='disabled'>下一页 &gt;</span>";
        }
        page += "<select onchange='location.href = this.options[this.selectedIndex].value'>";
        for (let i = 0; i < pagesize; i++) {
            let c_page = i + 1;
            if (c_page == currentpage) {
                page += "<option selected>第" + c_page + "页</option>";
            } else {
                page += "<option value='" + url + c_page + "'>第" + c_page + "页</option>";
            }
        }
        page += "</select>";
        page += "</div>";
    }
    return page;
}

/*将带有层级的数组，整理成一个无层级的新数组（将子级跟父级放到同同一层）
* @param any ary 需要处理的数组
* @param any newAry 返回整理好的新数组*/
function arrangeArray(ary, newAry) {
    newAry = newAry || [];
    if (!isEmpty(ary)) {
        ary.forEach((e) => {
            newAry.push(e);
            if (!isEmpty(e.children)) {
                arrangeArray(e.children, newAry);
            }
        })
    }
    return newAry;
}

/*获取导航信息
* @param int id 要获取哪个导航下的子栏目，为0时，返回状态为"显示"的菜单
* @param string field 要获取导航相关字段信息
* @param any menuAry 要处理的导航菜单数据*/
function menu(id, field, menuAry) {
    id = id || 0;
    field = field || '';
    menuAry = menuAry || menu_info; //默认调用整站的导航数据
    let a_menuAry = arrangeArray(menuAry); //将数组的子级都放到第一层级

    if (!isEmpty(field)) {
        for (let i = 0; i < a_menuAry.length; i++) {
            let row = a_menuAry[i];
            if (row.id == id) {
                return row[field];
            }
        }
        return '';
    } else if (id > 0) {
        let menu_ary = [];
        a_menuAry.forEach((e) => {
            if (e.id == id) {
                menu_ary = e.children;
            }
        });
        return menu_ary;
    } else {
        let menu_ary = [];
        for (let i = 0; i < menuAry.length; i++) {
            let row = menuAry[i];
            if (row.children.length > 0) row.children = menu(id, field, row.children);
            if (row.is_show) menu_ary.push(row);
        }
        return menu_ary;
    }
}

/*图片管理-相关位置图片的获取
参数id：图片位置的ID
参数field：要获取的图片信息字段名*/
function pic(id, field) {
    if (id > 0 && !isEmpty(pic_info)) {
        if (!isEmpty(field)) {
            for (let i = 0; i < pic_info.length; i++) {
                let row = pic_info[i];
                if (row.classid == id) return row[field];
            }
            return '';
        } else {
            let info = [];
            for (let i = 0; i < pic_info.length; i++) {
                let row = pic_info[i];
                if (row.classid == id) info.push(row);
            }
            return info;
        }
    }
    return '';
}

/*按需获取文章分类信息
* @param int id 要获取信息的文章分类ID，为0时，获取所有分类
* @param string field 要获取的文章分类字段值
* @param int top 显示文章分类的条数
* @param any newsClassAry 要处理的文章分类数据*/
function news_class(id, field, top, newsClassAry) {
    id = id || 0;
    field = field || '';
    top = top || 0;
    newsClassAry = newsClassAry || news_class_info;
    let a_newsClassAry = arrangeArray(newsClassAry); //将数组的子级都放到第一层级

    if (!isEmpty(field)) { //获取指定文章分类的相关字段值
        for (let i = 0; i < a_newsClassAry.length; i++) {
            let row = a_newsClassAry[i];
            if (row.id == id) {
                return row[field];
            }
        }
        return '';
    } else if (id > 0) {
        let news_class_ary = [];
        a_newsClassAry.forEach((e) => {
            if (e.id == id) {
                news_class_ary = e.children;
                if (top > 0) news_class_ary = news_class_ary.slice(0, top);
            }
        });
        return news_class_ary;
    } else {
        let news_class_ary = [];
        for (let i = 0; i < newsClassAry.length; i++) {
            let row = newsClassAry[i];
            if (row.children.length > 0) row.children = news_class(id, field, top, row.children);
            news_class_ary.push(row);
        }
        return news_class_ary;
    }
}

/*按需获取产品分类信息
* @param int id 要获取信息的产品分类ID，为0时，获取所有分类
* @param string field 要获取的产品分类字段值
* @param int top 显示产品分类的条数
* @param any productClassAry 要处理的产品分类数据*/
function product_class(id, field, top, productClassAry) {
    id = id || 0;
    field = field || '';
    top = top || 0;
    productClassAry = productClassAry || product_class_info;
    let a_productClassAry = arrangeArray(productClassAry); //将数组的子级都放到第一层级

    if (!isEmpty(field)) { //获取指定产品分类的相关字段值
        for (let i = 0; i < a_productClassAry.length; i++) {
            let row = a_productClassAry[i];
            if (row.id == id) {
                return row[field];
            }
        }
        return '';
    } else if (id > 0) {
        let product_class_ary = [];
        a_productClassAry.forEach((e) => {
            if (e.id == id) {
                product_class_ary = e.children;
                if (top > 0) product_class_ary = product_class_ary.slice(0, top);
            }
        });
        return product_class_ary;
    } else if (id == 0 && top > 0) {
        let product_class_ary = [];
        product_class_ary = productClassAry.slice(0, top);
        return product_class_ary;
    } else {
        let product_class_ary = [];
        for (let i = 0; i < productClassAry.length; i++) {
            let row = productClassAry[i];
            if (row.children.length > 0) row.children = product_class(id, field, top, row.children);
            product_class_ary.push(row);
        }
        return product_class_ary;
    }
}

// ------------------------------自定义常用function 结束------------------------------

// ------------------------------接口数据请求 开始------------------------------
// 网站全局数据
function getGlobal() {
    let info = webforceAjax('global.php');
    info = info.code == 200 ? info.obj.data : '';
    return info;
}

/*获取文章列表信息
参数id：文章分类ID（为空时，显示所有文章）
参数top：显示文章的个数*/
function getNewsList(id, top) {
    top = top || -1;
    let info = webforceAjax('news_list.php', 'POST', { id, top });
    info = info.code == 200 ? info.obj.data : '';
    return info;
}

/*单篇文章信息的获取
* @param int id 要获取信息的文章ID*/
function getNewsInfo(id) {
    let info = webforceAjax('news_detail.php', 'POST', { id });
    info = info.code == 200 ? info.obj.data : '';
    return info;
}

/*获取产品信息
参数id：产品分类ID（为空时，显示所有产品）
参数top：显示产品的个数*/
function getProductList(id, top) {
    top = top || -1;
    let info = webforceAjax('product_list.php', 'POST', { id, top });
    info = info.code == 200 ? info.obj.data : '';
    return info;
}

/*单个产品信息的获取
* @param int id 要获取信息的产品ID*/
function getProductInfo(id) {
    let info = webforceAjax('product_detail.php', 'POST', { id });
    info = info.code == 200 ? info.obj.data : '';
    return info;
}

// 流量统计
function trafficStatistics() {
    let url_referrer = document.referrer; //网站的来源页面
    let url = document.URL; //当前页面

    let info = webforceAjax('traffic_statistics_update.php', 'POST', { url_referrer, url });
    info = info.code == 200 ? info.obj : '';
    return info;
}

// 文章列表请求队列
var requestNewsList = requestNewsList || [];
var timeNewsList = timeNewsList || null;

/*文章展示-升级版-多接口请求合并
参数id：文章分类ID（为空时，显示所有文章）
参数top：显示文章的个数，-1表示显示所有相关文章
参数type：文章列表展现形式*/
function wf_news_list(id, top, type) {
    top = top || -1;
    type = type || 1;

    // 确保队列存在
    if (!requestNewsList) {
        requestNewsList = [];
    }
    if (!timeNewsList) {
        timeNewsList = null;
    }

    // 创建延迟对象
    var deferred = $.Deferred();
    // 将请求添加到队列
    requestNewsList.push({
        id: id,
        top: top,
        type: type,
        deferred: deferred
    });

    // 清除现有计时器，重新设置延迟执行
    clearTimeout(timeNewsList);
    timeNewsList = setTimeout(function () {
        // 复制当前的队列，然后清空队列
        var currentQueue = requestNewsList.slice();
        requestNewsList = [];

        // 提取参数列表
        var paramList = currentQueue.map(function (item) {
            return {
                id: item.id,
                top: item.top,
                type: item.type
            };
        });

        let data = { param: JSON.stringify(paramList) };
        // 发送AJAX请求
        $.ajax({
            url: `${url_host}${api_entrance}news_lists.php`,
            type: 'POST',
            data: data,
            traditional: true,
            datatype: "json",
            success: function (response) {
                // 假设后端返回的是一个数组，按照请求的顺序返回了结果
                let info = response.obj.data;

                for (let i = 0; i < info.length; i++) {
                    // 根据类型调用对应的展示方法
                    let rk = info[i]["request_key"]; //请求参数
                    let type = rk.substring(rk.lastIndexOf('|') + 1); //展示方式
                    let newsData = info[i]["list"];

                    var functionName = 'newsShow' + type;
                    let html = "";
                    if (typeof window[functionName] === 'function') {
                        html = window[functionName](newsData);
                    } else {
                        // 默认处理
                        html = newsShow1(newsData);
                    }

                    // 同时仍然resolve deferred对象，以便外部可以使用
                    currentQueue[i].deferred.resolve(html);
                }
            },
            error: function (xhr, status, error) {
                // 对于每个deferred对象，都reject
                for (var i = 0; i < currentQueue.length; i++) {
                    currentQueue[i].deferred.reject(xhr, status, error);
                }
            }
        });
    }, 100);

    // 返回promise对象
    return deferred.promise();
}

// 文章详情请求队列
var requestNewsDetail = requestNewsDetail || [];
var timeNewsDetail = timeNewsDetail || null;

/*文章详情-升级版-多接口请求合并
参数id：文章ID
参数type：文章详情展现形式*/
function wf_news_detail(id, type) {
    type = type || 1;

    // 确保队列存在
    if (!requestNewsDetail) {
        requestNewsDetail = [];
    }
    if (!timeNewsDetail) {
        timeNewsDetail = null;
    }

    // 创建延迟对象
    var deferred = $.Deferred();
    // 将请求添加到队列
    requestNewsDetail.push({
        id: id,
        type: type,
        deferred: deferred
    });

    // 清除现有计时器，重新设置延迟执行
    clearTimeout(timeNewsDetail);
    timeNewsDetail = setTimeout(function () {
        // 复制当前的队列，然后清空队列
        var currentQueue = requestNewsDetail.slice();
        requestNewsDetail = [];

        // 提取参数列表
        var paramList = currentQueue.map(function (item) {
            return {
                id: item.id,
                type: item.type
            };
        });

        let data = { param: JSON.stringify(paramList) };
        // 发送AJAX请求
        $.ajax({
            url: `${url_host}${api_entrance}news_details.php`,
            type: 'POST',
            data: data,
            traditional: true,
            datatype: "json",
            success: function (response) {
                // 假设后端返回的是一个数组，按照请求的顺序返回了结果
                let info = response.obj.data;

                for (let i = 0; i < info.length; i++) {
                    let newsData = info[i];

                    // 同时仍然resolve deferred对象，以便外部可以使用
                    currentQueue[i].deferred.resolve(newsData);
                }
            },
            error: function (xhr, status, error) {
                // 对于每个deferred对象，都reject
                for (var i = 0; i < currentQueue.length; i++) {
                    currentQueue[i].deferred.reject(xhr, status, error);
                }
            }
        });
    }, 100);

    // 返回promise对象
    return deferred.promise();
}

// 产品列表请求队列
var requestProductList = requestProductList || [];
var timeProductList = timeProductList || null;

/*产品展示-升级版-多接口请求合并
参数id：产品分类ID（为空时，显示所有产品）
参数top：显示产品的个数，-1表示显示所有相关文章
参数type：产品列表展现形式*/
function wf_product_list(id, top, type) {
    id = id || 0;
    top = top || -1;
    type = type || 1;

    // 确保队列存在
    if (!requestProductList) {
        requestProductList = [];
    }
    if (!timeProductList) {
        timeProductList = null;
    }

    // 创建延迟对象
    var deferred = $.Deferred();
    // 将请求添加到队列
    requestProductList.push({
        id: id,
        top: top,
        type: type,
        deferred: deferred
    });

    // 清除现有计时器，重新设置延迟执行
    clearTimeout(timeProductList);
    timeProductList = setTimeout(function () {
        // 复制当前的队列，然后清空队列
        var currentQueue = requestProductList.slice();
        requestProductList = [];

        // 提取参数列表
        var paramList = currentQueue.map(function (item) {
            return {
                id: item.id,
                top: item.top,
                type: item.type
            };
        });

        let data = { param: JSON.stringify(paramList) };
        // 发送AJAX请求
        $.ajax({
            url: `${url_host}${api_entrance}product_lists.php`,
            type: 'POST',
            data: data,
            traditional: true,
            datatype: "json",
            success: function (response) {
                // 假设后端返回的是一个数组，按照请求的顺序返回了结果
                let info = response.obj.data;

                for (let i = 0; i < info.length; i++) {
                    // 根据类型调用对应的展示方法
                    let rk = info[i]["request_key"]; //请求参数
                    let type = rk.substring(rk.lastIndexOf('|') + 1); //展示方式
                    let productData = info[i]["list"];

                    var functionName = 'proShow' + type;
                    let html = "";
                    if (typeof window[functionName] === 'function') {
                        html = window[functionName](productData);
                    } else {
                        // 默认处理
                        html = proShow1(productData);
                    }

                    // 同时仍然resolve deferred对象，以便外部可以使用
                    currentQueue[i].deferred.resolve(html);
                }
            },
            error: function (xhr, status, error) {
                // 对于每个deferred对象，都reject
                for (var i = 0; i < currentQueue.length; i++) {
                    currentQueue[i].deferred.reject(xhr, status, error);
                }
            }
        });
    }, 100);

    // 返回promise对象
    return deferred.promise();
}

// 产品详情请求队列
var requestProductDetail = requestProductDetail || [];
var timeProductDetail = timeProductDetail || null;

/*产品详情-升级版-多接口请求合并
参数id：产品ID
参数type：文章详情展现形式*/
function wf_product_detail(id, type) {
    type = type || 1;

    // 确保队列存在
    if (!requestProductDetail) {
        requestProductDetail = [];
    }
    if (!timeProductDetail) {
        timeProductDetail = null;
    }

    // 创建延迟对象
    var deferred = $.Deferred();
    // 将请求添加到队列
    requestProductDetail.push({
        id: id,
        type: type,
        deferred: deferred
    });

    // 清除现有计时器，重新设置延迟执行
    clearTimeout(timeProductDetail);
    timeProductDetail = setTimeout(function () {
        // 复制当前的队列，然后清空队列
        var currentQueue = requestProductDetail.slice();
        requestProductDetail = [];

        // 提取参数列表
        var paramList = currentQueue.map(function (item) {
            return {
                id: item.id,
                type: item.type
            };
        });

        let data = { param: JSON.stringify(paramList) };
        // 发送AJAX请求
        $.ajax({
            url: `${url_host}${api_entrance}product_details.php`,
            type: 'POST',
            data: data,
            traditional: true,
            datatype: "json",
            success: function (response) {
                // 假设后端返回的是一个数组，按照请求的顺序返回了结果
                let info = response.obj.data;

                for (let i = 0; i < info.length; i++) {
                    let newsData = info[i];

                    // 同时仍然resolve deferred对象，以便外部可以使用
                    currentQueue[i].deferred.resolve(newsData);
                }
            },
            error: function (xhr, status, error) {
                // 对于每个deferred对象，都reject
                for (var i = 0; i < currentQueue.length; i++) {
                    currentQueue[i].deferred.reject(xhr, status, error);
                }
            }
        });
    }, 100);

    // 返回promise对象
    return deferred.promise();
}

/*封装ajax请求
* @param string url 请求接口
* @param string type 请求方式
* @param any data 请求数据
* @param bool async 请求数据*/
function webforceAjax(url, type, data, async) {
    url = api_entrance + url || '';
    type = type || 'GET';
    data = data || '';
    async = async || false;
    let info = '';

    if (!isEmpty(url)) {
        $.ajax({
            url: url_host + url, //接口地址
            type: type, //传值方式
            data: data,
            datatype: "json",
            async: async, // 设置同步方式
            success: function (res) {
                info = res;
            },
            error: function () {
                console.log(url + " error"); //输出报错接口
            }
        });
    } else {
        console.log("无效的接口参数！");
    }

    return info;
}

// ------------------------------接口数据请求 结束------------------------------