<?php

use AlibabaCloud\Client\AlibabaCloud;
use AlibabaCloud\Client\Exception\ClientException;
use AlibabaCloud\Client\Exception\ServerException;
use AlibabaCloud\Client\Request\RpcRequest;

// 导入自动加载文件
require_once(dirname(__DIR__) . '/vendor/autoload.php');
// 导入调用参数的方法
require_once(dirname(__DIR__) . '/myclass/Parameter.php');

$param = new \myclass\Parameter(); //获取相关参数

// 配置阿里云Access Key ID和Access Key Secret
AlibabaCloud::accessKeyClient($param::getParameter('accessKeyId'), $param::getParameter('accessKeySecret'))
    ->regionId('cn-hangzhou')
    ->asDefaultClient();

// 调用阿里云短信发送接口
function sendSMS($phoneNumber, $templateCode, $templateParamArray)
{
    try {
        $request = new RpcRequest();
        $request->product('Dysmsapi');
        $request->version('2017-05-25');
        $request->action('SendSms');
        $request->method('POST');
        $request->host('dysmsapi.aliyuncs.com');

        // 设置请求参数
        $request->options([
            'query' => [
                'PhoneNumbers' => $phoneNumber,
                'SignName' => myclass\Parameter::getParameter('signName'),
                'TemplateCode' => $templateCode,
                'TemplateParam' => json_encode($templateParamArray)
            ],
        ]);

        $response = $request->connectTimeout(20)->timeout(25)->request();

        return $response->toArray();

    } catch (ClientException $e) {
        // 处理客户端异常
        return ['Code' => 'Error', 'Message' => $e->getErrorMessage()];
    } catch (ServerException $e) {
        // 处理服务端异常
        return ['Code' => 'Error', 'Message' => $e->getErrorMessage()];
    }
}
