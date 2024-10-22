# Random-Image-API

#### 介绍
一个轻量级的 PHP 应用程序，旨在提供一个随机图片的API。用户可以通过 HTTP 请求访问 API 并获取随机图片链接。支持多种响应格式，包括 JSON 和 HTTP 重定向。该项目适用于需要动态展示随机图片的场景，如网站背景图片轮播等。

主要功能是从 img.txt 文件中读取图片链接，随机选择一个链接，并根据请求参数 type 的值以不同的格式返回图片链接。如果 type 是 'json'，则返回JSON格式的数据；否则，默认行为是重定向到图片链接。为了实现“1分钟内再次请求不变化图片”的功能，引入缓存机制。

### 目录结构
确保你的项目目录中有 `cache` 文件夹，并且服务器有权限写入该文件夹。例如：
```
index/
├── cache
│   └── random_image_cache.json
├── images
│   ├── a.jpg
│   ├── b.jpg
│   ├── c.jpg
│   ...
│   └── k.jpg
├── img.txt
├── random.php
└── README.md
```


### 代码解释

1. **定义文件路径和缓存文件**
   ```php
   $filename = "img.txt";
   $cache_file = 'cache/random_image_cache.json';
   ```
   - `$filename` 是存储图片链接的文件名。
   - `$cache_file` 是缓存文件的路径，用于存储随机选择的图片链接和缓存时间。

2. **检查文件是否存在**
   ```php
   if (!file_exists($filename)) {
       http_response_code(404);
       header('Content-Type: application/json');
       echo json_encode(['error' => '文件不存在']);
       exit;
   }
   ```
   - 检查 `img.txt` 文件是否存在，如果不存在，返回404错误并终止脚本。

3. **从文本文件中读取链接**
   ```php
   $pics = file($filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
   ```
   - 使用 `file()` 函数读取文件内容到数组中，并去除每行末尾的换行符和空行。

4. **检查是否读取到了图片链接**
   ```php
   if (empty($pics)) {
       http_response_code(404);
       header('Content-Type: application/json');
       echo json_encode(['error' => '没有找到图片链接']);
       exit;
   }
   ```
   - 检查数组是否为空，如果为空，返回404错误并终止脚本。

5. **检查缓存文件是否存在并且没有过期**
   ```php
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
   ```
   - 定义缓存持续时间为1分钟(不需要缓存可设为0，则每次刷新接口图片都变化)。
   - 检查缓存文件是否存在并且没有过期。如果缓存有效，读取缓存文件中的图片链接。

6. **如果缓存有效，直接使用缓存中的图片链接**
   ```php
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
   ```
   - 如果缓存无效，从数组中随机选择一个图片链接，并将新的图片链接和当前时间保存到缓存文件中。

7. **根据请求参数返回指定格式**
   ```php
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
   ```
   - 根据请求参数 `type` 的值，返回JSON格式的数据或重定向到图片链接。

