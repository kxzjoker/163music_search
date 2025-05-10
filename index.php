<?php
/**
 * 网易云音乐搜索API接口
 * 接收参数：
 * - name: 歌曲名称
 * - limit: 返回结果数量，默认10，范围1-100
 */

// 设置响应头
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

// 获取请求参数
$name = isset($_REQUEST['name']) ? trim($_REQUEST['name']) : '';
$limit = isset($_REQUEST['limit']) ? intval($_REQUEST['limit']) : 10;

// 参数验证
if (empty($name)) {
    echo json_encode(['code' => 400, 'message' => '请提供歌曲名称']);
    exit;
}

// 限制limit范围在1-100之间
$limit = max(1, min(100, $limit));

try {
    // 调用搜索API
    $result = searchMusic($name, $limit);
    echo json_encode($result, JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    echo json_encode(['code' => 500, 'message' => $e->getMessage()]);
}

/**
 * 搜索音乐
 * @param string $keywords 搜索关键词
 * @param int $limit 返回结果数量
 * @param int $offset 偏移量
 * @param int $type 搜索类型，1为单曲
 * @return array 搜索结果
 */
function searchMusic($keywords, $limit = 10, $offset = 0, $type = 1) {
    // 构建请求数据
    $data = [
        's' => $keywords,
        'limit' => $limit,
        'offset' => $offset,
        'type' => $type
    ];
    
    // 加密参数
    $encryptedData = encryptParams($data);
    
    // 构建请求头
    $headers = [
        'User-Agent: Mozilla/5.0 (iPhone; CPU iPhone OS 13_2_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/13.0.3 Mobile/15E148 Safari/604.1',
        'Connection: Keep-Alive',
        'Content-Type: application/x-www-form-urlencoded',
        'Referer: http://music.163.com',
        'X-Real-IP: 112.88.7.213'
    ];
    
    // 发送请求到网易云音乐API
    $response = sendRequest('http://music.163.com/weapi/cloudsearch/pc', $encryptedData, $headers);
    
    return $response;
}

/**
 * 发送HTTP请求
 * @param string $url 请求URL
 * @param array $data 请求数据
 * @param array $headers 请求头
 * @return array 响应结果
 */
function sendRequest($url, $data, $headers = []) {
    $ch = curl_init();
    
    // 设置请求选项
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    // 执行请求
    $response = curl_exec($ch);
    
    // 检查是否有错误
    if (curl_errno($ch)) {
        throw new Exception('请求失败: ' . curl_error($ch));
    }
    
    // 关闭连接
    curl_close($ch);
    
    // 解析响应
    $result = json_decode($response, true);
    if (!$result) {
        throw new Exception('解析响应失败');
    }
    
    return $result;
}

/**
 * 加密请求参数
 * @param array $data 请求数据
 * @return array 加密后的参数
 */
function encryptParams($data) {
    $presetKey = '0CoJUm6Qyw8W8jud';
    $iv = '0102030405060708';
    $pubKey = '010001';
    $modulus = '00e0b509f6259df8642dbc35662901477df22677ec152b5ff68ace615bb7b725152b3ab17a876aea8a5aa76d2e417629ec4ee341f56135fccf695280104e0312ecbda92557c93870114af6c9d05c4f7f0c3685b7a46bee255932575cce10b424d813cfe4875d3e82047b97ddef52741d546b8e289dc6935b3ece0462db0a22b8e7';
    $secretKey = createRandomString(16);
    
    $jsonStr = json_encode($data);
    $params = aesEncrypt($jsonStr, $presetKey);
    $params = aesEncrypt($params, $secretKey);
    
    $encSecKey = rsaEncrypt($secretKey, $pubKey, $modulus);
    
    return [
        'params' => $params,
        'encSecKey' => $encSecKey
    ];
}

/**
 * 生成随机字符串
 * @param int $length 字符串长度
 * @return string 随机字符串
 */
function createRandomString($length) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $result = '';
    for ($i = 0; $i < $length; $i++) {
        $result .= $chars[mt_rand(0, strlen($chars) - 1)];
    }
    return $result;
}

/**
 * AES加密
 * @param string $text 待加密文本
 * @param string $key 加密密钥
 * @return string 加密后的文本
 */
function aesEncrypt($text, $key) {
    $iv = '0102030405060708';
    $pad = 16 - (strlen($text) % 16);
    $text = $text . str_repeat(chr($pad), $pad);
    
    return base64_encode(openssl_encrypt($text, 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $iv));
}

/**
 * RSA加密
 * @param string $text 待加密文本
 * @param string $pubKey 公钥
 * @param string $modulus 模数
 * @return string 加密后的文本
 */
function rsaEncrypt($text, $pubKey, $modulus) {
    $text = strrev($text);
    $biText = hexToBigint(bin2hex($text));
    $biPubKey = hexToBigint($pubKey);
    $biModulus = hexToBigint($modulus);
    $biRet = modPow($biText, $biPubKey, $biModulus);
    
    return str_pad(bigintToHex($biRet), 256, '0', STR_PAD_LEFT);
}

/**
 * 十六进制转大整数
 * @param string $hex 十六进制字符串
 * @return string 大整数字符串
 */
function hexToBigint($hex) {
    $dec = '0';
    $len = strlen($hex);
    for ($i = 0; $i < $len; $i++) {
        $dec = bcadd(bcmul($dec, '16'), ctype_digit($hex[$i]) ? $hex[$i] : strpos('abcdef', strtolower($hex[$i])) + 10);
    }
    return $dec;
}

/**
 * 大整数转十六进制
 * @param string $bigint 大整数字符串
 * @return string 十六进制字符串
 */
function bigintToHex($bigint) {
    $hex = '';
    while (bccomp($bigint, '0') > 0) {
        $remainder = bcmod($bigint, '16');
        $hex = dechex(intval($remainder)) . $hex;
        $bigint = bcdiv($bigint, '16');
    }
    return $hex;
}

/**
 * 模幂运算
 * @param string $base 底数
 * @param string $exponent 指数
 * @param string $modulus 模数
 * @return string 结果
 */
function modPow($base, $exponent, $modulus) {
    if (bccomp($modulus, '1') === 0) {
        return '0';
    }
    
    $result = '1';
    $base = bcmod($base, $modulus);
    
    while (bccomp($exponent, '0') > 0) {
        if (bcmod($exponent, '2') === '1') {
            $result = bcmod(bcmul($result, $base), $modulus);
        }
        $exponent = bcdiv($exponent, '2');
        $base = bcmod(bcmul($base, $base), $modulus);
    }
    
    return $result;
}
?>