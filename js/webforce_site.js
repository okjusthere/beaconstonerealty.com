$(document).ready(function () {
    sitemap(document.documentElement.outerHTML);
})

function sitemap(html) {
    if (!isEmpty(html) && local_site === "update") {
        let res = webforceAjax('sitemap.php', 'POST', {"html": html, "site": local_site});
        let info = res.code == 200 && res.message == "success" ? true : false;
        if (info && local_site === "update") {
            showModal('success', '网站地图更新成功!',);
            window.location.href = "/sitemap.xml"
        } else {
            console.log(info);
        }
    }
}