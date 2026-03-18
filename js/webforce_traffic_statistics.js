var trafficStatisticsData = getTrafficStatistics(); //获取流量数据

//获取日期
function getDate(i) {
    let date = new Date();
    date.setDate(date.getDate() - i); //减去i天，主要用来获取今天或昨天的日期
    let year = date.getFullYear();
    let month = ("0" + (date.getMonth() + 1)).slice(-2);
    let day = ("0" + date.getDate()).slice(-2);
    return year + "-" + month + "-" + day; //今天日期
}

//获取今日ip数
function getIP(i) {
    i = i || 0;
    let num = 0;
    let data = trafficStatisticsData;
    if (data.length > 0) {
        let today = getDate(i); //今天日期

        let ip_all = [];
        for (let i = 0; i < data.length; i++) {
            let visit_time = formatTime(data[i]["visit_time"]); //首次访问时间
            let ip = data[i]["ip"]; //首次访问时间
            if (today === visit_time) {
                ip_all.push(ip);
            }
        }
        //去除重复数组
        let uniqueIP = $.grep(ip_all, function (value, index) {
            return index === ip_all.indexOf(value);
        });
        num = uniqueIP.length;
    }
    return num;
}

//获取今日浏览量
function getPageView(i) {
    i = i || 0;
    let num = 0;
    let data = trafficStatisticsData;
    if (data.length > 0) {
        let today = getDate(i); //今天日期

        let page_view_all = [];
        for (let i = 0; i < data.length; i++) {
            let visit_time = formatTime(data[i]["visit_time"]); //首次访问时间
            let page_view = data[i]["page_view"]; //首次访问时间
            let ip = data[i]["ip"]; //首次访问时间
            if (today === visit_time) {
                page_view_all.push(page_view);
                num = page_view.length + num;
            }
        }
    }
    return num;
}

//获取今日访客数
function getUserAgent(i) {
    i = i || 0;
    let num = 0;
    let data = trafficStatisticsData;
    if (data.length > 0) {
        let today = getDate(i); //今天日期

        let user_agent_all = [];
        for (let i = 0; i < data.length; i++) {
            let visit_time = formatTime(data[i]["visit_time"]); //首次访问时间
            let user_agent = data[i]["user_agent"]; //用户代理
            if (today === visit_time) {
                user_agent_all.push(user_agent);
            }
        }
        //去除重复数组
        let uniqueUserAgent = $.grep(user_agent_all, function (value, index) {
            return index === user_agent_all.indexOf(value);
        });
        num = uniqueUserAgent.length;
    }
    return num;
}

//获取流量统计数据
function getTrafficStatistics() {
    let info = webforceAjax('traffic_statistics.php');
    info = info.code == 200 ? info.obj.data : [];
    return info;
}