<?php
//获取后台路由
//查看是否有访问权限
include_once '../checking_user.php';

//链接数据库
include_once '../../../wf-config.php';
global $link;

//获取自定义的返回json的函数
include_once '../../../myclass/ResponseJson.php';

class myData
{
    use \myclass\ResponseJson\ResponseJson;
}

$jsonData = new myData();

$code = 500;  //响应码
$message = '未响应';  //响应信息
$obj = ['data' => []]; // 返回对象（数据）

header('Content-Type:application/json; charset=utf-8');

 if (session_status() !== PHP_SESSION_ACTIVE) session_start(); //开启session
 if (empty($_SESSION["manager_username"]) && my_crypt($_SESSION["manager_username"], 2) != '13812345678') {
     echo $jsonData->jsonData(201, '您没有管理该页面的权限！', []);
     exit;
 }

try {
    // 准备主查询SQL - 按层级、排序值和ID排序
    $sql = "SELECT id,parent_id,router_level,router_path,router_name,component,router_icon,hidden,sort,add_time FROM tb_system_router ORDER BY router_level ASC, sort DESC, id ASC";
    $stmt = $link->prepare($sql);

    if (!$stmt) throw new Exception('路由查询语句准备失败' . $link->error);
    if (!$stmt->execute()) throw new Exception('路由查询执行失败' . $stmt->error);

    $res = $stmt->get_result();
    $routerData = [];
    while ($row = $res->fetch_assoc()) {
        $routerData[] = $row;
    }
    $obj["data"] = buildTree($routerData);
    $code = 200;
    $message = 'success';
} catch (Exception $e) {
    $code = 100;
    $message = $e->getMessage();
} finally {
    if (isset($stmt) && $stmt instanceof mysqli_stmt) {
        $stmt->close();
    }
}

/**
 * 构建树形结构
 * @param array $data 原始数据
 * @return array 树形结构数据
 */
function buildTree($data) {
    $tree = [];

    // 按层级分组
    $levelMap = [];
    foreach ($data as $item) {
        $level = $item['router_level'];
        if (!isset($levelMap[$level])) {
            $levelMap[$level] = [];
        }
        $levelMap[$level][$item['id']] = $item;
    }

    // 从最底层开始构建树（反向处理）
    $levels = array_keys($levelMap);
    if (empty($levels)) {
        return $tree;
    }

    $maxLevel = max($levels);

    // 从最底层开始向上构建
    for ($currentLevel = $maxLevel; $currentLevel > 0; $currentLevel--) {
        if (!isset($levelMap[$currentLevel])) continue;

        foreach ($levelMap[$currentLevel] as $id => $node) {
            $parentId = $node['parent_id'];
            $parentLevel = $currentLevel - 1;

            // 在父级中找到对应的节点并添加为子节点
            if (isset($levelMap[$parentLevel][$parentId])) {
                if (!isset($levelMap[$parentLevel][$parentId]['children'])) {
                    $levelMap[$parentLevel][$parentId]['children'] = [];
                }

                // 构建子节点数据结构（临时包含sort用于排序）
                $childNode = buildNodeStructure($node, true); // true表示包含sort字段

                // 如果当前节点有children，保持它们
                if (isset($node['children'])) {
                    $childNode['children'] = $node['children'];
                }

                // 添加到父节点的children数组中
                $levelMap[$parentLevel][$parentId]['children'][] = $childNode;
            }
        }

        // 对当前层级的每个父节点的子节点按照sort排序
        foreach ($levelMap[$currentLevel] as $id => $node) {
            $parentId = $node['parent_id'];
            $parentLevel = $currentLevel - 1;

            if (isset($levelMap[$parentLevel][$parentId]['children'])) {
                // 按照sort字段从大到小排序
                usort($levelMap[$parentLevel][$parentId]['children'], function($a, $b) {
                    $sortA = isset($a['sort']) ? $a['sort'] : 0;
                    $sortB = isset($b['sort']) ? $b['sort'] : 0;
                    return $sortB - $sortA;
                });

                // 排序后移除sort字段
                foreach ($levelMap[$parentLevel][$parentId]['children'] as &$child) {
                    if (isset($child['sort'])) unset($child['sort']);
                }
            }
        }
    }

    // 处理根节点（level = 0）
    if (isset($levelMap[0])) {
        // 先构建根节点（临时包含sort用于排序）
        $rootNodes = [];
        foreach ($levelMap[0] as $rootNode) {
            $treeNode = buildNodeStructure($rootNode, true); // true表示包含sort字段

            // 如果根节点有children，添加它们
            if (isset($rootNode['children'])) {
                $treeNode['children'] = $rootNode['children'];
            }

            $rootNodes[] = $treeNode;
        }

        // 对根节点按照sort从大到小排序
        usort($rootNodes, function($a, $b) {
            return $b['sort'] - $a['sort'];
        });

        // 排序后移除sort字段
        foreach ($rootNodes as &$node) {
            if (isset($node['sort'])) unset($node['sort']);
        }

        $tree = $rootNodes;
    }

    return $tree;
}

/**
 * 构建节点数据结构
 * @param array $node 数据库节点数据
 * @param bool $includeSort 是否包含sort字段（用于排序）
 * @return array 格式化后的节点数据
 */
function buildNodeStructure($node, $includeSort = false) {
    $treeNode = [
        'path' => $node['router_path'],
        'name' => $node['router_name'],
        'component' => $node['component']
    ];

    // 如果需要包含sort字段用于排序
    if ($includeSort) {
        $treeNode['sort'] = $node['sort'];
    }

    // 构建meta信息
    $meta = [];
    if (!empty($node['router_icon'])) {
        $meta['icon'] = $node['router_icon'];
    }
    // 转换hidden字段：1展示->false, 2不展示->true
    $meta['hidden'] = ($node['hidden'] == '2');

    if (!empty($meta)) {
        $treeNode['meta'] = $meta;
    }

    return $treeNode;
}

//关闭数据库链接
mysqli_close($link);

echo $jsonData->jsonData($code, $message, $obj);