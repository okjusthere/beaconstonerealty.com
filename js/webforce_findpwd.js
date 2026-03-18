// 当前步骤
let currentStep = 1;

// 更新步骤状态
function updateSteps() {
    const steps = document.querySelectorAll('.step');
    const panels = document.querySelectorAll('.step-panel');

    // 更新步骤指示器
    steps.forEach((step, index) => {
        step.classList.remove('active', 'completed');
        if (index + 1 < currentStep) {
            step.classList.add('completed');
        } else if (index + 1 === currentStep) {
            step.classList.add('active');
        }
    });

    // 更新面板
    panels.forEach((panel, index) => {
        panel.classList.remove('active');
        if (index + 1 === currentStep) {
            panel.classList.add('active');
        }
    });
}

// 切换到下一步
function goToNextStep() {
    if (currentStep < 3) {
        currentStep++;
        updateSteps();
    }
}

// 切换到上一步
function goToPrevStep() {
    if (currentStep > 1) {
        currentStep--;
        updateSteps();
    }
}

// 发送验证码按钮
document.getElementById('send-code-btn').addEventListener('click', function () {
    const phoneInput = document.getElementById('phone');
    const phone = phoneInput.value.trim();

    if (!phone || !/^1[3-9]\d{9}$/.test(phone)) {
        showModal('warning', '请输入正确格式的手机号！');
        phoneInput.focus();
        return;
    }

    // 禁用按钮并开始倒计时
    this.disabled = true;

    //发送验证码请求
    let info = webforceAjax('send_sms.php', 'POST', {"type": 1, "mobile": phone});

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
document.getElementById('new-password').addEventListener('input', function () {
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
document.getElementById('confirm-new-password').addEventListener('input', function () {
    const password = document.getElementById('new-password').value;
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

// 步骤1：下一步按钮
document.getElementById('next-step1-btn').addEventListener('click', function () {
    const phone = document.getElementById('phone').value.trim();
    const smsCode = document.getElementById('sms-code').value.trim();

    if (!phone || !/^1[3-9]\d{9}$/.test(phone)) {
        showModal('warning', '请输入正确格式的手机号！');
        document.getElementById('phone').focus();
        return;
    }

    if (!smsCode) {
        showModal('warning', '请输入验证码！');
        document.getElementById('sms-code').focus();
        return;
    }

    // 校验手机号--改成调用校验手机号接口
    let info = webforceAjax('findpwd_verify.php', 'POST', {
        "user_mobile": phone,
        "sms_code": smsCode
    });

    if (info.code === 200) {
        //将接口获取到的参数，存入会话
        sessionStorage.setItem('reset_token', info.obj.reset_token);
        sessionStorage.setItem('reset_mobile', info.obj.reset_mobile);
        // 验证通过，进入下一步
        goToNextStep();
    } else if (info.code === 100 || info.code === 500) {
        showModal('error', '提示', info.message);
    } else {
        showModal('error', '出错了', '校验失败，请联系管理员');
    }
});

// 步骤2：上一步按钮
document.getElementById('prev-step2-btn').addEventListener('click', function () {
    goToPrevStep();
});

// 步骤2：下一步按钮
document.getElementById('next-step2-btn').addEventListener('click', function () {
    const newPassword = document.getElementById('new-password').value.trim();
    const confirmPassword = document.getElementById('confirm-new-password').value.trim();
    const phone = sessionStorage.getItem('reset_mobile');
    const reset_token = sessionStorage.getItem('reset_token');

    if (!newPassword || newPassword.length < 8) {
        showModal('warning', '密码长度至少8位！');
        document.getElementById('new-password').focus();
        return;
    } else if (checkPassword(newPassword) < 4) {
        showModal('warning', '密码错误', '密码至少包含数字、大小写字母、特殊符号其中3种！');
        document.getElementById('new-password').focus();
        return;
    }

    if (newPassword !== confirmPassword) {
        showModal('warning', '两次输入的密码不一致！');
        document.getElementById('confirm-new-password').focus();
        return;
    }

    if (isEmpty(phone) || isEmpty(reset_token)) {
        showModal('warning', '无法获取要重置的账号！');
        return;
    }

    // 显示重置中状态
    this.innerHTML = '重置中...';
    this.disabled = true;

    // 密码密文传输
    let encryptInfo = encryptParam(newPassword);
    let _password = encryptInfo.ciphertext;
    let password_iv = encryptInfo.iv;
    let encryptConfirmInfo = encryptParam(confirmPassword);
    let _confirmPassword = encryptConfirmInfo.ciphertext;
    let confirmPasswordIV = encryptConfirmInfo.iv;

    // 开始重置--改成调用重置接口
    let info = webforceAjax('findpwd_reset.php', 'POST', {
        "user_mobile": phone,
        "new_password": _password,
        "new_password_iv": password_iv,
        "confirm_password": _confirmPassword,
        "confirm_password_iv": confirmPasswordIV,
        "reset_token": reset_token
    });

    if (info.code === 200) {
        // 重置成功，进入下一步
        goToNextStep();
    } else if (info.code === 100 || info.code === 500) {
        showModal('error', '提示', info.message);
    } else {
        showModal('error', '出错了', '重置失败，请联系管理员');
    }

    // 恢复按钮状态
    setTimeout(() => {
        this.innerHTML = '下一步';
        this.disabled = false;
    }, 1000);
});

// 步骤3：前往登录按钮
document.getElementById('go-to-login-btn').addEventListener('click', function () {
    // 在实际应用中，这里会跳转到登录页面
    window.location.href = '/login';
});

// 初始化步骤
updateSteps();