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

//获取缩略图路径
function getThumbnailPath($thumbnail): string
{
    $path = "/images/no_picture.jpg";
    if (!empty($thumbnail)) {
        $path = $thumbnail[0]["url"];
    }
    return $path;
}

//解密前端传过来的参数
function decrypt($data, $ivBase64)
{
    // 将Base64字符串转换为二进制数据
    $iv = base64_decode($ivBase64);
    $ciphertext = base64_decode($data);

    // 密钥（必须与前端加密时使用的密钥相同）
    $secretKey = 'bXl86SP9WsIpkMGVSBjhxHibC53Kmch3'; // 必须是32字节长（256位）

    // 解密数据
    $decrypted = openssl_decrypt($ciphertext, 'aes-256-cbc', $secretKey, OPENSSL_RAW_DATA, $iv);

    return $decrypted;
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

/* 获取相关分类所选的详情模板ID
 * @param string $table 要查询的表名
 * @param int $id 要查询的ID
 */
function getDetailTemplateId($table, $id)
{
    global $link;

    $template_id = 0;
    $sql = "SELECT detail_template_id FROM `$table` WHERE id=?";
    $stmt = $link->prepare($sql); //准备SQL语句
    $stmt->bind_param('i', $id); //绑定参数
    //执行查询
    $stmt->execute();
    // 获取结果
    $result = $stmt->get_result();
    if ($result) {
        $row = $result->fetch_assoc();
        $template_id = $row["detail_template_id"];
    }

    $stmt->close(); // 关闭语句

    return $template_id;
}

//查看是否有相同伪静态链接存在
function haveSameStaticUrl($url)
{
    global $link;

    $sql = "SELECT id FROM tb_rewrite_rules WHERE static_url=? LIMIT 1";
    $stmt = $link->prepare($sql);
    $stmt->bind_param('s', $url);
    //执行查询
    $stmt->execute();
    $result = $stmt->get_result();
    $number = $result->num_rows;
    $stmt->close();

    return $number > 0;
}

//通过路由模板ID获取对应的页面名称，从而自动匹配路由名称
function getRoutePage($template_id): string
{
    global $link;
    $page = '';

    $sql = "SELECT route_page FROM tb_route_template WHERE id=?";
    $stmt = $link->prepare($sql);

    if ($stmt) {
        //绑定参数
        $stmt->bind_param('i', $template_id);

        // 执行查询
        $stmt->execute();

        // 获取结果
        $result = $stmt->get_result();
        if ($result) {
            $row = $result->fetch_assoc();
            if ($row) {
                // 获取结果，并返回去除后缀名的页面名称
                $page_name = $row["route_page"];
                $page = preg_replace('/\.html$/', '', $page_name);
            }
        }
    }

    $stmt->close(); // 关闭语句

    return $page;
}

//路由表插入路由数据
function insertRewriteRules($static_url, $template_id, ?string $is_custom = '1', ?string $params = null): int
{
    global $link;
    $r_id = 0;

    $sql = "INSERT INTO tb_rewrite_rules (static_url,template_id,is_custom,params) VALUES (?,?,?,?)";
    $stmt = $link->prepare($sql);
    if ($stmt) {
        //绑定参数
        $stmt->bind_param('siss', $static_url, $template_id, $is_custom, $params);

        //执行插入
        $result = $stmt->execute();
        if ($result) {
            $r_id = $stmt->insert_id; //获取插入的ID
        }
    }

    $stmt->close(); // 关闭语句

    return $r_id;
}

//通过路由ID获取对应的路由信息
function getRewriteRules(mysqli $link, $id)
{
    $data = new stdClass(); //返回的数据
    $sql = "SELECT static_url,params FROM tb_rewrite_rules WHERE id=?";
    $stmt = $link->prepare($sql);
    if ($stmt) {
        //绑定参数
        $stmt->bind_param('i', $id);

        //执行语句
        if ($stmt->execute()) {
            $result = $stmt->get_result(); //获取查询结果
            $data = $result->fetch_assoc();
            $data["params"] = json_decode($data["params"], true);
        }
    }

    $stmt->close(); // 关闭语句
    return $data;
}

/**
 * 封装更新语句
 * @param string $table 需要更新的表
 * @param array $whereClauseParams 子条件值
 * @param array $whereColumns 子条件名
 * @param array $data 需更新的值
 */
function updateRecord($table, $whereClauseParams, $whereColumns, $data)
{
    global $link;

    if (empty($data) || count($whereClauseParams) !== count($whereColumns)) {
        return false;
    }
    $setParts = [];
    $typesSet = '';
    $bindParamsSet = [];

    foreach ($data as $column => $value) {
        $setParts[] = "`$column` = ?";
        $typesSet .= (is_int($value)) ? 'i' : 's';
        $bindParamsSet[] = $value;
    }

    $setClause = implode(', ', $setParts);

    // 构建WHERE部分的SQL语句和参数类型字符串
    $whereParts = [];
    $typesWhere = '';
    $bindParamsWhere = [];

    for ($i = 0; $i < count($whereClauseParams); $i++) {
        $whereParts[] = "`{$whereColumns[$i]}` = ?";
        $typesWhere .= (gettype($whereColumns[$i]) === 'integer') ? 'i' : 's';
        $bindParamsWhere[] = &$whereClauseParams[$i]; // 引用传递
    }

    $whereClause = implode(' AND ', $whereParts);
    // 构建完整的SQL语句
    $sql = "UPDATE `$table` SET $setClause WHERE $whereClause";
    // 准备SQL语句
    $stmt = $link->prepare($sql);

    if ($stmt === false) {
        return false;
    }
    // 合并参数类型和参数值
    $types = $typesSet . $typesWhere;
    $bindParams = array_merge($bindParamsSet, $bindParamsWhere);

    // 使用call_user_func_array来绑定参数
    $bindRefs = [];
    for ($i = 0; $i < count($bindParams); $i++) {
        $bindRefs[] = &$bindParams[$i];
    }

    call_user_func_array([$stmt, 'bind_param'], array_merge([$types], $bindRefs));

    // 执行SQL语句
    if ($stmt->execute()) {
        $result = ($stmt->affected_rows === 0 || $stmt->affected_rows > 0); //获取执行结果
        // 关闭语句
        $stmt->close();
        return $result; // 返回受影响的行数
    } else {
        // 关闭语句
        $stmt->close();
        return false;
    }
}

/*
 * 封装删除语句
 * @param string $table 需要进行删除操作的表
 * @param array $whereClauseParams 子条件值
 * @param array $whereColumns 子条件名
 */
function deleteRecord($table, $whereClauseParams, $whereColumns): bool
{
    global $link;

    //校验必要参数
    if (empty($table) || count($whereClauseParams) !== count($whereColumns)) {
        return false;
    }

    // 构建WHERE部分的SQL语句和参数类型字符串
    $whereParts = []; //条件数组
    $bindParamsTypes = ''; //参数类型
    $bindParams = []; //绑定参数

    for ($i = 0; $i < count($whereClauseParams); $i++) {
        $whereParts[] = "`{$whereColumns[$i]}` = ?";
        $bindParamsTypes .= (gettype($whereColumns[$i]) === 'integer') ? 'i' : 's';
        $bindParams[] = &$whereClauseParams[$i]; // 引用传递
    }

    $where = implode(' AND ', $whereParts); //用and拼接删除条件

    //sql语句
    $sql = "DELETE FROM `$table` WHERE {$where}";

    // 准备SQL语句
    $stmt = $link->prepare($sql);
    if (!$stmt) return false; //准备sql语句失败

    //将数组转换为字符串，并绑定参数
    $stmt->bind_param($bindParamsTypes, ...$bindParams);

    //执行语句
    $result = $stmt->execute();
    $stmt->close();

    return $result;
}

/*
 * 封装删除多条记录语句-只支持通过ID删除
 * @param string $table 需要进行删除操作的表
 * @param array $idAry 子条件值
 */
function deleteRecords($table, $idAry): bool
{
    global $link;

    //校验必要参数
    if (empty($table) || count($idAry) === 0) return false;

    $where = implode(',', array_fill(0, count($idAry), '?')); //占位符数据
    $bindParamsTypes = str_repeat('i', count($idAry)); //参数类型

    //SQL语句
    $sql = "DELETE FROM `$table` WHERE ID IN ({$where})";

    // 准备SQL语句
    $stmt = $link->prepare($sql);
    if (!$stmt) return false; //准备sql语句失败

    //将数组转换为字符串，并绑定参数
    $stmt->bind_param($bindParamsTypes, ...$idAry);

    //执行语句
    $result = $stmt->execute();
    $stmt->close();

    return $result;
}