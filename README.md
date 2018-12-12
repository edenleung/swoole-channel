# swoole-channel
#### 作者参考了 workerman/channel

### 安装
~~~
composer require xiaodi/swoole-channel
~~~

### 服务端
`server.php`
~~~
use xiaodi\Channel\Server;
$server = new Server('0.0.0.0', 9501);
$server->run();
~~~

###
`client.php`
~~~
use xiaodi\Channel\Client;

// 添加监听
Client::on('sayHello', function($msg) {
    echo '我被回调了' . $msg;
});

// 广播事件
Client::publish('sayHello', 'hey xiaodi');
~~~
