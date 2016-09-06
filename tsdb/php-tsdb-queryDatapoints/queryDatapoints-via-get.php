<?php

require "../../authorization/auth.php";

// 配置AK/SK和endpoint，生成认证字符串。

$ak = "";  // AccessKeyID
$sk = "";  // SecretAccessKey
$endpoint = "";  // TSDB连接地址，如：{database}.tsdb.iot.gz.baidubce.com

$method = "GET";
$uri = "/v1/datapoint";
$queryConditions = array(
    "queries" => array(
        array(
            "metric" => "cpu_idle",
            "filters" => array(
                "start" => "10 days ago",
                "tags" => array(
                    "host" => array("server1", "server2")
                )
            )
        )
    )
);
$params = array(
    "query" => json_encode($queryConditions)
);

date_default_timezone_set("UTC");
$timestamp = new \DateTime();
$expirationInSeconds = 3600;

$authorization = generateAuthorization($ak, $sk, $method, $endpoint, $uri, $params, $timestamp, $expirationInSeconds);
print("authorization: {$authorization}\n");

// 构造HTTP请求的URL、Header、Body。

$queryStr = HttpUtil::getCanonicalQueryString($params);
$url = "http://{$endpoint}{$uri}?{$queryStr}";
$timeStr = $timestamp->format("Y-m-d\TH:i:s\Z");
$head = array(
    "Content-Type:application/json",
    "Authorization:{$authorization}",
    "x-bce-date:{$timeStr}"
);

// 发送HTTP请求，写入数据点。

$curlp = curl_init();
curl_setopt($curlp, CURLOPT_URL, $url);
curl_setopt($curlp, CURLOPT_HTTPHEADER, $head);

curl_setopt($curlp, CURLOPT_RETURNTRANSFER, 1);
$response = curl_exec($curlp);
$status = curl_getinfo($curlp, CURLINFO_HTTP_CODE);
curl_close($curlp);

print("status: {$status}\n");
print("response: {$response}\n");

?>