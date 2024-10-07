<?php
// 存有image链接的文件名
$filename = "img.txt";
$cache_file = 'cache/random_image_cache.json';

// 检查文件是否存在
if (!file_exists($filename)) {
    http_response_code(404);
    header('Content-Type: application/json');
    echo json_encode(['error' => '文件不存在']);
    exit;
}

// 从文本文件中读取链接
$pics = file($filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

// 检查是否读取到了图片链接
if (empty($pics)) {
    http_response_code(404);
    header('Content-Type: application/json');
    echo json_encode(['error' => '没有找到图片链接']);
    exit;
}

// 检查缓存文件是否存在并且没有过期
$cache_duration = 60; // 缓存持续时间为1分钟
$cache_is_valid = false;
$pic = null;

if (file_exists($cache_file)) {
    $cache_time = filemtime($cache_file);
    if (time() - $cache_time < $cache_duration) {
        $cache_is_valid = true;
        $cache_data = json_decode(file_get_contents($cache_file), true);
        $pic = $cache_data['pic'];
    }
}

// 如果缓存有效，直接使用缓存中的图片链接
if (!$cache_is_valid) {
    // 从数组中随机选择一个链接
    $pic = $pics[array_rand($pics)];

    // 将新的图片链接和当前时间保存到缓存文件中
    $cache_data = [
        'pic' => $pic,
        'time' => time()
    ];
    file_put_contents($cache_file, json_encode($cache_data));
}

// 根据请求参数返回指定格式
$type = isset($_GET['type']) ? $_GET['type'] : '';

switch ($type) {
    case 'json':
        header('Content-Type: application/json');
        echo json_encode(['pic' => $pic]);
        break;

    default:
        header("Location: $pic");
        exit;
}
?>