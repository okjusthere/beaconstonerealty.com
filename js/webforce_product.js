/*产品列表
参数displays：每页显示的产品个数
参数type：要展现的样式（默认有1，2，3三种样式，0代表自定义样式）若有多个样式，用大于3的数字定义，-1表示使用后台选择的样式
参数currentpage：当前页码
参数id：文章分类ID，要读取多个分类下的产品以数组的形式传入*/
function product_inner(displays, type, currentpage, id) {
    displays = displays || 12;
    type = type || -1;
    currentpage = currentpage || 1;
    id = id || 0;

    let prolist = ""; //最终返回给前端的内容
    if (id == 0 && local_id != "") {
        id = local_id;
    }
    if (type === -1) {
        if (id > 0) {
            type = product_class(id, 'show_type');
        } else {
            proclass_l = product_class_info.length; //产品分类个数
            if (proclass_l > 0) {
                proclass_l = proclass_l - 1;
                type = product_class_info[proclass_l]['show_type'];
            }
        }
    }
    if (local_page != "") { //当前页码不为空时，给currentpage赋值
        currentpage = local_page;
    }
    let product = getInnerProduct(id, displays, currentpage);

    if (JSON.stringify(product) != "[]" && JSON.stringify(product) != "{}") {
        if (product.data.length > 0) {
            // 动态调用 proInnerShow + 数字
            if (typeof window[`proInnerShow${type}`] === 'function') {
                prolist = window[`proInnerShow${type}`](product.data);
            } else {
                prolist = proInnerShow1(news.data);
            }

            let total = product.total; //信息总条数
            let pagesize = product.pagesize; //总共有多少页
            let current_page = product.currentpage; //当前页码
            if (pagesize > 1) {
                prolist += flippingPages(total, pagesize, current_page); //翻页
            }
        } else {
            prolist = "<p style='color: #d5d7de'>暂无记录</p>";
        }
    } else {
        prolist = "<p style='color: #d5d7de'>您没有访问该信息的权限</p>";
    }

    return prolist;
}

/*产品展示形式-自定义
参数proAry：产品数据*/
function proInnerShow0(proAry) {
    let proshow = "<div class='inside_product0'><ul>";

    for (let i = 0; i < proAry.length; i++) {
        let id = proAry[i]["id"]; //产品ID
        let title = proAry[i]["title"]; //产品名
        let url = proAry[i]["url"]; //链接
        let specifications = proAry[i]["specifications"]; //产品规格
        let origin = proAry[i]["origin"]; //产品产地
        let price = proAry[i]["price"]; //产品价格
        let keywords = proAry[i]["keywords"]; //关键词
        let description = proAry[i]["description"]; //产品描述
        let thumbnail = proAry[i]["thumbnail"]; //缩略图
        let photo_album = proAry[i]["photo_album"]; //图片相册
        let add_time = formatTime(proAry[i]["add_time"]); //产品发布日期

        proshow += "<li><a href='" + url + "' title='" + title + "'>" +
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

/*产品展示形式一
参数proAry：产品数据*/
function proInnerShow1(proAry) {
    let proshow = "<div class='inside_product1'><ul>";

    for (let i = 0; i < proAry.length; i++) {
        let id = proAry[i]["id"]; //产品ID
        let title = proAry[i]["title"]; //产品名
        let url = proAry[i]["url"]; //链接
        let specifications = proAry[i]["specifications"]; //产品规格
        let origin = proAry[i]["origin"]; //产品产地
        let price = proAry[i]["price"]; //产品价格
        let keywords = proAry[i]["keywords"]; //关键词
        let description = proAry[i]["description"]; //产品描述
        let thumbnail = proAry[i]["thumbnail"]; //缩略图
        let photo_album = proAry[i]["photo_album"]; //图片相册
        let field = proAry[i]["field"]; //自定义字段
        let add_time = formatTime(proAry[i]["add_time"]); //产品发布日期

        proshow += "<li><a href='" + url + "' title='" + title + "'>" +
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

/*产品展示形式二
参数proAry：产品数据*/
function proInnerShow2(proAry) {
    let proshow = "<div class='inside_product2'><ul>";

    for (let i = 0; i < proAry.length; i++) {
        let id = proAry[i]["id"]; //产品ID
        let title = proAry[i]["title"]; //产品名
        let url = proAry[i]["url"]; //链接
        let specifications = proAry[i]["specifications"]; //产品规格
        let origin = proAry[i]["origin"]; //产品产地
        let price = proAry[i]["price"]; //产品价格
        let keywords = proAry[i]["keywords"]; //关键词
        let description = proAry[i]["description"]; //产品描述
        let thumbnail = proAry[i]["thumbnail"]; //缩略图
        let photo_album = proAry[i]["photo_album"]; //图片相册
        let add_time = formatTime(proAry[i]["add_time"]); //产品发布日期

        proshow += "<li><a href='" + url + "' title='" + title + "'>" +
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

/*产品展示形式三
参数proAry：产品数据*/
function proInnerShow3(proAry) {
    let proshow = "<div class='inside_product3'><ul>";

    for (let i = 0; i < proAry.length; i++) {
        let id = proAry[i]["id"]; //产品ID
        let title = proAry[i]["title"]; //产品名
        let url = proAry[i]["url"]; //链接
        let specifications = proAry[i]["specifications"]; //产品规格
        let origin = proAry[i]["origin"]; //产品产地
        let price = proAry[i]["price"]; //产品价格
        let keywords = proAry[i]["keywords"]; //关键词
        let description = proAry[i]["description"]; //产品描述
        let thumbnail = proAry[i]["thumbnail"]; //缩略图
        let photo_album = proAry[i]["photo_album"]; //图片相册
        let add_time = formatTime(proAry[i]["add_time"]); //产品发布日期

        proshow += "<li><a href='" + url + "' title='" + title + "'>" +
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

/*获取产品信息
参数id：产品分类ID（为空时，显示所有文章）
参数displays：每页显示产品的个数
参数currentpage：当前页码*/
function getInnerProduct(id, displays, currentpage) {
    displays = displays || 12;
    currentpage = currentpage || 1;

    let info = [];
    info = webforceAjax("inner_product.php", "POST", {
        "id": id,
        "displays": displays,
        "currentpage": currentpage,
        "local_attr": local_attr
    });
    info = info.code == 200 ? info.obj : [];
    return info;
}

//展示产品筛选列表
function showProductScreen(type) {
    type = type || 1;
    let showInfo = "";
    let proscreen = product_screen(); //获取产品属性信息
    if (JSON.stringify(proscreen) != "[]" && JSON.stringify(proscreen) != "{}") {
        if (proscreen.data.length > 0) {
            switch (type) {
                case 1:
                    showInfo = proScreenShow1(proscreen.data)
                    break;
            }
        }
    }
    return showInfo;
}

//产品筛选展示一
function proScreenShow1(proscreen) {
    let showInfo = "";

    showInfo += "<ul>";
    for (let i = 0; i < proscreen.length; i++) {
        let id = proscreen[i]["id"]; //属性分类ID
        let parentid = proscreen[i]["parentid"]; //父级分类ID
        let title = proscreen[i]["title"]; //属性分类名称
        let screen_type = proscreen[i]["screen_type"]; //该属性是多选还是单选
        let children = proscreen[i]["children"]; //属性分类对应的子分类
        let attribute = proscreen[i]["attribute"]; //属性分类对应的属性值

        let time = 0 //匹配hover的次数，如果是单选，只能匹配一次
        let l_attr = local_attr.split(","); //将当前属性参数转化成数组，便于处理

        showInfo += "<li>" + title + "：";
        for (let v = 0; v < attribute.length; v++) {
            let v_id = attribute[v]["id"]; //属性值ID
            let v_value = attribute[v]["value"]; //属性值名称
            let strClass = ""; //选中后的class
            if (l_attr.includes(v_id.toString()) && time == 0) {
                strClass = " class='hover'";
                if (screen_type == 1) time++;
            }

            showInfo += "<a" + strClass + " href='javascript:void(0)' data-id='" + v_id + "' onclick='screen(" + v_id + "," + screen_type + ",$(this))'>" + v_value + "</a>&nbsp;&nbsp;";
        }
        if (children.length > 0) {
            showInfo += proScreenShow1(children);
        }
        showInfo += "</li>";
    }
    showInfo += "</ul>";
    return showInfo;
}

//对筛选情况进行组合
function screen(id, screen_type, valueClick) {
    let url = window.location.href;
    if (url.indexOf('?') != -1) {
        if (url.indexOf("attr=") == -1) {
            url = url + "&attr=" + id;
        } else {
            if (!isEmpty(local_attr)) {
                let attr = local_attr.split(","); //将当前属性参数转化成数组，便于处理
                let newAttr = new Array(); //存放处理好的ID数组
                //如果当前属性筛选是单选时，查看有没有同级别的其他属性值ID被选中，如果有，移除掉同级别的ID
                if (screen_type == 1) {
                    let hover_id = $(valueClick).parent().find(".hover").attr("data-id"); //当前被选中的属性值ID
                    console.log(hover_id != id);
                    if (hover_id != id.toString()) {
                        newAttr = handleAttrValue(attr, hover_id);
                        newAttr.push(id.toString());
                    } else {
                        newAttr = handleAttrValue(attr, id);
                    }
                } else {
                    newAttr = handleAttrValue(attr, id.toString());
                }
                url = replaceAttr(newAttr.toString());
            } else {
                url = replaceAttr(id);
            }
        }
    } else {
        url = url + "?attr=" + id;
    }

    window.location.href = url;
}

/*对筛选数据进行增删操作
* @param array attr：当前连接中的属性ID组成的数组
* @param string del_id：要带入判断的数据*/
function handleAttrValue(attr, det_id) {
    let newAttr = new Array();
    let mate = 0; //检查当前点击的id是否已经存在
    attr.forEach((e) => {
        if (e != det_id) {  //当点击的ID已经存在时，移除ID，否则添加ID
            newAttr.push(e);
        } else {
            mate++;
        }
    });
    if (mate == 0 && !isEmpty(det_id)) newAttr.push(det_id);
    return newAttr;
}

//替换连接中attr的值
function replaceAttr(newAttr) {
    let url = new URL(window.location.href);
    let search = url.search; // 获取查询字符串
    let regex = new RegExp("(attr)=([^&]*)", "g"); // 正则表达式用于匹配param参数和它的值
    search = search.replace(regex, "$1=" + newAttr); // 用新的值替换匹配的部分
    url.search = search; // 更新URL
    return url.href;
}

//获取产品筛选数据
function product_screen() {
    let info = [];
    info = webforceAjax("product_screen.php");
    info = info.code == 200 ? info.obj : [];
    return info;
}
