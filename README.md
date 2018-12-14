# swoole-channel
#### 作者参考了 workerman/channel
### docker测试
~~~
docker pull twosee/swoole-coroutine
docker run -ti -p 9501:9501 -p 9502:9502 -p 9503:9503 -v /c/swoole:/home twosee/swoole-coroutine
~~~
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

// 自定义配置
Client::config('127.0.0.1', 9501);

// 添加监听
Client::on('sayHello', function($msg) {
    echo '我被回调了' . $msg;
});

// 广播事件
Client::publish('sayHello', 'hey xiaodi');
~~~
