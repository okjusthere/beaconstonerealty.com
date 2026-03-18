$(document).ready(function () {
    $("#inquire").click(function () {
        let condition = $("#condition").val().trim(); //查询条件
        if (condition == "" || condition == null) {
            alert("请填写证书编号！");
            $("#condition").focus();
            return false;
        }

        inner_query(condition);
    })
});

/*查询显示
参数q_condition：查询条件（通常是指证书编号）*/
function inner_query(q_condition) {
    let query_show = "";
    let query_info = getInnerQuery(q_condition);
    if (query_info == false) {
        query_show += "该证书不存在";
    } else {
        query_show += query_info.q_condition;
        query_show += query_info.thumbnail;
        query_show += query_info.content;
    }
    $(".query .content").html(query_show);
}

/*搜索
* 参数q_condition：查询条件（通常是指证书编号）*/
function getInnerQuery(q_condition) {
    let info = [];
    info = webforceAjax("inner_querydetail.php", "POST", {"q_condition": q_condition});
    info = info.code == 200 ? info.obj.data : [];
    return info;
}
