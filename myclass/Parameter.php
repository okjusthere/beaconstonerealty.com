<?php

namespace myclass;

class Parameter
{
    /* 获取指定参数信息
     * @param string $parameter_key 要获取的参数名*/
    public static function getParameter(string $parameter_key)
    {
        $parameter_value = ''; //要获取的参数名对应的值
        $path = dirname(__DIR__) . '/application/admin/config/parameter.json'; //json数据文件路径
        $data = file_get_contents($path);
        $data = json_decode($data, true);

        //判断当前后台有没有启用自定义参数，若启用了，从本站中获取参数，若没有调用远程参数（接收信息的手机号和邮箱号始终调用本站的）
        if($data[0]["parameter_value"]||$parameter_key==="phoneNumber"||$parameter_key==="email"){
            foreach ($data as $k => $val) {
                if ($val["parameter_key"] === $parameter_key) {
                    $parameter_value = my_crypt($val["parameter_value"], 2);
                }
            }
        }else{
            @$sitemanage_info=file_get_contents("http://sitemanage.webforce.com.cn/page/config/parameter.json");
            $data_sitemanage = json_decode($sitemanage_info, true);
            foreach ($data_sitemanage as $k => $val) {
                if ($val["parameter_key"] === $parameter_key) {
                    $parameter_value = my_crypt($val["parameter_value"], 2);
                }
            }
        }

        return $parameter_value;
    }
}