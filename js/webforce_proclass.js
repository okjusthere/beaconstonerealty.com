/*文章列表
参数displays：每页显示的文章分类个数
参数type：要展现的样式
参数currentpage：当前页码
参数id：文章分类ID，要读取多个分类下的文章以数组的形式传入*/
function proclass_inner(displays, type, currentpage, id) {
    displays = displays || 10;
    type = type || 1;
    currentpage = currentpage || 1;
    id = id || 0;

    let proclasslist = ""; //最终返回给前端的内容

    if (id == 0 && local_id != "") {
        id = local_id;
    }
    if (local_page != "") { //当前页码不为空时，给currentpage赋值
        currentpage = local_page;
    }
    let proclass = getInnerProClass(id, displays, currentpage);

    if (proclass.data.length > 0) {
        // 动态调用 proClassInnerShow + 数字
        if (typeof window[`proClassInnerShow${type}`] === 'function') {
            proclasslist = window[`proClassInnerShow${type}`](proclass.data);
        } else {
            proclasslist = proClassInnerShow1(proclass.data);
        }
    }

    let total = proclass.total; //信息总条数
    let pagesize = proclass.pagesize; //总共有多少页
    let current_page = proclass.currentpage; //当前页码
    if (pagesize > 1) {
        proclasslist += flippingPages(total, pagesize, current_page); //翻页
    }

    return proclasslist;
}

/*文章分类展示形式一
参数proClassAry：接口返回的文章分类相关数据*/
function proClassInnerShow1(proClassAry) {
    let proclassshow = "<div class='inside_proClass1'><ul>";
    for (let i = 0; i < proClassAry.length; i++) {
        let id = proClassAry[i]["id"]; //文章ID
        let title = proClassAry[i]["title"]; //文章标题
        let keywords = proClassAry[i]["keywords"]; //关键词
        let description = proClassAry[i]["description"]; //文章描述
        let thumbnail = proClassAry[i]["thumbnail"]; //缩略图
        let enclosure = proClassAry[i]["enclosure"]; //附件
        let photo_album = proClassAry[i]["photo_album"]; //图片相册
        let add_time = formatTime(proClassAry[i]["add_time"]); //文章分类添加日期
        let children = proClassAry[i]["children"]; //子栏目
        let url = proClassAry[i]["url"]; //链接
        if (children > 0) {
            url = `/proclass/${id}`;
        }

        proclassshow += "<li><a href='" + url + "' title='" + title + "'>" +
            "<div class='nr_box'>" +
            "<div class='imgbg'>" +
            "<div class='img'><img src='" + thumbnail + "' alt='" + title + "'/></div>" +
            "</div>" +
            "<div class='txtbg'>" +
            "<div class='txt'>" +
            "<div class='h2'>" + title + "</div>" +
            "<div class='h3'>" + description + "</div>" +
            "<div class='imore1'><span>MORE</span></div>" +
            "</div>" +
            "</div>" +
            "</div>" +
            "</a></li>";
    }
    proclassshow += "</ul><div class='clear'></div></div>";

    return proclassshow;
}

/*获取产品分类信息
参数id：产品分类ID（为空时，显示所有产品分类）
参数displays：每页显示产品分类的个数
参数currentpage：当前页码*/
function getInnerProClass(id, displays, currentpage) {
    displays = displays || 10;
    currentpage = currentpage || 1;
    let info = [];
    info = webforceAjax("inner_proclass.php", "POST", {
        "id": id,
        "displays": displays,
        "currentpage": currentpage
    })
    info = info.code == 200 ? info.obj : [];
    return info;
}
