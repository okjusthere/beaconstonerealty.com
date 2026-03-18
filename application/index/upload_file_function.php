<?php
//PHP文件上传功能封装函数
/*
 * 实现（单）文件上传
 * param（中文意思-》参数）
 * @param1 array $file 需要上传的文件信息：一维五元素数组（name,tmp_name,type,error,size）
 * @param2 array $allow_type 允许上传的MIME类型
 * @param3 string $path 存储的路径
 * @param4 string &$error 如果出现错误的原因
 * @param5 array $allow_format=array() 允许上传的文件格式
 * @param6 int $max_size=2000000 允许上传的最大值
 */
function upload_single($file, $allow_type, $path, &$error, $allow_format = array(), $max_size = 2000000)
{
    //判断文件是否有效
    if (!is_array($file) || !isset($file['error'])) {
        //文件无效
        $error = '不是一个有效的上传文件！';
        return false;
    }

    //判断文件存储路径是否有效
    if (!is_dir($path)) {
        //路径不存在
        $error = $path;
        return false;
    }

    //判断文件上传过程是否出错
    switch ($file['error']) {
        case 1:
        case 2:
            $error = '文件超出服务器允许大小！';
            return false;
        case 3:
            $error = '文件上传过程中出现问题，只上传了一部分！';
            return false;
        case 4:
            $error = '用户没有选中要上传的文件！';
            return false;
        case 6:
        case 7:
            $error = '文件保存失败！';
            return false;
    }

    //判断MIME类型
    if (!in_array($file['type'], $allow_type)) {
        //该文件类型不允许上传
        $error = '当前文件类型不允许上传！';
        return false;
    }

    //判断文件后缀是否允许
    //取出后缀
    $ext = ltrim(strrchr($file['name'], '.'), '.');
    if (!empty($allow_format) && !in_array($ext, $allow_format)) {
        //不允许上传
        $error = '当前文件格式不允许上传！';
        return false;
    }

    //判断当前文件大小是否满足当前需求
    if ($file['size'] > $max_size) {
        //文件过大
        $error = '当前上传的文件超出大小，最大允许' . $max_size . '字节';
        return false;
    }

    //构造文件名字：类型_年月日+随机字符串.$ext
    $fullname = strstr($file['type'], '/', true) . '_' . date('YmdHis');
    //产生随机字符串
    for ($i = 0; $i < 4; $i++) {
        $fullname .= chr(mt_rand(65, 90));
    }
    //拼凑后缀
    $fullname .= '.' . $ext;

    //移动到指定目录
    if (!is_uploaded_file($file['tmp_name'])) {
        //文件不是上传的
        $error = '错误：不是上传文件！';
        return false;
    }
    if (move_uploaded_file($file['tmp_name'], $path . '/' . $fullname)) {
        //成功
        //$info = ['name' => $fullname, 'url' => str_replace('.', '', $path) . '/' . $fullname];
        $info = ['name' => $file['name'], 'url' => '/' . str_replace('../', '', $path) . '/' . $fullname];

        //return str_replace('.', '', $path) . '/' . $fullname;
        return $info;
    } else {
        //移动失败
        $error = 'error';
        return false;
    }

}