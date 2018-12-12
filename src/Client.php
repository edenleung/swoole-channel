<?php
namespace xiaodi\Channel;

class Client
{
    protected static $_events = array();
    /**
     * 订阅事件
     *
     * @param [type] $event 事件名
     * @param [type] $callback 回调函数
     * @return void
     */
    public static function on($event, $callback)
    {
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
            throw new \Exception('连接ChannelServer失败!');
        });

        $client->on("close", function ($cli) {
        });

        $client->connect('0.0.0.0', 9501, 0.5);
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
        $client = new \swoole_client(SWOOLE_SOCK_TCP, SWOOLE_SOCK_ASYNC);

        $client->on("connect", function ($cli) use ($event, $data) {
            $params['type'] = 'publish';
            $params['event'] = $event;
            $params['data'] = $data;
            $cli->send(serialize((array)$params));
        });

        $client->on("error", function ($cli) {
            throw new \Exception('连接ChannelServer失败!');
        });

        $client->on("close", function ($cli) {
        });

        $client->connect('0.0.0.0', 9501, 0.5);
    }
}