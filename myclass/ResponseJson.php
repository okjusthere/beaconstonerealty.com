<?php

namespace myclass\ResponseJson;
//header("Content-type:text/html;charset=utf-8");

trait ResponseJson
{
    //当接口出现业务异常时的返回
    public function jsonData($code, $message, $obj = [])
    {
        return $this->jsonResponse($code, $message, $obj);
    }

    //接口请求成功时的返回
    public function jsonSuccessData($data = [])
    {
        return $this->jsonResponse(200, 'success', $data);
    }

    //定义一个返回json的函数
    private function jsonResponse($code, $message, $obj)
    {
        $content = ['code' => $code, 'message' => $message, 'obj' => $obj];
        return json_encode($content);
    }
}