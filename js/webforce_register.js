// 发送验证码按钮
document.getElementById('send-code-btn').addEventListener('click', function () {
    const phoneInput = document.getElementById('phone');
    const phone = phoneInput.value.trim();

    if (isEmpty(phone)) {
        showModal('warning', '请输入手机号！');
        phoneInput.focus();
        return false;
    } else if (!/^1[3-9]\d{9}$/.test(phone)) {
        showModal('warning', '请输入正确格式的手机号！');
        phoneInput.focus();
        return false;
    }

    // 禁用按钮并开始倒计时
    this.disabled = true;

    //发送验证码请求
    let info = webforceAjax('send_sms.php', 'POST', {"type": 1, "mobile": phone});

    //如果发送成功，开始倒计时
    if (info.code === 200) {
        let countdown = 60;

        const timer = setInterval(() => {
            this.textContent = `${countdown}秒后重新发送`;
            countdown--;

            if (countdown < 0) {
                clearInterval(timer);
                this.disabled = false;
                this.textContent = '发送验证码';
            }
        }, 1000);

        // 发送成功提示
        showModal('success', '发送成功', `验证码已发送到 ${phone}，请注意查收`);
    } else if (info.code === 100 || info.code === 500) {
        this.disabled = false;
        showModal('error', '提示', info.message);
    } else {
        this.disabled = false;
        showModal('error', '出错了', '发送失败，请联系管理员');
    }
});

// 密码强度检测
document.getElementById('password').addEventListener('input', function () {
    const password = this.value;
    const strengthFill = document.getElementById('strength-fill');
    const strengthText = document.getElementById('strength-text');

    // 重置样式
    strengthFill.className = 'strength-fill';

    if (password.length === 0) {
        strengthText.textContent = '密码强度';
        strengthText.style.color = '#666';
        return;
    }

    let strength = checkPassword(password);

    // 根据强度设置样式
    if (strength <= 2) {
        strengthFill.classList.add('strength-weak');
        strengthText.textContent = '弱';
        strengthText.style.color = '#ff4757';
    } else if (strength <= 4) {
        strengthFill.classList.add('strength-medium');
        strengthText.textContent = '中';
        strengthText.style.color = '#ffa502';
    } else {
        strengthFill.classList.add('strength-strong');
        strengthText.textContent = '强';
        strengthText.style.color = '#2ed573';
    }
});

// 密码格式检测
function checkPassword(password) {
    let strength = 0;

    // 长度检查
    if (password.length >= 8) strength++;

    // 包含小写字母
    if (/[a-z]/.test(password)) strength++;

    // 包含大写字母
    if (/[A-Z]/.test(password)) strength++;

    // 包含数字
    if (/[0-9]/.test(password)) strength++;

    // 包含特殊字符
    if (/[^a-zA-Z0-9]/.test(password)) strength++;

    return strength;
}

// 确认密码验证
document.getElementById('confirm-password').addEventListener('input', function () {
    const password = document.getElementById('password').value;
    const confirmPassword = this.value;
    const matchText = document.getElementById('password-match');

    if (confirmPassword.length === 0) {
        matchText.textContent = '';
        matchText.className = 'password-match';
        return;
    }

    if (password === confirmPassword) {
        matchText.textContent = '✓ 密码匹配';
        matchText.className = 'password-match success';
    } else {
        matchText.textContent = '✗ 密码不匹配';
        matchText.className = 'password-match error';
    }
});

// 注册按钮点击事件
document.getElementById('register-btn').addEventListener('click', function () {
    const phone = document.getElementById('phone').value.trim();
    const smsCode = document.getElementById('sms-code').value.trim();
    const username = document.getElementById('username').value.trim();
    const password = document.getElementById('password').value.trim();
    const confirmPassword = document.getElementById('confirm-password').value.trim();
    const agree = document.getElementById('agree').checked;

    if (isEmpty(phone)) {
        showModal('warning', '请输入手机号！');
        document.getElementById('phone').focus();
        return;
    } else if (!/^1[3-9]\d{9}$/.test(phone)) {
        showModal('warning', '请输入正确格式的手机号！');
        document.getElementById('phone').focus();
        return;
    }

    if (!smsCode) {
        showModal('warning', '请输入验证码');
        document.getElementById('sms-code').focus();
        return;
    }

    if (!username || username.length < 2 || username.length > 16) {
        showModal('warning', '请输入2-16位的用户名');
        document.getElementById('username').focus();
        return;
    }

    if (!password || password.length < 8) {
        showModal('warning', '密码长度至少8位');
        document.getElementById('password').focus();
        return;
    } else if (checkPassword(password) < 4) {
        showModal('warning', '密码错误', '密码至少包含数字、大小写字母、特殊符号其中3种！');
        document.getElementById('password').focus();
        return;
    }

    if (password !== confirmPassword) {
        showModal('warning', '两次输入的密码不一致！');
        document.getElementById('confirm-password').focus();
        return;
    }

    if (!agree) {
        showModal('warning', '请阅读并同意用户协议！');
        document.getElementById('agree').focus();
        return;
    }

    // 显示注册中
    this.innerHTML = '<span>注册中...</span>';
    this.disabled = true;

    // 密码密文传输
    let encryptInfo = encryptParam(password);
    let _password = encryptInfo.ciphertext;
    let password_iv = encryptInfo.iv;
    let encryptConfirmInfo = encryptParam(confirmPassword);
    let _confirmPassword = encryptConfirmInfo.ciphertext;
    let confirmPasswordIV = encryptConfirmInfo.iv;

    let info = webforceAjax('register.php', 'POST', {
        "regist_type": 1,
        "user_mobile": phone,
        "user_name": username,
        "user_password": _password,
        "user_password_iv": password_iv,
        "user_password_confirm": _confirmPassword,
        "user_password_confirm_iv": confirmPasswordIV,
        "code": smsCode
    });

    if (info.code === 200) {
        //注册成功
        showModal('success', '注册成功', '', 1500, function () {
            // 注册成功后的回调
            window.location.href = '/login'; // 跳转到登录页面
        });
    } else if (info.code === 100 || info.code === 500) {
        showModal('error', '提示', info.message);
    } else {
        showModal('error', '出错了', '注册失败，请联系管理员');
    }

    // 恢复按钮状态
    setTimeout(() => {
        this.innerHTML = '<span>立即注册</span>';
        this.disabled = false;
    }, 3000);
});

/*$(document).ready(function () {
    //手机号注册
    $("#register_mobile").click(function () {
        let mobile = $("#mobile").val().trim(); //用户手机号
        if (mobile == "" || mobile == null) {
            alert("请输入手机号！");
            $("#mobile").focus();
            return false;
        } else if (!/^1[3456789]\d{9}$/.test(mobile)) {
            alert("手机号码格式不正确！");
            $("#mobile").focus();
            return false;
        }

        let mobile_code = $("#mobile_code").val().trim(); //验证码
        if (mobile_code == "" || mobile_code == null) {
            alert("请输入验证码！");
            $("#mobile_code").focus();
            return false;
        }

        registerByMobile(mobile, mobile_code);
    });

    //发送验证码
    $("#sendcode").click(function () {
        let mobile = $("#mobile").val().trim(); //用户手机号
        if (mobile == "" || mobile == null) {
            alert("请输入手机号！");
            $("#mobile").focus();
            return false;
        } else if (!/^1[3456789]\d{9}$/.test(mobile)) {
            alert("手机号码格式不正确！");
            $("#mobile").focus();
            return false;
        }
        sendCode(mobile);
    });

    //账号密码注册
    $("#code_img").attr("src", url_host + "/application/admin/captcha.php?time=" + Math.random()); //验证码
    $("#register_username").click(function () {
        let username = $("#username").val();
        if (username == "" || username == null) {
            alert("请输入用户名！");
            $("#username").focus();
            return false;
        } else if (!/^[\u4E00-\u9FA5a-zA-Z0-9_]{4,25}$/.test(username)) {
            alert("用户名长度：4-25字符，可以包含数字、字母或者汉字!");
            $("#username").focus();
            return false;
        }

        let password = $("#password").val();
        if (password == "" || password == null) {
            alert("请输入用户密码！");
            $("#password").focus();
            return false;
        } else if (!/^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])[A-Za-z0-9]{8,16}$/.test(password)) {
            alert("密码必须由8~16位数字、大小写字母组合!");
            $("#password").focus();
            return false;
        }

        let c_password = $("#confirm_password").val();
        if (c_password == "" || c_password == null) {
            alert("请再次输入用户密码！");
            $("#confirm_password").focus();
            return false;
        } else if (!/^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])[A-Za-z0-9]{8,16}$/.test(c_password)) {
            alert("密码必须由8~16位数字、大小写字母组合!");
            $("#confirm_password").focus();
            return false;
        }

        if (password !== c_password) {
            alert("两次输入的密码不一致，请重新输入！");
            $("#confirm_password").focus();
            return false;
        }

        let code = $("#code").val();
        if (code == "" || code == null) {
            alert("请输入验证码！");
            $("#code").focus();
            return false;
        }

        registerByUsername(username, password, code);
    });
});

//发送短信验证码
function sendCode(mobile) {
    $("#sendcode").attr("disabled", true);

    if (/^1[3456789]\d{9}$/.test(mobile)) {
        $.ajax({
            url: url_host + "/page/sendsms.php", //接口地址
            type: "POST",
            data: {"type": 1, "mobile": mobile},
            datatype: "json",
            async: false,
            success: function (res) {
                if (res == "success") {
                    $("#sendcode").val("60s");
                    let t = null;
                    t = setTimeout(time, 1000); //开始执行
                    function time() {
                        clearTimeout(t); //清除定时器
                        let second = $("#sendcode").val().replace("s", "");
                        if (second == "1") {
                            $("#sendcode").val("发送验证码");
                            $("#sendcode").removeAttr("disabled");
                        } else {
                            $("#sendcode").val(--second + "s");
                            t = setTimeout(time, 1000); //设定定时器，循环执行
                        }
                    }
                } else {
                    $("#sendcode").removeAttr("disabled");
                }
            },
            error: function () {
                console.log("sendCode error");
                $("#sendcode").removeAttr("disabled");
            }
        });
    }

}

//手机号注册接口
function registerByMobile(mobile, code) {
    let info = webforceAjax('register.php', 'POST', {"regist_type": 1, "user_mobile": mobile, "mobile_code": code});

    if (info.message == 'success') {
        showModal('success', "注册成功！");
        window.location.href = "/login";
    } else if (info.code == 100) {
        showModal('warning', info.message);
    } else {
        showModal('error', '注册失败，请联系管理员！');
    }
}

//用户名注册接口
function registerByUsername(username, userpassword, code) {
    let info = webforceAjax('register.php', 'POST', {
        "regist_type": 2,
        "user_name": username,
        "user_password": userpassword,
        "code": code
    });
    changeCode(); //更新验证码

    if (info.message == 'success') {
        showModal('success', "注册成功！");
        window.location.href = "/login";
    } else if (info.code == 100) {
        showModal('warning', info.message);
    } else {
        showModal('error', '注册失败，请联系管理员！');
    }
}*/

//点击更新验证码
function changeCode() {
    $("#code_img").attr("src", url_host + "/application/admin/captcha.php?time=" + Math.random());
}