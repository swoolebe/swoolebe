<?php

namespace System;

/**
 *  运行时
 * @package System
 *
 */
class HttpServer
{
    private $server = null;

    private $supportMime = [
        'html' => 'text/html',
        'htm' => 'text/html',
        'xhtml' => 'application/xhtml+xml',
        'xml' => 'text/xml',
        'txt' => 'text/plain',
        'log' => 'text/plain',

        'js' => 'application/javascript',
        'json' => 'application/json',
        'css' => 'text/css',

        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif' => 'image/gif',
        'png' => 'image/png',
        'bmp' => 'image/bmp',
        'ico' => 'image/icon',
        'svg' => 'image/svg+xml',

        'mp3' => 'audio/mpeg',
        'wav' => 'audio/wav',

        'mp4' => 'video/avi',
        'avi' => 'video/avi',
        '3gp' => 'application/octet-stream',
        'flv' => 'application/octet-stream',
        'swf' => 'application/x-shockwave-flash',

        'zip' => 'application/zip',
        'rar' => 'application/octet-stream',

        'ttf' => 'application/octet-stream',
        'fon' => 'application/octet-stream',

        'doc' => 'application/msword',
        'xls' => 'application/vnd.ms-excel',
        'ppt' => 'application/vnd.ms-powerpoint',
        'mdb' => 'application/msaccess',
        'chm' => 'application/octet-stream',

        'pdf' => 'application/pdf',
    ];

    public function __construct()
    {
    }


    public function start()
    {
        $this->server = new \swoole_http_server('0.0.0.0', 80);
        $this->server->on('request', [$this, 'onRequest']);

        /*
        $this->server->on('Start', [$this, 'onStart']);
        $this->server->on('WorkerStart', [$this, 'onWorkerStart']);
        $this->server->on('WorkerStop', [$this, 'onWorkerStop']);
        $this->server->on('Task', [$this, 'onTask']);
        $this->server->on('Finish', [$this, 'onFinish']);
        $this->server->on('PipeMessage', [$this, 'onPipeMessage']);
        $this->server->on('WorkerError', [$this, 'onWorkerError']);
        $this->server->on('ManagerStart', [$this, 'onManagerStart']);
        $this->server->on('ManagerStop', [$this, 'onManagerStop']);
        $this->server->on('Shutdown', [$this, 'onShutdown']);
        */

        $this->server->start();
    }

    public function onRequest(\Swoole\Http\Request $request, \Swoole\Http\Response $response) {

        /*
         * REQUEST_URI 结构：/{controller}/{action}[?[k=v]
         */
        $uri = $request->server['request_uri'];;    // 返回值为:

        $rootPath = Be::getRuntime()->getRootPath();
        if (file_exists($rootPath . $uri)) {
            $ext = strtolower(pathinfo($uri, PATHINFO_EXTENSION));
            if (isset($this->supportMime[$ext])) {
                $response->header('Content-Type', $this->supportMime[$ext], false);
                $response->sendfile($rootPath . $uri);
            }
        }

        $controller = 'Index';
        $action = 'index';

        // /{controller}/{action}/
        $uris = explode('/', $uri);
        $len = count($uris);
        if ($len > 1 && $uris[1]) {
            $controller = $uris[1];
        }

        if ($len > 2 && $uris[2]) {
            $action = $uris[2];
        }

        try {

            $instance = Be::getController($controller);
            if (method_exists($instance, $action)) {
                $instance->$action(new Request($request), new Response($response));
            } else {
                $response->status(404);
                $response->end('<meta charset="utf-8" />未定义的任务：' . $action);
            }

        } catch (\Throwable $e) {

            /*
            Log::emergency($e->getMessage(), [
                'code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTrace()
            ]);
            */

            $response->status(404);
            $response->end('<meta charset="utf-8" />系统错误：' . $e->getMessage());

        }
    }

}
