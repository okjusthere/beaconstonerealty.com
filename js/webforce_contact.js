// 联系页面
function contact(type) {
    type = type || 1;
    let contact = "";
    switch (type) {
        case 1:
            contact = contactShow1(web_info)
            break;
        case 2:
            contact = contactShow2(web_info)
            break;
    }
    return contact;
}

// 联系页面展现形式一
function contactShow1(info) {
    let con = "<div class='inside_contact1'>";
    con += "<ul>";
    con += !isEmpty(info.company) ? "<li><h2>" + info.company + "</h2></li>" : "";
    con += !isEmpty(info.address) ? "<li>地址：" + info.address + "</li>" : "";
    con += !isEmpty(info.phone) ? "<li>电话：" + info.phone + "</li>" : "";
    con += !isEmpty(info.mobile) ? "<li>手机：" + info.mobile + "</li>" : "";
    con += !isEmpty(info.email) ? "<li>邮箱：" + info.email + "</li>" : "";
    con += !isEmpty(info.fax) ? "<li>传真：" + info.fax + "</li>" : "";
    con += !isEmpty(info.contact) ? "<li>联系人：" + info.contact + "</li>" : "";
    con += !isEmpty(info.qq) ? "<li>业务QQ：" + info.qq + "</li>" : "";
    con += !isEmpty(info.wechat) ? "<li>微信号：" + info.wechat + "</li>" : "";
    con += !isEmpty(info.zip) ? "<li>邮编：" + info.zip + "</li>" : "";
    con += !isEmpty(info.icp) ? "<li>备案号：<a href='https://beian.miit.gov.cn/' target='_blank'>" + info.icp + "</a></li>" : "";
    con += !isEmpty(info.icp_police) ? "<li>公安备案号：" + info.icp_police + "</li>" : "";
    con += !isEmpty(info.weburl) ? "<li>网址：" + info.weburl + "</li>" : "";
    con += !isEmpty(info.map) ? "<li>" + info.map + "</li>" : "";
    con += "</ul>";
    con += "</div>";
    return con;
}

// 联系页面展现形式一
function contactShow2(info) {
    let con = `   <ul>
                <li data-aos="fade-up"><i class="fa fa-phone"></i><strong>Phone</strong>
                    <p>${info.phone}</p>
                </li>
                <li data-aos="fade-up"><i class="fa fa-envelope-o"></i><strong>Email</strong>
                    <p>${info.email}</p>
                </li>
                <li data-aos="fade-up"><i class="fa fa-location-arrow"></i><strong>Address</strong>
                    <p>${info.address}</p>
                </li>
            </ul>`
    return con;
}
