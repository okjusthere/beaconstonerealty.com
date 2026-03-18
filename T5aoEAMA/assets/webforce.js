//获取当前参数信息
$(document).ready(function () {
    document.title = "Webforce站动力网站管理系统"
    $.ajax({
        url: "../application/admin/getbasicinfo.php", //接口地址
        type: "GET", //传值方式
        data: "",
        datatype: "json",
        async: false, // 设置同步方式
        success: function (res) {
            if (res.message == "success") {
                let basicinfo = res.obj.data
                if (!isEmpty(basicinfo.background_title)) {
                    document.title = basicinfo.background_title
                }
            }
        },
        error: function () {

        }
    });
})

/*判断值是否为空
* @param any value 需要判断是否为空的参数*/
function isEmpty(value) {
    let result = false;
    if (value == "" || value == null || value == undefined) {
        result = true;
    }
    return result;
}