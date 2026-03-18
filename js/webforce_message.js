$(document).ready(function () {
    //验证码
    $("#code_img").attr("src", url_host + "/application/admin/captcha.php?time=" + Math.random());

    //在线留言表单验证
    $("#submit").click(function () {
        //咨询主题
        let title = l_news_info ? l_news_info.title : '';
        //联系人
        let first = $('input[name="contacts"]').val().trim();
        if (first == "") {
            showModal("warning", "first Name！");
            $('input[name="contacts"]').focus();
            return false;
        }
        //联系人
        let lastname = $('input[name="lastname"]').val().trim();
        if (lastname == "") {
            showModal("warning", "last Name！");
            $('input[name="lastname"]').focus();
            return false;
        }
        let contacts = "first Name:" + first + ",last Name:" + lastname;

        //电子邮箱
        let email = $('input[name="email"]').val().trim();
        if (email == "") {
            showModal("warning", "Email Address");
            $('input[name="email"]').focus();
            return false;
        } else if (!/^[\w-]+(\.[\w-]+)*@[\w-]+(\.(\w)+)*(\.(\w){2,3})$/.test(email)) {
            showModal("error", "Email address is invalid");
            $('input[name="email"]').focus();
            return false;
        }

        //联系电话
        let phone = $('input[name="phone"]').val().trim();
        if (phone == "") {
            showModal("warning", "Phone number！");
            $('input[name="phone"]').focus();
            return false;
        } else if (!(/^13\d{9}$/g.test(phone)) && !(/^15\d{9}$/g.test(phone)) && !(/^18\d{9}$/g.test(phone)) && !(/^17\d{9}$/g.test(phone))) {
            showModal("error", "The format of the mobile phone number is incorrect！");
            $('input[name="phone"]').focus();
            return false;
        }

        //咨询内容
        let message = $('textarea[name="message"]').val().trim();
        if (message == "") {
            showModal("warning", "Please fill in the consultation content");
            $('textarea[name="message"]').focus();
            return false;
        }
        // //验证码
        // let code = $('input[name="code"]').val().trim();
        // if (code == "") {
        //     showModal("warning", "请填写验证码！");
        //     $('input[name="code"]').focus();
        //     return false;
        // }

        let info = webforceAjax('inner_message.php', 'POST', {
            "title": title,
            "contacts": contacts,
            "phone": phone,
            "email": email,
            "message": message,
            // "code": code
        });
        if (info.message == "success") {
            showModal("success", "提交成功，我们将尽快与您取得联系！");
            window.location.href = "/";
        } else {
            showModal("error", "请填写验证码！");
            changeCode(); //更新验证码
        }
    });

    //在线留言表单验证
    $("#submit2").click(function () {
        //咨询主题
        let title = 'Join as an agent';
        //联系人
        let first = $('input[name="contacts2"]').val().trim();
        if (first == "") {
            showModal("warning", "first Name！");
            $('input[name="contacts2"]').focus();
            return false;
        }
        //联系人
        let lastname = $('input[name="lastname2"]').val().trim();
        if (lastname == "") {
            showModal("warning", "last Name！");
            $('input[name="lastname2"]').focus();
            return false;
        }
        let contacts = "first Name:" + first + ",last Name:" + lastname;

        //电子邮箱
        let email = $('input[name="email2"]').val().trim();
        if (email == "") {
            showModal("warning", "Email Address");
            $('input[name="email2"]').focus();
            return false;
        } else if (!/^[\w-]+(\.[\w-]+)*@[\w-]+(\.(\w)+)*(\.(\w){2,3})$/.test(email)) {
            showModal("error", "Email address is invalid");
            $('input[name="email2"]').focus();
            return false;
        }

        //联系电话
        let phone = $('input[name="phone2"]').val().trim();
        if (phone == "") {
            showModal("warning", "Phone number！");
            $('input[name="phone2"]').focus();
            return false;
        } else if (!(/^13\d{9}$/g.test(phone)) && !(/^15\d{9}$/g.test(phone)) && !(/^18\d{9}$/g.test(phone)) && !(/^17\d{9}$/g.test(phone))) {
            showModal("error", "The format of the mobile phone number is incorrect！");
            $('input[name="phone2"]').focus();
            return false;
        }

        //SelectMarket
        let SelectMarket = $('input[name="SelectMarket"]').val().trim();
        if (SelectMarket == "") {
            showModal("warning", "SelectMarket！");
            $('input[name="SelectMarket"]').focus();
            return false;
        }

        //LinkedIn
        let LinkedIn = $('input[name="LinkedIn"]').val().trim();
        if (LinkedIn == "") {
            showModal("warning", "LinkedIn！");
            $('input[name="LinkedIn"]').focus();
            return false;
        }

        //咨询内容
        let message = $('textarea[name="message2"]').val().trim();
        if (message == "") {
            showModal("warning", "Please fill in the consultation content");
            $('textarea[name="message2"]').focus();
            return false;
        }
        // //验证码
        // let code = $('input[name="code"]').val().trim();
        // if (code == "") {
        //     showModal("warning", "请填写验证码！");
        //     $('input[name="code"]').focus();
        //     return false;
        // }

        message = "SelectMarket:" + SelectMarket + ",LinkedIn:" + LinkedIn + ",message:" + message;

        let info = webforceAjax('inner_message.php', 'POST', {
            "title": title,
            "contacts": contacts,
            "phone": phone,
            "email": email,
            "message": message,
            // "code": code
        });
        if (info.message == "success") {
            showModal("success", "提交成功，我们将尽快与您取得联系！");
            window.location.href = "/";
        } else {
            showModal("error", "请填写验证码！");
            changeCode(); //更新验证码
        }
    });
    //在线留言表单验证
    $("#submit3").click(function () {
        //咨询主题
        let title = 'contact us';
        //联系人
        let first = $('input[name="contacts2"]').val().trim();
        if (first == "") {
            showModal("warning", "first Name！");
            $('input[name="contacts2"]').focus();
            return false;
        }
        //联系人
        let lastname = $('input[name="lastname2"]').val().trim();
        if (lastname == "") {
            showModal("warning", "last Name！");
            $('input[name="lastname2"]').focus();
            return false;
        }
        let contacts = "first Name:" + first + ",last Name:" + lastname;

        //电子邮箱
        let email = $('input[name="email2"]').val().trim();
        if (email == "") {
            showModal("warning", "Email Address");
            $('input[name="email2"]').focus();
            return false;
        } else if (!/^[\w-]+(\.[\w-]+)*@[\w-]+(\.(\w)+)*(\.(\w){2,3})$/.test(email)) {
            showModal("error", "Email address is invalid");
            $('input[name="email2"]').focus();
            return false;
        }

        //联系电话
        let phone = $('input[name="phone2"]').val().trim();
        if (phone == "") {
            showModal("warning", "Phone number！");
            $('input[name="phone2"]').focus();
            return false;
        } else if (!(/^13\d{9}$/g.test(phone)) && !(/^15\d{9}$/g.test(phone)) && !(/^18\d{9}$/g.test(phone)) && !(/^17\d{9}$/g.test(phone))) {
            showModal("error", "The format of the mobile phone number is incorrect！");
            $('input[name="phone2"]').focus();
            return false;
        }

        //budget
        let budget = $('input[name="budget"]').val().trim();
        let bedrooms = $('input[name="bedrooms"]').val().trim();
        let purchase = $('input[name="purchase"]').val().trim();
        let location = $('input[name="location"]').val().trim();


        //咨询内容
        let message = "budget:" + budget + ",bedrooms:" + bedrooms + ",purchase:" + purchase + ",location:" + location;
        // //验证码
        // let code = $('input[name="code"]').val().trim();
        // if (code == "") {
        //     showModal("warning", "请填写验证码！");
        //     $('input[name="code"]').focus();
        //     return false;
        // }

        let info = webforceAjax('inner_message.php', 'POST', {
            "title": title,
            "contacts": contacts,
            "phone": phone,
            "email": email,
            "message": message,
            // "code": code
        });
        if (info.message == "success") {
            showModal("success", "提交成功，我们将尽快与您取得联系！");
            window.location.href = "/";
        } else {
            showModal("error", "请填写验证码！");
            changeCode(); //更新验证码
        }
    });
})

//点击更新验证码
function changeCode() {
    $("#code_img").attr("src", url_host + "/application/admin/captcha.php?time=" + Math.random());
}