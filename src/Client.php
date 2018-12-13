<?php
namespace xiaodi\Channel;

class Client
{
    protected static $_events = array();

    protected static $_remoteIp = '127.0.0.1';

    protected static $_remotePort = 9501;

    /**
     * 自定义配置
     *
     * @param string $ip
     * @param string $port
     * @return void
     */
    public static function config($ip, $port)
    {
        self::$_remoteIp = $ip;
        self::$_remotePort = $port;
    }

    /**
     * 订阅事件
     *
     * @param [type] $event 事件名
     * @param [type] $callback 回调函数
     * @return void
     */
    public static function on($event, $callback)
    {
        if (PHP_SAPI !== 'cli') {
            throw new \Exception('监听事件只支持CLI模式');
        }

        $client = new \swoole_client(SWOOLE_SOCK_TCP, SWOOLE_SOCK_ASYNC);

        $client->on("connect", function ($cli) use ($event, $callback) {
            $data['type'] = 'subscribe';
            $data['event'] = $event;
            self::$_events[$event] = $callback;
            $cli->send(serialize($data));
        });

        $client->on("receive", function ($cli, $data) {
            echo "收到服务的广播了: {$data}\n";
            $data = unserialize($data);
            $event = $data['event'];
            $params = $data['data'];
            call_user_func(self::$_events[$event], $params);
        });

        $client->on("error", function ($cli) {
            throw new \Exception('连接服务失败!');
        });

        $client->on("close", function ($cli) {
            echo 'close';
        });

        $client->connect(self::$_remoteIp, self::$_remotePort, 0.5);
    }

    /**
     * 广播事件
     *
     * @param [type] $event
     * @param [type] $data
     * @return void
     */
    public static function publish(String $event, $data)
    {
        $client = new \swoole_client(SWOOLE_SOCK_TCP);
        if (!$client->connect(self::$_remoteIp, self::$_remotePort, -1)) {
            throw new \Exception("connect failed. Error: {$client->errCode}\n");
        }

        $params['type'] = 'publish';
        $params['event'] = $event;
        $params['data'] = $data;

        $client->send(serialize((array)$params));
        $client->close();
    }
}
