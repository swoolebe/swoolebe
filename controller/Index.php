<?php

namespace Controller;

use System\Be;
use System\PublicController;
use System\Request;
use System\Response;

class Index extends PublicController
{

    const ERR_PARSE = -32700;
    const ERR_REQUEST = -32600;
    const ERR_METHOD = -32601;
    const ERR_PARAMS = -32602;
    const ERR_INTERNAL = -32603;
    const ERR_SERVER = -32000;

    public function index(Request $request, Response $response)
    {
        $response->setHeader('Content-Type', 'application/json; charset=UTF-8');
        $inputString = $request->rawContent();
        if (!$inputString) {
            $response->end(json_encode($this->error(null, static::ERR_REQUEST)));
            return;
        }

        $inputData = json_decode($inputString, false);
        if (!$inputData) {
            $response->end(json_encode($this->error(null, static::ERR_PARSE)));
            return;
        }

        if (is_array($inputData)) {
            $results = [];
            foreach ($inputData as $x) {
                $result = $this->handle($x);
                if ($result) {
                    $results[] = $result;
                }
            }

            $response->end(json_encode($results));
        } else {
            $result = $this->handle($inputData);
            $response->end(json_encode($result));
        }
    }

    public function handle($inputData)
    {
        $inputArray = $this->obj2Arr($inputData);
        $id = false;
        if (isset($inputArray['id'])) {
            $id = $inputArray['id'];
        }

        if (!isset($inputArray['method'])) {
            return $this->error($id, static::ERR_METHOD);
        }

        if (!isset($inputArray['params'])) {
            $inputArray['params'] = [];
        }

        $method = $inputArray['method'];
        $methods = explode('.', $method);
        if (count($methods) != 2) {
            return $this->error($id, static::ERR_METHOD);
        }

        try {
            $serviceName = $methods[0];
            $function = $methods[1];
            $service = Be::getService($serviceName);
            $result = \Swoole\Coroutine::call_user_func_array(array($service, $function), $inputArray['params']);
            return $this->success($id, $result);
        } catch (\Exception $e) {
            return $this->error($id, $e->getCode(), $e->getMessage());
        }
    }


    public function success($id, $result)
    {
        if ($id === false) return false;

        return [
            'jsonrpc' => '2.0',
            'result' => $result,
            'id' => $id
        ];
    }

    public function error($id, $code, $message = null)
    {
        if ($id === false) return false;

        if (!$message) {
            switch ($code) {
                case static::ERR_PARSE:
                    $message = 'Parse error'; // 语法解析错误
                    break;
                case static::ERR_REQUEST:
                    $message = 'Invalid Request';  // 无效请求
                    break;
                case static::ERR_METHOD:
                    $message = 'Method not found'; // 找不到方法
                    break;
                case static::ERR_PARAMS:
                    $message = 'Invalid params'; // 无效的参数
                    break;
                case static::ERR_INTERNAL:
                    $message = 'Internal error'; // 内部错误
                    break;
                case static::ERR_SERVER:
                    $message = 'Server error'; // 服务端错误
                    break;
                default:
                    $message = 'Unknown error'; // 未知错误
                    break;
            }
        }

        return [
            'jsonrpc' => '2.0',
            'error' => [
                'code' => $code,
                'message' => $message
            ],
            'id' => $id
        ];
    }

    public function obj2Arr($obj)
    {
        $arr = (array)$obj;
        foreach ($arr as $k => $v) {
            if (gettype($v) == 'object' || gettype($v) == 'array') {
                $arr[$k] = (array)$this->obj2Arr($v);
            }
        }

        return $arr;
    }


}
