<?php
//object(stdclass)转换成array
function object_array($array)
{
    if (is_object($array)) {
        $array = (array)$array;
    }
    if (is_array($array)) {
        foreach ($array as $key => $value) {
            $array[$key] = object_array($value);
        }
    }
    return $array;
}

//判断字符串中是否包含某个中文，如"续费"
function safeContainsChinese($content, $keyword) {
    if (function_exists('mb_strpos')) {
        return mb_strpos($content, $keyword, 0, 'UTF-8') !== false;
    } else {
        // 回退方案
        return strpos($content, $keyword) !== false;
    }
}

//获取缩略图路径
function getThumbnailPath($thumbnail): string
{
    $path = "/images/no_picture.jpg";
    if (!empty($thumbnail)) {
        $path = $thumbnail[0]["url"];
    }
    return $path;
}

//获取附件路径
function getEnclosurePath($enclosure): array
{
    $path = [];
    if (!empty($enclosure)) {
        foreach ($enclosure as $k => $val) {
            $enclosureInfo = [];
            $enclosureInfo["path"] = $val["url"];
            $enclosureInfo['file_size'] = trans_byte(filesize('../..' . $val["url"]));
            $path[] = $enclosureInfo;
        }
    }
    return $path;
}

//获取图片相册（多图）路径
function getPhotoAlbumPath($photoAlbum): array
{
    $path = array();
    if (!empty($photoAlbum)) {
        foreach ($photoAlbum as $k => $val) {
            $path[] = $val["url"];
        }
    }
    return $path;
}

//定义计算文件大小的函数，以常见的格式显示
function trans_byte($byte): string
{
    $KB = 1024;
    $MB = 1024 * $KB;
    $GB = 1024 * $MB;
    $TB = 1024 * $GB;
    if ($byte < $KB) {
        return $byte . 'B';
    } else if ($byte < $MB) {
        return round($byte / $KB, 2) . 'KB';
    } else if ($byte < $GB) {
        return round($byte / $MB, 2) . 'MB';
    } else if ($byte < $TB) {
        return round($byte / $GB, 2) . 'GB';
    } else {
        return round($byte / $TB, 2) . 'TB';
    }
}

/*获取对应表的自定义字段内容
参数$table_name：要获取内容的表名
参数$id:对应的记录ID*/
function getFieldInfo($table_name, $id)
{
    $field_info = array();
    if ($id > 0 && is_numeric($id)) {
        //sql查询语句
        $sql_field_info = "select field_name,field_content,(select field_type from field_custom where table_name='{$table_name}' and field_name=field_info.field_name) as field_type from field_info where table_name='{$table_name}' and record_id={$id}";
        //获取查询结果集
        $res_field_info = my_sql($sql_field_info);
        if ($res_field_info) {
            while ($row = mysqli_fetch_assoc($res_field_info)) {
                if ($row["field_type"] == 3) {
                    $row["field_content"] = json_decode($row["field_content"]);
                } else if ($row["field_type"] == 5) {
                    $row["field_content"] = getThumbnailPath(object_array(json_decode($row["field_content"])));
                } else if ($row["field_type"] == 6) {
                    $row["field_content"] = getPhotoAlbumPath(object_array(json_decode($row["field_content"])));
                } else if ($row["field_type"] == 7) {
                    $row["field_content"] = getEnclosurePath(object_array(json_decode($row["field_content"])));
                }
                $field_info[$row["field_name"]] = $row["field_content"];
            }
        }
    }
    return $field_info;
}

//处理自定义字段信息
function processFieldInfo($field_data): array
{
    // 处理合并后的字段数据
    $field_info = array();
    if (!empty($field_data)) {
        $field_items = explode('|||', $field_data);
        foreach ($field_items as $item) {
            list($field_name, $field_content, $field_type) = explode('::', $item);

            // 根据字段类型处理内容
            if ($field_type == 3) {
                $field_content = json_decode($field_content);
            } else if ($field_type == 5) {
                $field_content = getThumbnailPath(object_array(json_decode($field_content)));
            } else if ($field_type == 6) {
                $field_content = getPhotoAlbumPath(object_array(json_decode($field_content)));
            } else if ($field_type == 7) {
                $field_content = getEnclosurePath(object_array(json_decode($field_content)));
            }

            $field_info[$field_name] = $field_content;
        }
    }

    return $field_info;
}

/* 加密/解密
 * 参数$data：要加密/解密的内容--加密明文/解密密文
 * 参数$type：要加密还是解密（1：加密；2：解密）*/
function my_crypt($data, int $type)
{
    $method = 'aes-128-ecb'; //加密/解密方式，可以通过openssl_get_cipher_methods()获取有哪些加密方式
    $key = 'webforce'; //秘钥
    $options = 0; //以下标记的按位或： OPENSSL_RAW_DATA 、 OPENSSL_ZERO_PADDING

    if ($type === 1) {
        return openssl_encrypt($data, $method, $key, $options);
    } else if ($type === 2) {
        return openssl_decrypt($data, $method, $key, $options);
    }
}

/*剔除没有访问权限的分类id
 * @param $data：需要被检测有没有权限的参数
 * @param $table：要检查的表*/
function eliminateID($data, $table): string
{
    if (session_status() !== PHP_SESSION_ACTIVE) session_start(); //开启session
    $userpower = $_SESSION['u_power'] ?? 'no_login';
    $id = "";
    $sql = "select id from {$table} where id in({$data}) and if(isnull(allow_access)=1||length(trim(allow_access))=0,id>0,allow_access like '%\"{$userpower}\"%')"; //筛选出符合访问条件的id

    $res = my_sql($sql);
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $id .= $row["id"] . ",";
        }
        $id = rtrim($id, ",");
    }
    return $id;
}

//判断当前网址有没有发送短信的余量
function canSendSMS()
{
    $result = false;
    $domain = $_SERVER['HTTP_HOST']; //获取当前网址
    @$web_info = file_get_contents("http://sitemanage.webforce.com.cn/page/getwebinfo.php?domain=" . $domain);
    $web_info = json_decode($web_info, true);
    if ((int)$web_info['obj']['data']['sms_number'] > 0) {
        $result = true;
    }
    return $result;
}

//更新短信的余量
function updateNumberSMS()
{
    $result = false;
    $domain = $_SERVER['HTTP_HOST']; //获取当前网址
    @$number_update = file_get_contents("http://sitemanage.webforce.com.cn/page/sms_number.php?num=1&domain=" . $domain);
    $number_update = json_decode($number_update, true);
    if ($number_update['message'] == 'success') {
        $result = true;
    }
    return $result;
}

//获取当前网址
function getUrl()
{
    // 获取协议类型
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http';
    // 获取主机名(包括域名和端口)
    $host = $_SERVER['HTTP_HOST'];

    // 返回组合完整的网址
    return $protocol . '://' . $host;
}

/*当$id为单个ID时，获取该文章分类下的所有子分类的ID
@param int $parent_id：文章分类ID*/
function getNewsClassChildID(mysqli $link, $parent_id): string
{
    $where_child = "";
    $sql_child = "select id from news_class where parentid = ?";
    $stmt = $link->prepare($sql_child); //准备SQL语句
    $stmt->bind_param('i', $parent_id); //绑定参数
    //执行SQL语句
    if ($stmt->execute()) {
        $res = $stmt->get_result(); //获取查询结果
        while ($row = $res->fetch_assoc()) {
            $where_child .= getNewsClassChildID($link, $row["id"]);
            $where_child .= $row["id"] . ",";
        }
    }
    // 关闭语句
    $stmt->close();

    return $where_child;
}

/*当$id为单个ID时，获取该产品分类下的所有子分类的ID
@param int $parentid：文章分类ID*/
function getProductClassChildID(mysqli $link, $parent_id): string
{
    $where_child = "";
    $sql_child = "select id from product_class where parentid = ?";
    $stmt = $link->prepare($sql_child); //准备SQL语句
    $stmt->bind_param('i', $parent_id); //绑定参数
    //执行SQL语句
    if ($stmt->execute()) {
        $res = $stmt->get_result(); //获取查询结果
        while ($row = $res->fetch_assoc()) {
            $where_child .= getProductClassChildID($link, $row["id"]);
            $where_child .= $row["id"] . ",";
        }
    }
    // 关闭语句
    $stmt->close();

    return $where_child;
}

/* 处理要多表联动的字段
 * @param string $field 要处理的字段，字段之间用英文逗号隔开
 * @param string $abbr 对应表名缩写
 */
function processTableField($field, $abbr): string
{
    $field_array = [];
    foreach (explode(',', $field) as $item) {
        $field_array[] = "{$abbr}.{$item}";
    }

    return implode(',', $field_array);
}

/*
 * 处理链接
 * @param string url 要处理的链接*/
function processURL($url): string
{
    // 定义要剔除的关键词数组（便于维护）
    $excludePatterns = [
        'http://',
        'javascript:void(0)',
        '#no'
    ];

    // 检查字符串是否包含特定的内容
    $containsExcluded = false;

    if (!empty($url)) {
        foreach ($excludePatterns as $pattern) {
            if (strpos($url, $pattern) !== false) {
                $containsExcluded = true;
                break;
            }
        }
    } else {
        return '';
    }

    if ($containsExcluded) {
        return $url;
    } else {
        return '/' . $url;
    }
}

/*
 * 注册用户时，检查用户名有没有重复的
 * @param array $field 要查询的字段
 * @param string $field_value 要查询的字段值*/
function haveSameUser(mysqli $link, $field, $field_value): bool
{
    $result = true; //是否有相同的用户名，默认是有

    $sql = "select id from user where {$field}=?"; //判断当前账号有没有注册过
    //准备SQL语句
    $stmt = $link->prepare($sql);
    //绑定参数
    $stmt->bind_param('s', $field_value);
    //执行查询
    if ($stmt->execute()) {
        //获取查询结果集
        $res = $stmt->get_result();
        $result = ($res->num_rows > 0);
    }

    return $result;
}

//解密前端传过来的参数
function decryptIndex($data, $ivBase64)
{
    // 检查输入参数
    if (empty($data) || empty($ivBase64)) {
        return ['success' => false, 'error' => '解密参数不能为空'];
    }
    
    // 将Base64字符串转换为二进制数据
    $iv = base64_decode($ivBase64);
    $ciphertext = base64_decode($data);
    
    // 检查Base64解码是否成功
    if ($iv === false || $ciphertext === false) {
        return ['success' => false, 'error' => 'Base64解码失败'];
    }
    
    // 检查IV长度是否符合AES-CBC的要求（16字节）
    if (strlen($iv) !== 16) {
        return ['success' => false, 'error' => 'IV长度不正确'];
    }
    
    // 密钥（必须与前端加密时使用的密钥相同）
    $secretKey = 'fMce8fVH5tGOEK7CicKC23MQM0n0pZaM'; // 必须是32字节长（256位）
    
    // 解密数据
    $decrypted = openssl_decrypt($ciphertext, 'aes-256-cbc', $secretKey, OPENSSL_RAW_DATA, $iv);
    
    // 检查解密是否成功
    if ($decrypted === false) {
        // 获取具体的错误信息
        $errorMsg = openssl_error_string() ?: '解密失败';
        return ['success' => false, 'error' => $errorMsg];
    }
    
    // 返回成功结果和解密后的数据
    return ['success' => true, 'data' => $decrypted];
}