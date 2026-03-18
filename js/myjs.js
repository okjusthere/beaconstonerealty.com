//留言弹窗
window.onload = function () {
    //加载移除
    /*$(".loading_box").addClass("removeload");
    //移除加载状态
    $("html").removeClass("onload");
    //动画
    if (!(/msie [6|7|8|9]/i.test(navigator.userAgent))) {
        new WOW().init();
    };*/

};
$(document).ready(function () {
    //电脑栏目鼠标经过显示二级
    $(".m11_head .menu_down").hover(
        function () {
            $(this).children("ul").stop().slideDown();
        },
        function () {
            $(this).children("ul").stop().slideUp();
        }
    );
    //手机栏目
    $(".m11_head .nav_open").click(function () {
        $(".m11_head .nav_box").stop().slideToggle();
        $(this).toggleClass("active");
    });
    $(".m11_head .nav_close").click(function () {
        $(".m11_head .nav_box").stop().slideUp();
        $(".m11_head .nav_open").removeClass("active");
    });
    $(".m11_head .nav_list a").click(function () {
        $(".m11_head .nav_box").stop().slideUp();
        $(".m11_head .nav_open").removeClass("active");
    });
    $(".m11_head .nav_list ul li span i").click(function () {
        $(this).parent("span").siblings("ul").stop().slideToggle();
        $(this).parent("span").parent("li").toggleClass("active");
        $(this).parent("span").parent("li").siblings("li").children("ul").stop().slideUp();
        $(this).parent("span").parent("li").siblings("li").removeClass("active");
    });
    //搜索
    $(".h_search_btn").click(function () {
        $(".h_search_box").stop().slideToggle();
        $(".h_search_btn").toggleClass("active");
    });
    $(".h_search_close").click(function () {
        $(".h_search_box").stop().slideUp();
        $(".h_search_btn").removeClass("active");
    });

    var minHeight = 60;
    var minHeight2 = $(".inbannerbg").height() - $(".m11_headbg").height() - 1;
    $(window).scroll(function () {
        var s = $(document).scrollTop();

        if (s > minHeight) {
            $(".header").addClass("active");

        } else {
            $(".header").removeClass("active");

        }

        if (s > minHeight2) {
            $(".inner_menu2").addClass("active");

            $(".m1_side_a").addClass("active");
            $(".m1_side_c").addClass("lfixed");

        } else {
            $(".inner_menu2").removeClass("active");

            $(".m1_side_a").removeClass("active");
            $(".m1_side_c").removeClass("lfixed");
            $(".m1_side_c").removeClass("hiden");
            $(".m1_side_c").removeClass("shown");

        }
    })


    //客服
    var kefustate = getCookieD2("KEFUSTATE");
    if (kefustate == "OFF") {
        $(".m1_side_kefu_btn").addClass("active");
        $(".m1_side_kefu").addClass("active");
    } else {
        $(".m1_side_kefu_btn").removeClass("active");
        $(".m1_side_kefu").removeClass("active");
    }
    //打开
    $(".m1_side_kefu_open").click(function () {
        $(".m1_side_kefu_btn").removeClass("active");
        $(".m1_side_kefu").removeClass("active");
        setCookieD2('KEFUSTATE', 'ON', 1);
    });
    //关闭
    $(".m1_side_kefu_close").click(function () {
        $(".m1_side_kefu_btn").addClass("active");
        $(".m1_side_kefu").addClass("active");
        setCookieD2('KEFUSTATE', 'OFF', 1);
    });
    //返回顶部
    $(".m1_side_kefu .gotop").bind("click", function () {
        $('html, body').animate({scrollTop: 0}, 300);
        return false;
    })
    //返回顶部2
    $(".go_top").bind("click", function () {
        $('html, body').animate({scrollTop: 0}, 300);
        return false;
    })
    /*$(".go_top").hide();
    $(window).bind('scroll resize', function () {
        if ($(window).scrollTop() <= 300) {
            $(".go_top").hide();
        } else {
            $(".go_top").show();
        }
    });*/

})


/* */

!(function () {
    var serviceOnline = (function () {
        var timer = null;
        //悬浮QQ匀速移动
        var startMove = function (argument) {
            var scrollsidebar = document.getElementById("scrollsidebar");
            clearInterval(timer);
            timer = setInterval(function () {
                var speed = (argument - scrollsidebar.offsetTop) / 4;
                speed = speed > 0 ? Math.ceil(speed) : Math.floor(speed);
                if (argument == scrollsidebar.offsetTop) {
                    clearInterval(timer);
                } else {
                    scrollsidebar.style.top = scrollsidebar.offsetTop + speed + "px";
                }
            }, 20);
        };
        //鼠标移动
        var scrollMove = function () {
            window.onscroll/* = window.onload*/ = function () {
                var scrollsidebar = document.getElementById("scrollsidebar");
                var scrolltop =
                    document.body.scrollTop || document.documentElement.scrollTop;
                startMove(
                    parseInt(
                        (document.documentElement.clientHeight - scrollsidebar.offsetHeight) / 2 + scrolltop
                    )
                );
            };
        };
        //返回出来的方法
        return {
            init: function () {
                scrollMove();
            }
        };
    })();

    //初始化
    serviceOnline.init();
})();


//设置Cookie
function setCookieD2(c_name, value, expiredays) {
    var exdate = new Date()
    exdate.setDate(exdate.getDate() + expiredays)
    document.cookie = c_name + "=" + escape(value) +
        ((expiredays == null) ? "" : "; expires=" + exdate.toGMTString())
}

//获取Cookie
function getCookieD2(c_name) {
    if (document.cookie.length > 0) {
        c_start = document.cookie.indexOf(c_name + "=")
        if (c_start != -1) {
            c_start = c_start + c_name.length + 1
            c_end = document.cookie.indexOf(";", c_start)
            if (c_end == -1) c_end = document.cookie.length
            return unescape(document.cookie.substring(c_start, c_end))
        }
    }
    return ""
}

//动态出现效果
if (!(/msie [6|7|8|9]/i.test(navigator.userAgent))) {
    new WOW().init();
}
;


//Add------------------------------------------------------------------------------------------------

// 监听页面滚动位置事件-使用DOMContentLoaded确保所有元素都已加载
/*document.addEventListener('DOMContentLoaded', function () {
    // 显示已保存的滚动位置
    function displaySavedPosition() {
        try {
            const savedPosition = localStorage.getItem(`sc_${page_name}`);
        } catch (error) {
            console.error('Error accessing localStorage:', error);
        }
    }

    // 恢复滚动位置
    function restoreScrollPosition() {
        try {
            const savedPosition = localStorage.getItem(`sc_${page_name}`);
            if (savedPosition) {
                // 添加轻微延迟以确保页面已完全渲染
                setTimeout(() => {
                    window.scrollTo(0, parseInt(savedPosition));
                }, 200);
            }
        } catch (error) {
            console.error('Error restoring scroll position:', error);
        }
    }

    // 更新滚动指示器
    function updateScrollIndicator() {
        const scrollTop = document.documentElement.scrollTop;
        const scrollHeight = document.documentElement.scrollHeight - document.documentElement.clientHeight;
        const scrollPercent = (scrollTop / scrollHeight) * 100;
        // scrollIndicator.style.width = scrollPercent + '%';
    }

    // 初始化
    displaySavedPosition();
    restoreScrollPosition();

    // 监听滚动事件，更新当前滚动位置显示
    window.addEventListener('scroll', function () {
        const scrollPosition = window.scrollY || document.documentElement.scrollTop;
        updateScrollIndicator();

        // 自动保存滚动位置（节流处理）
        if (!this.scrollTimeout) {
            this.scrollTimeout = setTimeout(() => {
                try {
                    localStorage.setItem(`sc_${page_name}`, scrollPosition);
                } catch (error) {
                    console.error('Error saving scroll position:', error);
                }
                this.scrollTimeout = null;
            }, 210);
        }
    });

    // 保存滚动位置到localStorage
    function saveScrollPosition() {
        try {
            const scrollPosition = window.scrollY || document.documentElement.scrollTop;
            localStorage.setItem(`sc_${page_name}`, scrollPosition);
        } catch (error) {
            console.error('Error saving scroll position:', error);
        }
    }

    // 在页面卸载前自动保存滚动位置
    window.addEventListener('beforeunload', function () {
        const scrollPosition = window.scrollY || document.documentElement.scrollTop;
        localStorage.setItem(`sc_${page_name}`, scrollPosition);
    });

    // 滚动到顶部
    scrollToTopBtn.addEventListener('click', function() {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
});*/


//处理本地伪静态无效的问题
function setURL() {
    // 获取body下所有的a标签
    var links = document.querySelectorAll('body a');

    // 遍历所有a标签
    links.forEach(function (link) {
        var originalHref = link.getAttribute('href');
        if (originalHref) {
            var transformedHref = transformURL(originalHref);
            link.setAttribute('href', transformedHref);
        }
    });
}

//URL转换函数
function transformURL(url) {
    if (isEmpty(url) || url == "/" || url == "/index" || url == "/home") {
        return `/home.html`;
    } else if (url.startsWith("/#")) {
        return url.replace("/#", "/home.html#");
    } else if ((/^\/(?=.*[a-zA-Z])(?!.*[^a-zA-Z\s][a-zA-Z]$)[a-zA-Z0-9\s\-_]*[a-zA-Z]$/).test(url)) {
        return url.concat(".html");
    } else {
        return url.replace(/\/([a-zA-Z0-9_-]+)\/(\d+)([&?]|$)/, (match, route, id, separator) => {
            if (id) {
                const connector = separator === '&' || separator === '?' ? '&' : '';
                return `/${route}.html?id=${id}${connector}`;
            } else {
                return match;
            }
        });
    }
}

// 等待页面加载完成
window.addEventListener('load', function () {
    requestIdleCallback(() => {
        setTimeout(() => {
            const hash = window.location.hash;
            if (hash) {
                // 去掉#号获取ID
                const elementId = hash.substring(1);

                // 方法1：使用getElementById（最可靠，支持数字ID）
                const element = document.getElementById(elementId);
                if (element) {
                    element.scrollIntoView({behavior: 'smooth'});
                    return;
                }

                // 方法2：尝试使用querySelector作为备选（处理其他类型的选择器）
                try {
                    const elementByQuery = document.querySelector(hash);
                    if (elementByQuery) {
                        elementByQuery.scrollIntoView({behavior: 'smooth'});
                        return;
                    }
                } catch (error) {
                    console.warn('无效的选择器:', hash);
                }

                // 方法3：最后的尝试 - 使用属性选择器
                const elementByAttr = document.querySelector(`[id="${CSS.escape(elementId)}"]`);
                if (elementByAttr) {
                    elementByAttr.scrollIntoView({behavior: 'smooth'});
                }
            }
        }, 100);
    });
});






// // 添加到文件顶部或合适位置
// // ------------------------------------------------------------------
// // 统一的 IntersectionObserver 管理器
// // ------------------------------------------------------------------
// const ObserverManager = (function () {
//     let instance = null;

//     class ObserverManager {
//         constructor() {
//             this.observers = new Map(); // 存储不同类型的观察者
//             this.observedElements = new WeakMap(); // 跟踪已观察的元素和观察者类型
//             this.defaultOptions = {
//                 root: null,
//                 rootMargin: '0px',
//                 threshold: 0.01
//             };
//         }

//         // 获取或创建特定配置的观察者
//         getObserver(type, options = {}) {
//             const key = `${type}_${JSON.stringify(options)}`;

//             if (!this.observers.has(key)) {
//                 const mergedOptions = { ...this.defaultOptions, ...options };
//                 const observer = new IntersectionObserver((entries) => {
//                     this.handleIntersection(type, entries, observer);
//                 }, mergedOptions);

//                 this.observers.set(key, {
//                     observer,
//                     options: mergedOptions,
//                     callbacks: new Map() // 存储元素对应的回调函数
//                 });
//             }

//             return this.observers.get(key);
//         }

//         // 统一处理交集变化
//         handleIntersection(type, entries, observerInstance) {
//             entries.forEach(entry => {
//                 const element = entry.target;
//                 const observerData = this.findObserverByInstance(observerInstance);

//                 if (observerData && observerData.callbacks.has(element)) {
//                     const callback = observerData.callbacks.get(element);
//                     if (typeof callback === 'function') {
//                         callback(entry, observerInstance);
//                     }
//                 }
//             });
//         }

//         // 查找观察者数据
//         findObserverByInstance(instance) {
//             for (const [key, data] of this.observers) {
//                 if (data.observer === instance) {
//                     return data;
//                 }
//             }
//             return null;
//         }

//         // 观察元素
//         observe(element, callback, options = {}) {
//             const observerData = this.getObserver('custom', options);

//             // 如果元素已经被其他观察者观察，先取消观察
//             if (this.observedElements.has(element)) {
//                 this.unobserve(element);
//             }

//             observerData.callbacks.set(element, callback);
//             observerData.observer.observe(element);
//             this.observedElements.set(element, observerData.observer);
//         }

//         // 停止观察元素
//         unobserve(element) {
//             if (this.observedElements.has(element)) {
//                 const observer = this.observedElements.get(element);

//                 // 从观察者的回调映射中移除
//                 for (const [key, data] of this.observers) {
//                     if (data.observer === observer && data.callbacks.has(element)) {
//                         data.callbacks.delete(element);
//                         break;
//                     }
//                 }

//                 observer.unobserve(element);
//                 this.observedElements.delete(element);
//             }
//         }

//         // 停止所有观察者
//         disconnectAll() {
//             this.observers.forEach(data => {
//                 data.observer.disconnect();
//                 data.callbacks.clear();
//             });
//             this.observers.clear();
//             this.observedElements = new WeakMap();
//         }

//         // 获取统计信息（调试用）
//         getStats() {
//             return {
//                 totalObservers: this.observers.size,
//                 totalObservedElements: Array.from(this.observedElements).length,
//                 observers: Array.from(this.observers.keys())
//             };
//         }
//     }

//     return {
//         getInstance: function () {
//             if (!instance) {
//                 instance = new ObserverManager();
//             }
//             return instance;
//         }
//     };
// })();

// // 初始化全局观察者管理器
// const observerManager = ObserverManager.getInstance();
// // ------------------------------------------------------------------

// window.addEventListener('load', () => {
//     let idleCallback = window.requestIdleCallback || ((cb) => setTimeout(cb, 1));
//     idleCallback(() => {
//         setTimeout(() => {
//             const customSelectArray = document.querySelectorAll('.custom-select');

//             customSelectArray.forEach(function (customSelect) {
//                 const selectHeader = customSelect.querySelector('.select-header');
//                 const selectValue = customSelect.querySelector('.select-value');
//                 const options = customSelect.querySelectorAll('.select-option');

//                 // 切换下拉菜单显示/隐藏
//                 selectHeader.addEventListener('click', function () {
//                     customSelect.classList.toggle('select-open');
//                 });

//                 // 选择选项
//                 options.forEach(option => {
//                     option.addEventListener('click', function () {
//                         // selectValue.innerHTML = this.innerHTML;
//                         customSelect.classList.remove('select-open');
//                     });
//                 });

//                 // 点击外部关闭下拉
//                 document.addEventListener('click', function (e) {
//                     if (!customSelect.contains(e.target)) {
//                         customSelect.classList.remove('select-open');
//                     }
//                 });
//             });
//         }, 100);
//     });
// });

// //懒加载
// //懒加载实现
// function initLazyLoad() {
//     // 选择所有需要懒加载的元素
//     let lazyElements = document.querySelectorAll('.lazy-img img[data-src], .lazy-bg[data-bg], .lazy-video video[data-src]');

//     lazyElements.forEach(element => {
//         observerManager.observe(
//             element,
//             (entry) => {
//                 if (entry.isIntersecting) {
//                     const element = entry.target;

//                     // 处理图片
//                     if (element.tagName === 'IMG' && element.hasAttribute('data-src')) {
//                         let src = element.getAttribute('data-src');
//                         if (src) {
//                             element.src = src;
//                             element.removeAttribute('data-src');
//                             element.closest('.lazy-img').classList.add('load');
//                             observerManager.unobserve(element);
//                         }
//                     }
//                     // 处理背景图
//                     else if (element.hasAttribute('data-bg')) {
//                         let bgUrl = element.getAttribute('data-bg');
//                         if (bgUrl) {
//                             element.style.backgroundImage = `url(${bgUrl})`;
//                             element.removeAttribute('data-bg');
//                             element.closest('.lazy-bg').classList.add('load');
//                             observerManager.unobserve(element);
//                         }
//                     }
//                     // 处理视频
//                     else if (element.hasAttribute('data-src')) {
//                         let videoSrc = element.getAttribute('data-src');
//                         let poster = element.getAttribute('data-poster');

//                         if (videoSrc) {
//                             if (element.tagName === 'VIDEO') {
//                                 let source = element.querySelector('source');
//                                 if (source) {
//                                     source.src = videoSrc;
//                                 } else {
//                                     element.src = videoSrc;
//                                 }

//                                 if (poster) {
//                                     element.poster = poster;
//                                     element.removeAttribute('data-poster');
//                                 }
//                             }
//                             element.removeAttribute('data-src');
//                             element.closest('.lazy-video').classList.add('load');

//                             // 如果需要自动播放
//                             if (element.hasAttribute('data-autoplay')) {
//                                 element.play().catch(e => console.log('自动播放失败:', e));
//                             }

//                             observerManager.unobserve(element);
//                         }
//                     }
//                 }
//             },
//             {
//                 rootMargin: '0px 0px 200px 0px',
//                 threshold: 0.01
//             }
//         );
//     });
// }

// //懒加载图片设置高度
// function setImgHeight() {
//     let lazyImages = document.querySelectorAll('.lazy-img');

//     lazyImages.forEach(img => {
//         try {
//             let lazyArea = img.closest('.lazy-area');

//             if (lazyArea) {
//                 img.style.height = lazyArea.outerHeight;
//             }
//         } catch (e) {
//             console.log("未找到lazy-area标签！");
//         }
//     });

//     // 为背景图容器设置最小高度（如果需要）
//     let lazyBgs = document.querySelectorAll('.lazy-bg');
//     lazyBgs.forEach(bg => {
//         try {
//             let lazyArea = bg.closest('.lazy-area');
//             if (lazyArea) {
//                 bg.style.minHeight = lazyArea.offsetHeight + 'px';
//             }
//         } catch (e) {
//             // 静默处理
//         }
//     });
// }

// //懒加载元素隐藏
// function lazyHidden() {
//     // 处理图片
//     let lazyImages = document.querySelectorAll('.lazy-img img');
//     lazyImages.forEach(img => {
//         img.setAttribute("data-src", img.src);
//         img.src = "";
//     });

//     // 处理背景图
//     let lazyBgs = document.querySelectorAll('.lazy-bg');
//     lazyBgs.forEach(bg => {
//         let computedStyle = window.getComputedStyle(bg);
//         let bgImage = computedStyle.backgroundImage;

//         if (bgImage && bgImage !== 'none') {
//             // 提取URL
//             let urlMatch = bgImage.match(/url\(["']?(.*?)["']?\)/);
//             if (urlMatch && urlMatch[1]) {
//                 bg.setAttribute('data-bg', urlMatch[1]);
//                 bg.style.backgroundImage = 'none';
//             }
//         }
//     });

//     // 处理视频
//     let lazyVideos = document.querySelectorAll('.lazy-video video');
//     lazyVideos.forEach(video => {
//         if (video.tagName === 'VIDEO') {
//             let source = video.querySelector('source');
//             let src = source ? source.src : video.src;

//             if (src) {
//                 video.setAttribute('data-src', src);

//                 // 保存封面图
//                 if (video.poster) {
//                     video.setAttribute('data-poster', video.poster);
//                     video.poster = '';
//                 }

//                 // 清空视频源
//                 if (source) {
//                     source.src = '';
//                 } else {
//                     video.src = '';
//                 }

//                 // 暂停视频
//                 video.pause();
//                 video.load();
//             }
//         }
//     });

//     //设置元素高度
//     setImgHeight();

//     // 初始化懒加载
//     initLazyLoad();
// }

// // 初始化懒加载
// window.addEventListener('load', () => {
//     let idleCallback = window.requestIdleCallback || ((cb) => setTimeout(cb, 1));
//     idleCallback(() => {
//         setTimeout(function () {
//             lazyHidden();
//         }, 100);
//     });
// });
