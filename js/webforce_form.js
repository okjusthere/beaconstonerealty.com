//获取在线表单
function form(id, type) {
    type = type || 1;
    let fieldInfo = getForm(id);
    let formShow = "";
    if (fieldInfo.length > 0) {
        switch (type) {
            case 1:
                formShow = formShow1(id, fieldInfo)
                break;
        }
    }
    return formShow;
}

//在线表单展示样式一
function formShow1(id, field) {
    let show = "<form id='form" + id + "'><ul>";
    field.forEach((e) => {
        let field_name = e.field_name; //字段名称
        let field_description = e.field_description; //字段描述
        let field_type = e.field_type; //字段类型{0/文本框，1/文本域，2/单选框，3/复选框，4/下拉框，5/文件上传}
        let field_value = e.field_value; //相关字段值
        let is_required = e.is_required; //是否必填{1/是；2/否}
        let required_tips = e.required_tips; //必填提示

        show += "<li>" + e.field_description;
        if (field_type === 0) { //文本框
            show += "<input type='text' name='" + field_name + "'>";
        } else if (field_type === 1) { //文本域
            show += "<textarea name='" + field_name + "' rows='5' cols='20'></textarea>";
        } else if (field_type === 2) { //单选框
            if (field_value.length > 0) {
                field_value.forEach((val) => {
                    show += "<input type='radio' name='" + field_name + "' value='" + val + "'>";
                    show += "<label>" + val + "</label>";
                })
            }
        } else if (field_type === 3) { //复选框
            if (field_value.length > 0) {
                field_value.forEach((val) => {
                    show += "<input type='checkbox' name='" + field_name + "' value='" + val + "'>";
                    show += "<label>" + val + "</label>";
                })
            }
        } else if (field_type === 4) { //下拉框
            if (field_value.length > 0) {
                show += "<select name='" + field_name + "'>";
                show += "<option value=''>请选择" + field_description + "</option>";
                field_value.forEach((val) => {
                    show += "<option value='" + val + "'>" + val + "</option>";
                })
                show += "</select>";
            }
        } else if (field_type === 5) { //文件上传
            show += "<input type='file' id='file_" + field_name + "' onchange='uploadChange(\"file_" + field_name + "\",\"path_" + field_name + "\")'>";
            show += "<input type='text' id='path_" + field_name + "' style='display: none;'>";
        }
        show += "</li>";
    });
    show += "<li><input type='button' id='formBtn" + id + "' value='提交'></li>";
    show += "</ul></form>";

    show += "<script type='text/javascript'>";
    show += "$('#formBtn" + id + "').click(function(){";
    show += "let submitData={};";
    show += "submitData.id=" + id + ";";
    field.forEach((e) => {
        let field_name = e.field_name; //字段名称
        let field_description = e.field_description; //字段描述
        let field_type = e.field_type; //字段类型{0/文本框，1/文本域，2/单选框，3/复选框，4/下拉框，5/文件上传}
        let field_value = e.field_value; //相关字段值
        let is_required = e.is_required; //是否必填{1/是；2/否}
        let required_tips = e.required_tips; //必填提示

        if (field_type === 0) { //文本框
            show += "let " + field_name + "=$('input[name=\"" + field_name + "\"]').val();";
            show += is_required === '1' ? "if(isEmpty(" + field_name + ")){showModal('warning','" + required_tips + "');$('input[name=\"" + field_name + "\"]').focus();return false;}" : "";
        } else if (field_type === 1) { //文本域
            show += "let " + field_name + "=$('textarea[name=\"" + field_name + "\"]').val();";
            show += is_required === '1' ? "if(isEmpty(" + field_name + ")){showModal('warning','" + required_tips + "');$('textarea[name=\"" + field_name + "\"]').focus();return false;}" : "";
        } else if (field_type === 2) { //单选框
            show += "let " + field_name + "=$('input[name=\"" + field_name + "\"]:checked').val();";
            show += is_required === '1' ? "if(isEmpty(" + field_name + ")){showModal('warning','" + required_tips + "');return false;}" : "";
        } else if (field_type === 3) { //复选框
            show += "let " + field_name + "=[];";
            show += "$('input[name=\"" + field_name + "\"]').each(function(){" +
                "if(this.checked){" + field_name + ".push($(this).val());}" +
                "});";
            show += is_required === '1' ? "if(isEmpty(" + field_name + ")){showModal('warning','" + required_tips + "');return false;}" : "";
        } else if (field_type === 4) { //下拉框
            show += "let " + field_name + "=$('select[name=\"" + field_name + "\"]').val();";
            show += is_required === '1' ? "if(isEmpty(" + field_name + ")){showModal('warning','" + required_tips + "');return false;}" : "";
        } else if (field_type === 5) { //文件上传
            show += "let fileInput_" + field_name + " = document.getElementById('file_" + field_name + "');";
            show += "let f_" + field_name + " = fileInput_" + field_name + ".files[0];"; //上传文件信息
            show += "let " + field_name + " = $('input[id=\"path_" + field_name + "\"]').val();"; //图片路径
            show += is_required === '1' ? "if(isEmpty(f_" + field_name + ")&&isEmpty(" + field_name + ")){showModal('warning','请选择要上传的文件！');return false;}" : "";
        }
        show += "submitData." + field_name + "=" + field_name + ";";
    });
    show += "let info = webforceAjax('form_submit.php','POST',submitData);" +
        "info = info.code == 200 ? info.message : '';" +
        "if(info==='success'){showModal('success','提交成功！');setTimeout(function () {window.location.href='index.html';},1500);}else{showModal('error','出错了，请联系管理员！');}";

    show += "});</script>";
    return show;
}

/*图片文件发生变化时触发
* @param fileID 获取图片上传信息
* @param showID 要展示图片路径的ID*/
function uploadChange(fileID, showID) {
    let fileInput = document.getElementById(fileID);
    let file = fileInput.files[0]; //上传文件信息
    if (!isEmpty(file)) {
        uploadFile(fileID, showID); //上传文件
    } else {
        $('input[id="' + showID + '"]').val('');
    }
}

/*上传图片
* @param fileID 获取图片上传信息
* @param showID 要展示图片路径的ID*/
function uploadFile(fileID, showID) {
    let fileInput = document.getElementById(fileID);
    let file = fileInput.files[0];

    if (isEmpty(file)) {
        showModal("warning", "请先选择上传文件");
        $("#" + fileID).focus();
        return false;
    } else {
        let formData = new FormData();
        formData.append("file", file);

        // 使用 AJAX 技术发送 POST 请求
        const xhr = new XMLHttpRequest();
        xhr.open('POST', url_host + '/page/upload.php', false); // 设置请求的 URL 和方法

        // 设置响应处理函数和上传进度处理函数
        xhr.onload = function () {
            if (xhr.status === 200) {
                // console.log($.parseJSON(xhr.response).obj.url);
                $res = $.parseJSON(xhr.response); //将接口返回的数据从string转换为array
                if ($res.code == 200) {
                    showModal("success", "上传成功！");
                    // document.getElementById(fileID).value = '';
                    $("#" + showID).val($res.obj.url);
                } else {
                    // showModal("error", $res.message);
                    return false;
                }
            } else {
                showModal("error", "文件上传失败，请联系管理员！");
                // console.error('文件上传失败');
            }
        };
        xhr.upload.onprogress = function (event) {
            // console.log(`上传进度：${event.loaded}/${event.total}`);
        };

        // 将 FormData 对象发送到服务器端处理逻辑中
        xhr.send(formData); // 将 FormData 对象发送到服务器端处理逻辑中，这里需要替换为实际的服务器端处理逻辑地址或方法名
    }
}

/*获取页面相关信息
参数id：页面的ID*/
function getForm(id) {
    let info = [];
    info = webforceAjax("form_type.php", "POST", {"id": id});
    info = info.code == 200 ? info.obj.data : [];
    return info;
}