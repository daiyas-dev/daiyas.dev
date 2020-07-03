<?php
namespace App\Libs\Test;
use \GuzzleHttp\Client;
use \GuzzleHttp\Pool;
use \GuzzleHttp\Promise;
use \GuzzleHttp\Promise\PromiseInterface;
use \GuzzleHttp\Psr7\Request;
//GuzzleHttpによる並列実行テスト
class DataSendTest
{
    public function Send($send_id = null)
    {
        $ret=$this->sendExec();
        $a="完了";
    }
    public function sendExec()
    {
        $url = "http://192.168.1.210/hellolaravel/public/api/beginner2p";
        $post_data = array(
            'FileData' => 'abcdef'
        );
        $options = [
                    'headers'=>array('Content-Type: application/json'),
                    'json' => $post_data
                   ];

        $list = [];

        $client = new Client();

        $requests = function ($u,$o) {
            for($i=0;$i<60;$i++) {
                yield new Request('POST', $u,$o);
            }
        };

        $pool = new Pool($client, $requests($url,$options), [
            'concurrency' => 25, //並列数
            'fulfilled' => function($response, $index) use (&$list) {
                if ($response->getStatusCode() !== 200) {
                    \Log::error($response->getStatusCode().'::'.$response->getReasonPhrase());
                } else {
                    $contents = $response->getBody();
                    if (!empty($contents)) {
                        $list[] = json_decode((string)$contents);
                    }
                }
            },
            'rejected' => function($reason, $index) {
                \Log::error($reason->getCode().'::'.$reason->getMessage());
            },
        ]);
        $promise = $pool->promise();
        $promise->wait();
        return $list;
    }
}
