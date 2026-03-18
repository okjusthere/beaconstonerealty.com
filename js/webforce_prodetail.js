var l_product_info = getProductDetail(local_id); //当前产品信息
//通过产品ID获取当前产品分类所绑定的栏目ID
var l_c_id = product_class(l_product_info.classid[0], "c_id");
if (l_c_id.length > 0) local_c_id = l_c_id[0];

$(document).ready(function () {
    //内页通用调用--开始
    $(".inbannerbg").html(banner_inner(5)); //内页通栏
    $(".weizhi").html(navigation()); //面包屑导航
    $(".m1_side_a").html(menu_inner()); //内页导航
    //内页通用调用--结束

    $(".m1_side_c").html(inner_contact()); //联系我们

    $(".content").html(prodetail()); //产品详情
    //产品详情多图
    $(".jqzoom").imagezoom();
    var galleryThumbs = new Swiper('.gallery-thumbs', {
        spaceBetween: 10,
        slidesPerView: 4,
        observer: true,
        observeParents: true,
        freeMode: true,
        watchSlidesVisibility: true,
        watchSlidesProgress: true,
    });
    var galleryTop = new Swiper('.gallery-top', {
        spaceBetween: 10,
        observer: true,
        observeParents: true,
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
        },
        thumbs: {
            swiper: galleryThumbs
        }
    });
    //产品详情切换
    jQuery(".slideTxtBox_02").slide({
        effect: "fade",
        autoPlay: false,
        delayTime: 500,
        interTime: 4000,
        defaultIndex: 0,
        titCell: ".hd li",
        mainCell: ".bd",
        trigger: "click",
        titOnClassName: "on",
        prevCell: ".prev",
        nextCell: ".next"
    });

});

//展示产品信息
function prodetail() {
    let prodetail = "";
    if (JSON.stringify(l_product_info) != "[]" && JSON.stringify(l_product_info) != "{}") {
        prodetail = "<div class='m11_proDetail'>"+
            "<div class='list1'>"+
            "<div class='lt'>"+

            "<div class='m1_galleryTop'>"+
            "<div class='swiper-container gallery-top'>"+
            "<div class='swiper-wrapper baguetteBox1'>";

        if(l_product_info.photo_album.length > 0){
            for (let i = 0; i < l_product_info.photo_album.length; i++) {
                prodetail += "<div class='swiper-slide'><a href='" + l_product_info.photo_album[i] + "' title='" + l_product_info.title + "'><div class='img'><img src='" + l_product_info.photo_album[i] + "' class='jqzoom' rel='" + l_product_info.photo_album[i] + "' alt='" + l_product_info.title + "'></div></a></div>";
            }
        }
        else{
            prodetail += "<div class='swiper-slide'><a href='" + l_product_info.thumbnail + "' title='" + l_product_info.title + "'><div class='img'><img src='" + l_product_info.thumbnail + "' class='jqzoom' rel='" + l_product_info.thumbnail + "' alt='" + l_product_info.title + "'></div></a></div>";
        }

        prodetail += "</div>"+
            "<div class='swiper-button-next swiper-button-white'></div><div class='swiper-button-prev swiper-button-white'></div>"+
            "</div>"+
            "</div>"+
            "<div class='m1_galleryThumbs'>"+
            "<div class='swiper-container gallery-thumbs'>"+
            "<div class='swiper-wrapper'>";

        if(l_product_info.photo_album.length > 0){
            for (let i = 0; i < l_product_info.photo_album.length; i++) {
                prodetail += "<div class='swiper-slide'><div class='img'><img src='" + l_product_info.photo_album[i] + "' title='" + l_product_info.title + "'></div></div>";
            }
        }
        else{
            prodetail += "<div class='swiper-slide'><div class='img'><img src='" + l_product_info.thumbnail + "' title='" + l_product_info.title + "'></div></div>";
        }

        prodetail += "</div>"+
            "</div>"+
            "</div>"+

            "</div>"+
            "<div class='rt'>"+
            "<div class='nr_box'>"+
            "<div class='h2'>" + l_product_info.title + "</div>"+
            "<div class='h3'>" + l_product_info.keywords + "</div>"+
            "<div class='h4'>" + l_product_info.description + "</div>"+
            "<div class='phone'><i class='fa fa-phone-square'></i><span>咨询热线</span><strong>"+web_info.mobile+"</strong></div>"+
            "</div>"+
            "</div>"+
            "<div class='clear'></div>"+
            "</div>"+

            "<div class='list2'>"+
            "<div class='slideTxtBox_02'>"+
            "<div class='hd'>"+
            "<ul>"+
            "<li><a href='javascript:;'>产品详情</a></li>"+
            "<li><a href='javascript:;'>自定义</a></li>"+
            "</ul>"+
            "</div>"+
            "<div class='bd'>"+
            "<div class='xiangqing'>" + l_product_info.content + "</div>"+
            "<div class='xiangqing'>添加自定义字段</div>"+
            "</div>"+
            "</div>"+
            "</div>"+

            "<div class='m11_prevnext2'>"+
            "<ul>";

        if (isEmpty(l_product_info.prev_url)) {
            prodetail += "<li><a href='javascript:void(0)'>没有了</a></li>";
        } else {
            prodetail += "<li><a href='" + l_product_info.prev_url + "'>上一个</a></li>";
        }

        if (isEmpty(l_product_info.next_url)) {
            prodetail += "<li><a href='javascript:void(0)'>没有了</a></li>";
        } else {
            prodetail += "<li><a href='" + l_product_info.next_url + "'>下一个</a></li>";
        }
        prodetail += "</ul><div class='clear'></div>"+
            "</div>"+
            "<div class='m11_pro_related'>加载中...</div>"+
            "</div>";

        //附件
        for (let i = 0; i < l_product_info.enclosure.length; i++) {
            prodetail += "<a href='" + l_product_info.enclosure[i]["path"] + "'>附件</a>";
        }
        //属性
        //prodetail += "<div class='attribute'>" + showProductAttribute() + "</div>";

        // 异步加载产品列表
        setTimeout(() => {
            wf_product_list(0,4).done(function(html) {
                $(`.m11_pro_related`).html(`<div class="h2"><span>相关产品</span></div>${html}`);
            });
        }, 0);

    } else {
        prodetail = "<p style='color: #d5d7de'>您没有访问该信息的权限</p>";
    }
    return prodetail;
}

/*获取产品相关信息
参数id：产品的ID*/
function getProductDetail(id) {
    let info = [];
    info = webforceAjax("inner_prodetail.php", "POST", {"id": id});
    info = info.code == 200 ? info.obj.data : [];
    return info;
}

//展示产品属性信息
function showProductAttribute(type) {
    type = type || 1;
    let showInfo = "";
    let proattribute = product_attribute(); //获取产品属性信息
    if (JSON.stringify(proattribute) != "[]" && JSON.stringify(proattribute) != "{}") {
        if (proattribute.data.length > 0) {
            switch (type) {
                case 1:
                    showInfo = proAttributeShow1(proattribute.data)
                    break;
            }
        }
    }
    return showInfo;
}

//产品属性展示一
function proAttributeShow1(proattribute) {
    let showInfo = "";

    showInfo += "<ul>";
    for (let i = 0; i < proattribute.length; i++) {
        let id = proattribute[i]["id"]; //产品属性信息ID
        let attribute_value = proattribute[i]["attribute_value"]; //属性值名称组合
        let price = proattribute[i]["price"]; //属性对应的价格
        let photo_album = proattribute[i]["photo_album"].length > 0 ? proattribute[i]["photo_album"] : "/images/no_picture.jpg"; //属性对应的图片相册

        showInfo += "<li onclick='attributeChange(" + id + "," + price + ",\"" + photo_album + "\",$(this))'>" + attribute_value + "</li>";
    }

    showInfo += "</ul>";
    return showInfo;
}

//产品属性点击切换事件
function attributeChange(id, price, photo_album, valueClick) {
    console.log(id);
    console.log(price.toFixed(2));

    let photo = photo_album.split(','); //图片相册
    let photo_list = "";
    for (let i = 0; i < photo.length; i++) {
        photo_list += "<img src='" + photo[i] + "'>";
    }
    $(valueClick).parent().parent().parent().find(".photo-album").html(photo_list);
    console.log(photo_album.split(','));
}

//获取产品属性数据
function product_attribute() {
    let info = [];
    info = webforceAjax("product_attribute.php", "POST", {"id": local_id});
    info = info.code == 200 ? info.obj : [];
    return info;
}