$(document).ready(function(e) {
	//产品左侧二级点击收放
	$(".m1_side_a ul li span>i").click(function(){
		$(this).parent("span").siblings("ul").stop().slideToggle();
		$(this).parent("span").parent("li").toggleClass("active");
		$(this).parent("span").parent("li").siblings("li").children("ul").stop().slideUp();
		$(this).parent("span").parent("li").siblings("li").removeClass("active");
	});
	//左侧联系方式显示隐藏
	$(".inLt_contact_close").click(function(){
		$(".m1_side_c").addClass("hiden").removeClass("shown");
	});
	$(".inLt_contact_open").click(function(){
		$(".m1_side_c").addClass("shown").removeClass("hiden");
	});
});



$(document).ready(function (e) {
    //发送验证码
    $("#sendcode").click(function () {
        let phone = $('input[name="phone"]').val().trim();
		if (phone == "") {
			showModal("warning", "请填写手机号码！")
			$('input[name="phone"]').focus();
			return false;
		} else if (!(/^13\d{9}$/g.test(phone)) && !(/^15\d{9}$/g.test(phone)) && !(/^18\d{9}$/g.test(phone)) && !(/^17\d{9}$/g.test(phone))) {
			showModal("error", "手机号码格式不正确！")
			$('input[name="phone"]').focus();
			return false;
		}
		
        sendCode(phone);
    });
    
	//询盘
	$(".pinquiry1_open").click(function () {
		$(".pinquiry1_box").fadeIn(10).find(".pinquiry1").addClass("fadeInRight2");
	});
	$(".pinquiry1_close").click(function () {
		$(".pinquiry1_box").fadeOut(100).find(".pinquiry1").removeClass("fadeInRight2");
	});

	
	//发起询盘
	$(".btnInquiry").click(function () {
		inquirySubmit();
	});
	
	
});

function inquirySubmit(){
	    let proname = $.cookie("proname"); //获取要询盘的产品名称
		//联系电话
		let phone = $('input[name="phone"]').val().trim();
		if (phone == "") {
			showModal("warning", "请填写手机号码！")
			$('input[name="phone"]').focus();
			return false;
		} else if (!(/^13\d{9}$/g.test(phone)) && !(/^15\d{9}$/g.test(phone)) && !(/^18\d{9}$/g.test(phone)) && !(/^17\d{9}$/g.test(phone))) {
			showModal("error", "手机号码格式不正确！")
			$('input[name="phone"]').focus();
			return false;
		}

		//电子邮箱
		let email = $('input[name="email"]').val().trim();
		if (email == "") {
			showModal("warning", "请填写电子邮箱！")
			$('input[name="email"]').focus();
			return false;
		} else if (!/^[\w-]+(\.[\w-]+)*@[\w-]+(\.(\w)+)*(\.(\w){2,3})$/.test(email)) {
			showModal("error", "Email地址不合法！E-mail格式：123@abc.com")
			$('input[name="email"]').focus();
			return false;
		}

		//联系人
		let contacts = $('input[name="contacts"]').val().trim();
		if (contacts == "") {
			showModal("warning", "请填写联系人！")
			$('input[name="contacts"]').focus();
			return false;
		}

		//咨询内容
		let message = $('textarea[name="message"]').val().trim();
		if (message == "") {
			showModal("warning", "请填写咨询内容！")
			$('textarea[name="message"]').focus();
			return false;
		}

		message = "产品名称：" + proname + "<br/>客户留言：" + message; //将询盘产品和咨询内容合并，提交到后台

		//验证码
		var code = $('input[name="code"]').val().trim();
		if (code == "") {
			showModal("warning", "请填写验证码！")
			$('input[name="code"]').focus();
			return false;
		}

        $(".btnInquiry").attr("disabled", true); //提交按钮无法再次点击
		//开始提交
		$.ajax({
			url: url_host + "/page/inner_message.php", //接口地址
			type: "POST", //传值方式
			data: {
				"title": "产品询盘",
				"contacts": contacts,
				"phone": phone,
				"email": email,
				"message": message,
				"code": code,
				"source":"inquiry"
			}, //请求参数
			datatype: "json",
			async: false, // 设置同步方式
			success: function (res) {
				if (res.message == "success") {
					showModal("success", "提交成功，我们将尽快与您取得联系！");
					$.cookie("proname", ""); //清除当前产品名称cookie内容
					
					setTimeout(function () {
					    $(".pinquiry1_box").fadeOut(100).find(".pinquiry1").removeClass("fadeInRight2");
					    setTimeout(function () {
						    window.location.reload();
					    }, 1000)
					}, 600)
				} else if (res.code == "100") {
					alert(res.message);
					$(".btnInquiry").removeAttr("disabled"); //提交按钮可以点击
				}
			},
			error: function () {
				console.log("submit error!");
				$(".btnInquiry").removeAttr("disabled"); //提交按钮可以点击
			}
		});
	}
	
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
                }
            },
            error: function () {
                console.log("sendCode error");
            }
        });
    }
}

//将不需要询盘的产品，从询盘表中移除
function removeProduct(proname) {
	let proArray = $.cookie("proname").split(',');
	let newProArray = [];
	let newProStr = "";
	if (proArray.length == 1) {
		showModal("warning", "产品名称若为空，无法进行询盘的哟！");
		return false;
	} else {
		proArray.forEach((e) => {
			if (e != proname) newProArray.push(e);
		})
	}
	let proShow = "";
	newProArray.forEach((e) => {
		proShow += "<span>" + e + "<i onclick='removeProduct(\"" + e + "\")'>×</i></span>";
	})
	$("#proName").html(proShow);

	newProStr = newProArray.join(','); //将生成的新数组转化为字符串并更新cookie值
	$.cookie("proname", newProStr);
}