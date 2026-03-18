/*文章列表
参数displays：每页显示的文章分类个数
参数type：要展现的样式
参数currentpage：当前页码
参数id：文章分类ID，要读取多个分类下的文章以数组的形式传入*/
function newsclass_inner(displays, type, currentpage, id) {
    displays = displays || 10;
    type = type || 1;
    currentpage = currentpage || 1;
    id = id || 0;

    let newsclasslist = ""; //最终返回给前端的内容

    if (id == 0 && local_id != "") {
        id = local_id;
    }
    if (local_page != "") { //当前页码不为空时，给currentpage赋值
        currentpage = local_page;
    }
    let newsclass = getInnerNewsClass(id, displays, currentpage);

    if (newsclass.data.length > 0) {
        // 动态调用 newsClassInnerShow + 数字
        if (typeof window[`newsClassInnerShow${type}`] === 'function') {
            newsclasslist = window[`newsClassInnerShow${type}`](newsclass.data);
        } else {
            newsclasslist = newsClassInnerShow1(newsclass.data);
        }
    }

    let total = newsclass.total; //信息总条数
    let pagesize = newsclass.pagesize; //总共有多少页
    let current_page = newsclass.currentpage; //当前页码
    if (pagesize > 1) {
        newsclasslist += flippingPages(total, pagesize, current_page); //翻页
    }

    return newsclasslist;
}

/*文章分类展示形式一
参数newsClassAry：接口返回的文章分类相关数据*/
function newsClassInnerShow1(newsClassAry) {
    let newsclassshow = "<div class='inside_newsClass1'><ul>";
    for (let i = 0; i < newsClassAry.length; i++) {
        let id = newsClassAry[i]["id"]; //文章分类ID
        let title = newsClassAry[i]["title"]; //文章分类标题
        let description = newsClassAry[i]["description"]; //文章分类描述
        let thumbnail = newsClassAry[i]["thumbnail"]; //缩略图
        let banner = newsClassAry[i]["banner"]; //文章分类通栏图片
        let content = newsClassAry[i]["content"]; //文章分类详情内容
        let add_time = formatTime(newsClassAry[i]["add_time"]); //文章分类添加日期
        let children = newsClassAry[i]["children"]; //子栏目
        let url = "newsclass.html?id=" + id;
        if (children == "0") {
            url = "news.html?id=" + id;
        }
        let news = getNewsList(id, 10); //获取该分类下的文章信息

        newsclassshow += "<div class='inside_newsClass1_box'>" +
            "<div class='itit1'>" +
            "<div class='h2'><strong>" + title + "</strong><a href='" + url + "'>MORE&gt;&gt;</a></div>" +
            "</div>" +
            "<div class='ilist'>";
        if (news.length > 0) {
            newsclassshow += "<ul>";
            for (let i = 0; i < news.length; i++) {
                let news_id = news[i]["id"]; //文章ID
                let news_title = news[i]["title"]; //文章标题
                let news_url = news[i]["url"]; //链接地址
                let news_keywords = news[i]["keywords"]; //关键词
                let news_description = news[i]["description"]; //文章描述
                let news_thumbnail = news[i]["thumbnail"]; //缩略图
                let news_enclosure = news[i]["enclosure"]; //附件
                let news_photo_album = news[i]["photo_album"]; //图片相册
                let news_add_time = formatTime(news[i]["add_time"]); //文章发布日期
                let target = (url.indexOf("http") != -1) ? "' target='_blank" : ""; //打开方式

                newsclassshow += "<li><a href='" + news_url + target + "' title='" + news_title + "'>" + news_title + "</a><span>" + news_add_time + "</span></li>";
            }
            newsclassshow += "</ul>";
        }

        newsclassshow += "<div class='clear'></div>" +
            "</div>" +
            "</div>";
    }
    newsclassshow += "</div>";

    return newsclassshow;
}

/*获取文章信息
参数id：文章分类ID（为空时，显示所有文章）
参数displays：每页显示文章的个数
参数currentpage：当前页码*/
function getInnerNewsClass(id, displays, currentpage) {
    displays = displays || 10;
    currentpage = currentpage || 1;

    let info = [];

    let result = webforceAjax('inner_newsclass.php', 'POST', {"id": id, "displays": displays, "currentpage": currentpage});
    info = result.code == 200 ? result.obj : [];

    return info;
}
