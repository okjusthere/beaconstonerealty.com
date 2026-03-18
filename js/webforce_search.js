$(document).ready(function () {
    search(local_k, local_t); //默认执行搜索

    $("#search").click(function () {
        let s_type = $("#search_type").val(); //要搜索的内容{1/产品；2/文章}
        let s_keywords = $("#search_keywords").val().trim(); //搜索关键词
        if (s_keywords == "" || s_keywords == null) {
            showModal("warning", "请填写关键词！")
            $("#search_keywords").focus();
            return false;
        }
        window.location.href = `/search?t=${s_type}&k=${s_keywords}`;
    });

    $("#search2").click(function () {
        let s_type = $("#search_type2").val(); //要搜索的内容{1/产品；2/文章}
        let s_keywords = $("#search_keywords2").val().trim(); //搜索关键词
        if (s_keywords == "" || s_keywords == null) {
            showModal("warning", "请填写关键词！")
            $("#search_keywords2").focus();
            return false;
        }
        window.location.href = `/search?t=${s_type}&k=${s_keywords}`;
    });
})

/*产品/文章搜索
* 参数keywords：搜索关键词
* 参数type：搜索的是文章还是产品（1/产品；2/文章）
* 参数currentpage：当前页码*/
function search(keywords, type, currentpage) {
    currentpage = currentpage || 1

    let search_list = "";

    if (local_page != "") { //当前页码不为空时，给currentpage赋值
        currentpage = local_page;
    }

    if (type == "") { //搜索类型为空的时候，赋值1，默认搜索产品
        type = "1";
    }

    let infoAry = getInnerSearch(keywords, type, 12, currentpage);
    // console.log(infoAry);
    // console.log(infoAry.data.length);

    if (JSON.stringify(infoAry) != "{}" && JSON.stringify(infoAry) != "[]") {
        if (infoAry.total > 0) {
            switch (type) {
                case "1":
                    search_list = productSearchShow(infoAry.data)
                    break;
                case "2":
                    search_list = newsSearchShow(infoAry.data)
                    break;
            }

            let total = infoAry.total; //信息总条数
            let pagesize = infoAry.pagesize; //总共有多少页
            let current_page = infoAry.currentpage; //当前页码
            if (pagesize > 1) {
                search_list += flippingPages(total, pagesize, current_page); //翻页
            }
        } else {
            search_list = "暂无结果";
        }
    } else {
        search_list = "暂无结果";
    }

    $(".search_content").html(search_list);
}

/*产品展示形式
参数proAry：接口返回的产品数据*/
function productSearchShow(proAry) {
    let proshow = "<div class='inside_product1'><ul>";
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
        let url = "prodetail.html?id=" + id;

        proshow += "<li><a href='" + url + "' title='" + title + "' target='_blank'>" +
            "<div class='nr_box'>" +
            "<div class='imgbg'>" +
            "<div class='img'><img src='" + thumbnail + "' alt='" + title + "'/></div>" +
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


/*文章展示形式
参数newsAry：接口返回的文章相关数据*/
function newsSearchShow(newsAry) {
    let newsshow = "<div class='inside_news1'><ul>";
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
        if (url == "" || url == null) {
            url = "newsdetail.html?id=" + id;
        }

        newsshow += "<li><a href='" + url + "' title='" + title + "' target='_blank'>" +
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

/*搜索
* 参数keywords：搜索关键词
* 参数type：搜索的是文章还是产品（1/产品；2/文章）
* 参数displays：每页显示产品的个数
* 参数currentpage：当前页码
* */
function getInnerSearch(keywords, type, displays, currentpage) {
    keywords = keywords || "";
    type = type || "1";
    displays = displays || 12;
    currentpage = currentpage || 1;

    let info = [];
    interface_url = "inner_product_search.php";
    if (type == "2") {
        interface_url = "inner_news_search.php";
        displays = 10; //搜索文章时，一页显示十条
    }
    // let id=1; //产品分类或文章分类的ID，如果想读取具体分类下的产品或文章，可给id赋值（id也可以输数组，如[1,2]）,然后将id填入下方的data中
    if (!isEmpty(keywords)) {
        info = webforceAjax(interface_url, 'POST', {
            "keywords": keywords,
            "displays": displays,
            "currentpage": currentpage
        });
        info = info.code == 200 ? info.obj : [];
    }
    return info;
}