// 修复切换登录方式功能
document.addEventListener('DOMContentLoaded', function () {
    const tabs = document.querySelectorAll('.tab');
    const panels = document.querySelectorAll('.form-panel');

    tabs.forEach(tab => {
        tab.addEventListener('click', function () {
            // 移除所有标签的active类
            tabs.forEach(t => t.classList.remove('active'));
            // 添加当前标签的active类
            this.classList.add('active');

            // 隐藏所有面板
            panels.forEach(panel => panel.classList.remove('active'));

            // 显示对应的面板
            const tabId = this.getAttribute('data-tab');
            const targetPanel = document.getElementById(`${tabId}-form`);
            if (targetPanel) {
                targetPanel.classList.add('active');
            }
        });
    });
});

// 验证码图片点击刷新
/*
document.getElementById('captcha-img').addEventListener('click', function() {
    this.textContent = '加载中...';
    setTimeout(() => {
        this.textContent = Math.random().toString(36).substr(2, 4).toUpperCase();
    }, 500);
});

// 初始化验证码
document.getElementById('captcha-img').textContent = Math.random().toString(36).substr(2, 4).toUpperCase();
*/

// 发送验证码按钮
/*document.getElementById('send-code-btn').addEventListener('click', function () {
    const phoneInput = document.getElementById('phone');
    const phone = phoneInput.value.trim();

    if (!phone || !/^1[3-9]\d{9}$/.test(phone)) {
        alert('请输入正确的手机号');
        phoneInput.focus();
        return;
    }

    // 禁用按钮并开始倒计时
    this.disabled = true;
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

    alert(`验证码已发送到 ${phone}，请注意查收`);
});*/

// 登录按钮点击事件
/*document.getElementById('account-login-btn').addEventListener('click', function() {
    const username = document.getElementById('username').value.trim();
    const password = document.getElementById('password').value.trim();
    const captcha = document.getElementById('captcha').value.trim();

    if (!username) {
        alert('请输入账号');
        return;
    }

    if (!password) {
        alert('请输入密码');
        return;
    }

    if (!captcha) {
        alert('请输入验证码');
        return;
    }

    alert('账号登录请求已发送');
});

document.getElementById('phone-login-btn').addEventListener('click', function() {
    const phone = document.getElementById('phone').value.trim();
    const smsCode = document.getElementById('sms-code').value.trim();

    if (!phone || !/^1[3-9]\d{9}$/.test(phone)) {
        alert('请输入正确的手机号');
        return;
    }

    if (!smsCode) {
        alert('请输入验证码');
        return;
    }

    alert('手机号登录请求已发送');
});
*/

//手机号登录
$("#login_mobile").click(function () {
    let mobile = $("#mobile").val().trim(); //用户手机号
    if (isEmpty(mobile)) {
        showModal('warning', "请输入手机号！");
        $("#mobile").focus();
        return false;
    } else if (!/^1[3-9]\d{9}$/.test(mobile)) {
        showModal('warning', '请输入正确格式的手机号！');
        $("#mobile").focus();
        return false;
    }

    let mobile_code = $("#mobile_code").val().trim(); //验证码
    if (isEmpty(mobile_code)) {
        showModal('warning', "请输入验证码！");
        $("#mobile_code").focus();
        return false;
    }

    // 显示登录中
    this.innerHTML = '登录中...';
    this.disabled = true;

    let info = webforceAjax('login.php', 'POST', {"login_type": 1, "user_mobile": mobile, "mobile_code": mobile_code});

    if (info.code === 200) {
        showModal('success', '登录成功！', '', 1500, function () {
            //登录成功跳转到首页
            window.location.href = "/index";
        });
    } else if (info.code === 100 || info.code === 500) {
        showModal('error', '提示', info.message);
    } else {
        showModal('error', '出错了', '登录失败，请联系管理员！');
    }

    // changeCode(); //更新验证码

    // 恢复按钮状态
    setTimeout(() => {
        this.innerHTML = '立即登录';
        this.disabled = false;
    }, 3000);
});

//发送验证码
$("#send_code").click(function () {
    const phoneInput = document.getElementById('mobile');
    const phone = phoneInput.value.trim();

    if (isEmpty(phone)) {
        showModal('warning', "请输入手机号！");
        $("#mobile").focus();
        return false;
    } else if (!/^1[3-9]\d{9}$/.test(phone)) {
        showModal('warning', '请输入正确格式的手机号');
        phoneInput.focus();
        return false;
    }

    // 禁用按钮
    this.disabled = true;

    //发送验证码请求
    let info = webforceAjax('send_sms.php', 'POST', {"type": 1, "mobile": phone});

    //如果发送成功，开始倒计时
    if (info.code === 200) {
        let countdown = 60; //倒计时
        const timer = setInterval(() => {
            this.textContent = `${countdown}秒后重新发送`;
            countdown--;

            if (countdown < 0) {
                clearInterval(timer);
                this.disabled = false;
                this.textContent = '发送验证码';
            }
        }, 1000);

        showModal('success', '发送成功', `验证码已发送到 ${phone}，请注意查收`);
    } else if (info.code === 100 || info.code === 500) {
        this.disabled = false;
        showModal('error', '提示', info.message);
    } else {
        this.disabled = false;
        showModal('error', '出错了', '发送失败，请联系管理员');
    }
});

//账号密码登录
$("#code_img").attr("src", url_host + "/application/admin/captcha.php?time=" + Math.random()); //验证码
$("#login_username").click(function () {
    let username = $("#username").val();
    if (username == "" || username == null) {
        showModal('warning', "请输入用户名！");
        $("#username").focus();
        return false;
    }

    let password = $("#password").val();
    if (password == "" || password == null) {
        showModal('warning', "请输入用户密码！");
        $("#password").focus();
        return false;
    }

    let code = $("#code").val();
    if (code == "" || code == null) {
        showModal('warning', "请输入验证码！");
        $("#code").focus();
        return false;
    }

    // 密码密文传输
    let encryptInfo = encryptParam(password);
    let _password = encryptInfo.ciphertext;
    let password_iv = encryptInfo.iv;

    // 显示登录中
    this.innerHTML = '登录中...';
    this.disabled = true;

    let info = webforceAjax('login.php', 'POST', {
        "login_type": 2,
        "user_name": username,
        "user_password": _password,
        "iv": password_iv,
        "code": code
    });

    if (info.code === 200) {
        showModal('success', '登录成功！', '', 1500, function () {
            //登录成功跳转到首页
            window.location.href = "/index";
        });
    } else if (info.code === 100 || info.code === 500) {
        showModal('error', '提示', info.message);
    } else {
        showModal('error', '出错了', '登录失败，请联系管理员！');
    }

    changeCode(); //更新验证码

    // 恢复按钮状态
    setTimeout(() => {
        this.innerHTML = '立即登录';
        this.disabled = false;
    }, 3000);
});

//点击更新验证码
function changeCode() {
    $("#code_img").attr("src", url_host + "/application/admin/captcha.php?time=" + Math.random());
}