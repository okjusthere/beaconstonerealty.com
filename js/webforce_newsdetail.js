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

        showinfo = "<div class='m11_newsdetail'>" +
            "<div class='tit'><div class='h2'>" + l_news_info.title + "</div><div class='h3'><span>发布日期：" + formatTime(l_news_info.add_time) + "</span><span>阅读量：" + view + "</span></div></div>" +
            "<div class='list1'>" +
            "<div class='xiangqing'>" + l_news_info.content + "</div>" +
            "</div>" +
            "<div class='prev_next'>";

        if (l_news_info.prev_id > 0) {
            showinfo += "<a href='" + l_news_info.prev_url + "'>上一篇：" + l_news_info.prev_title + "</a>";
        } else {
            showinfo += "<a href='javascript:void(0)'>上一篇：没有了</a>";
        }

        if (l_news_info.next_id > 0) {
            showinfo += "<a href='" + l_news_info.next_url + "'>下一篇：" + l_news_info.next_title + "</a>";
        } else {
            showinfo += "<a href='javascript:void(0)'>下一篇：没有了</a>";
        }
        showinfo += "</div>" +
            "</div>";
    } else {
        showinfo = "<p style='color: #d5d7de'>您没有访问该信息的权限</p>";
    }

    return showinfo;
}

//更新文章阅读次数
function updateView(id, view) {
    if (id > 0) {
        webforceAjax("news_update_view.php", "POST", {"id": id, "value": view});
    }
}

/*获取文章相关信息
参数id：页面的ID*/
function getNewsDetail(id) {
    let info = webforceAjax('inner_newsdetail.php', 'POST', {id});
    info = info.code == 200 ? info.obj.data : '';
    return info;
}
