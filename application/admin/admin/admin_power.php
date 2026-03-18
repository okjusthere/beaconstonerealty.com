<?php
//查看是否有访问权限
include_once '../checking_user.php';

//获取自定义的返回json的函数
include_once '../../../myclass/ResponseJson.php';

class myData
{
    use \myclass\ResponseJson\ResponseJson;
}

$jsonData = new myData();

$code = 200;  //响应码
$message = 'success';  //响应信息
$obj = array(); //返回对象（数据）
$obj["data"] = array(
    array(
        'path' => 'webset',
        'label' => '网站管理',
        'isTransverse' => true,
        'children' => array(
            array('path' => 'webinfo', 'label' => '网站信息修改'),
            array('path' => 'webseo', 'label' => '网站优化修改'),
            array('path' => 'webcontrol', 'label' => '网站开关管理'),
            array('path' => 'code', 'label' => '嵌入代码修改')
        )
    ),
    array(
        'path' => 'pic',
        'label' => '图片管理',
        'isTransverse' => true,
        'children' => array(
            array('path' => 'pic_list', 'label' => '浏览图片'),
            array('path' => 'pic_add', 'label' => '添加图片'),
            array('path' => 'pic_edit', 'label' => '修改图片'),
            array('path' => 'pic_delete', 'label' => '删除图片'),
        )
    ),
    array(
        'path' => 'picposition',
        'label' => '图片位置管理',
        'isTransverse' => true,
        'children' => array(
            array('path' => 'pic_position_list', 'label' => '浏览图片位置'),
            array('path' => 'pic_position_add', 'label' => '添加图片位置'),
            array('path' => 'pic_position_edit', 'label' => '修改图片位置'),
            array('path' => 'pic_position_delete', 'label' => '删除图片位置')
        )
    ),
    array(
        'path' => 'column',
        'label' => '导航菜单',
        'isTransverse' => true,
        'children' => array(
            array('path' => 'column_list', 'label' => '浏览导航菜单'),
            array('path' => 'column_add', 'label' => '添加导航菜单'),
            array('path' => 'column_edit', 'label' => '修改导航菜单'),
            array('path' => 'column_delete', 'label' => '删除导航菜单')
        )
    ),
    array(
        'path' => 'page',
        'label' => '页面管理',
        'isTransverse' => true,
        'children' => array(
            array('path' => 'page_list', 'label' => '浏览页面'),
            array('path' => 'page_add', 'label' => '添加页面'),
            array('path' => 'page_edit', 'label' => '修改页面'),
            array('path' => 'page_delete', 'label' => '删除页面')
        )
    ),
    array(
        'path' => 'news',
        'label' => '文章管理',
        'isTransverse' => true,
        'children' => array(
            array('path' => 'news_list', 'label' => '浏览文章'),
            array('path' => 'news_add', 'label' => '添加文章'),
            array('path' => 'news_edit', 'label' => '修改文章'),
            array('path' => 'news_delete', 'label' => '删除文章')
        )
    ),
    array(
        'path' => 'newsclass',
        'label' => '文章分类管理',
        'isTransverse' => true,
        'children' => array(
            array('path' => 'newsclass_list', 'label' => '浏览文章分类'),
            array('path' => 'newsclass_add', 'label' => '添加文章分类'),
            array('path' => 'newsclass_edit', 'label' => '修改文章分类'),
            array('path' => 'newsclass_delete', 'label' => '删除文章分类')
        )
    ),
    array(
        'path' => 'product',
        'label' => '产品管理',
        'isTransverse' => true,
        'children' => array(
            array('path' => 'product_list', 'label' => '浏览产品'),
            array('path' => 'product_add', 'label' => '添加产品'),
            array('path' => 'product_edit', 'label' => '修改产品'),
            array('path' => 'product_delete', 'label' => '删除产品')
        )
    ),
    array(
        'path' => 'proclass',
        'label' => '产品分类管理',
        'isTransverse' => true,
        'children' => array(
            array('path' => 'proclass_list', 'label' => '浏览产品分类'),
            array('path' => 'proclass_add', 'label' => '添加产品分类'),
            array('path' => 'proclass_edit', 'label' => '修改产品分类'),
            array('path' => 'proclass_delete', 'label' => '删除产品分类')
        )
    ),
    array(
        'path' => 'query',
        'label' => '查询信息管理',
        'isTransverse' => true,
        'children' => array(
            array('path' => 'query_list', 'label' => '浏览查询信息'),
            array('path' => 'query_add', 'label' => '添加查询信息'),
            array('path' => 'query_edit', 'label' => '修改查询信息'),
            array('path' => 'query_delete', 'label' => '删除查询信息')
        )
    ),
    array(
        'path' => 'message',
        'label' => '客户留言管理',
        'isTransverse' => true,
        'children' => array(
            array('path' => 'message_list', 'label' => '浏览客户留言'),
            array('path' => 'message_delete', 'label' => '删除客户留言')
        )
    ),
    array(
        'path' => 'field',
        'label' => '字段管理',
        'isTransverse' => true,
        'children' => array(
            array('path' => 'field_list', 'label' => '浏览字段'),
            array('path' => 'field_add', 'label' => '添加字段'),
            array('path' => 'field_edit', 'label' => '修改字段'),
            array('path' => 'field_delete', 'label' => '删除字段')
        )
    ),
    array(
        'path' => 'form',
        'label' => '在线表单管理',
        'isTransverse' => true,
        'children' => array(
            array('path' => 'form_list', 'label' => '浏览表单'),
            array('path' => 'form_type_add', 'label' => '添加表单'),
            array('path' => 'form_type_edit', 'label' => '修改表单'),
            array('path' => 'form_delete', 'label' => '删除表单')
        )
    ),
    array(
        'path' => 'admin',
        'label' => '系统账号',
        'isTransverse' => true,
        'children' => array(
            array('path' => 'admin_list', 'label' => '浏览账号'),
            array('path' => 'admin_add', 'label' => '添加账号'),
            array('path' => 'admin_edit', 'label' => '修改账号'),
            array('path' => 'admin_delete', 'label' => '删除账号')
        )
    ),
    array(
        'path' => 'power',
        'label' => '系统账号角色管理',
        'isTransverse' => true,
        'children' => array(
            array('path' => 'power_list', 'label' => '浏览角色'),
            array('path' => 'power_add', 'label' => '添加角色'),
            array('path' => 'power_edit', 'label' => '修改角色'),
            array('path' => 'power_delete', 'label' => '删除角色')
        )
    ),
    array(
        'path' => 'user',
        'label' => '用户管理',
        'isTransverse' => true,
        'children' => array(
            array('path' => 'user_list', 'label' => '浏览用户'),
            array('path' => 'user_add', 'label' => '添加用户'),
            array('path' => 'user_edit', 'label' => '修改用户'),
            array('path' => 'user_delete', 'label' => '删除用户')
        )
    ),
    array(
        'path' => 'user_power',
        'label' => '用户角色管理',
        'isTransverse' => true,
        'children' => array(
            array('path' => 'user_power_list', 'label' => '浏览用户角色'),
            array('path' => 'user_power_add', 'label' => '添加用户角色'),
            array('path' => 'user_power_edit', 'label' => '修改用户角色'),
            array('path' => 'user_power_delete', 'label' => '删除用户角色')
        )
    ),
    array(
        'path' => 'file',
        'label' => '文件管理',
        'isTransverse' => true,
        'children' => array(
            array('path' => 'file_list', 'label' => '浏览文件'),
            array('path' => 'file_upload', 'label' => '上传文件'),
            array('path' => 'file_edit', 'label' => '修改文件'),
            array('path' => 'file_delete', 'label' => '删除文件'),
            array('path' => 'file_packed', 'label' => '备份文件'),
            array('path' => 'file_download', 'label' => '下载文件')
        )
    ),
    array(
        'path' => 'route',
        'label' => '路由管理',
        'isTransverse' => true,
        'children' => array(
            array('path' => 'route_template_list', 'label' => '浏览路由'),
            array('path' => 'route_template_add', 'label' => '添加路由'),
            array('path' => 'route_template_edit', 'label' => '修改路由'),
            array('path' => 'route_template_delete', 'label' => '删除路由')
        )
    )
);


echo $jsonData->jsonData($code, $message, $obj);
