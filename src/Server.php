<?php
namespace xiaodi\Channel;

use Swoole\Table;

class Server
{

    protected $server = null;

    protected $table = '';

    /**
     * Server constructor.
     */
    public function __construct($address, $port)
    {
        $this->server = new \swoole_server($address, $port);
        $this->server->events = [];
        $this->server->on('connect', [$this, 'onConnect']);
        $this->server->on('receive', [$this, 'onReceive']);
        $this->server->on('close', [$this, 'onClose']);

        // 创建table 用来记录各种事件
        $this->table = new \Swoole\Table(1024);
        $this->table->column('fds', Table::TYPE_STRING, 11);
        $this->table->create();
    }

    /**
     * 启动服务
     */
    public function run()
    {
        $this->server->start();
    }

    /**
     * @param $server
     * @param $fd
     */
    public function onConnect($server, $fd)
    {
        echo "connection open: {$fd}\n";
    }

    /**
     * 服务端收到信息
     * @param $server
     * @param $fd
     * @param $reactor_id
     * @param $data
     */
    public function onReceive($server, $fd, $reactor_id, $data)
    {
        $data = unserialize($data);
        $type = $data['type'];

        switch ($type) {
            case 'subscribe':
                {
                    echo "服务端收到订阅信息\n";
                    $event = $data['event'];
                    $table_event = $this->table->get($event);
                    $fds = $table_event ? \json_decode($table_event['fds'], true) : [];
                    if (!in_array($fd, $fds)) {
                        $fds[] = $fd;
                    }

                    $this->table->set($event, ['fds' => \json_encode($fds)]);
                }
                break;

            case 'publish':
                {
                    $event = $data['event'];
                    $table_event = $this->table->get($event);
                    if ($table_event) {
                        $fds = json_decode($table_event['fds']);
                        foreach ($fds as $fd) {
                            $server->send($fd, serialize(['event' => $data['event'], 'data' => $data['data']]));
                        }
                    }
                }
                break;
        }
    }

    /**
     * @param $server
     * @param $fd
     */
    public function onClose($server, $fd)
    {
        echo "connection close: {$fd}\n";
    }

}
