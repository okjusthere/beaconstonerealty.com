var l_page_info = getPage(local_id); //当前页面信息
//通过文章ID获取当前页面所绑定的栏目ID
if (l_page_info.c_id.length > 0) local_c_id = l_page_info.c_id[0];

$(document).ready(function () {
    $(".inbannerbg").html(banner_inner(5));
    $(".weizhi").html(navigation());
    $(".inner_menu").html(menu_inner());

    $(".content").html(page());
});

//展示页面信息
function page() {
    let page = "";
    if (JSON.stringify(l_page_info) != "[]" && JSON.stringify(l_page_info) != "{}") {
        page = "<span>" + l_page_info.content + "</span>";
    } else {
        page = "<p style='color: #d5d7de'>您没有访问该信息的权限</p>";
    }
    return page;
}

/*获取页面相关信息
参数id：页面的ID*/
function getPage(id) {
    let info = [];
    info = webforceAjax("inner_page.php", "POST", {"id": id});
    info = info.code == 200 ? info.obj.data : [];
    return info;
}
