<?php
error_reporting(E_ALL ^ E_NOTICE);
require_once './src/QcloudApi/QcloudApi.php';


$config = array('SecretId'       => 'id',
                'SecretKey'      => 'key',
                'RequestMethod'  => 'POST',
                'DefaultRegion'  => 'gz');

$cvm = QcloudApi::load(QcloudApi::MODULE_CVM, $config);
$vod= QcloudApi::load(QcloudApi::MODULE_VOD_UPLOAD, $config);
$vod->setConfigRequestMethod('POST');

function getCurlValue($filename, $contentType, $postname)
{
    // PHP 5.5 introduced a CurlFile object that deprecates the old @filename syntax
    // See: https://wiki.php.net/rfc/curl-file-upload
    if (function_exists('curl_file_create')) {
        return curl_file_create($filename, $contentType, $postname);
    }
 
    // Use the old style if using an older version of PHP
    $value = "@{$this->filename};filename=" . $postname;
    if ($contentType) {
        $value .= ';type=' . $contentType;
    }
 
    return $value;
}

//换成你需要的视频
$file = 'hi.mp4';

$package = array(
    'fileName' => $file, 
    'fileSha' => sha1_file($file),
    'fileSize' => filesize($file),
    'dataSize' => filesize($file),
    'offset' => '0',
    'fileType' => pathinfo($file, PATHINFO_EXTENSION),
    'isTranscode' => '1',
    'isScreenshot' => '1'
);

var_dump($package);

$url = $vod->generateUrl('MultipartUploadVodFile', $package, 'http');

$file_path = realpath($file);

var_dump($file_path);

$cfile = getCurlValue($file,'multipart/form-data', $file);

$post = array('file'=>$cfile);
 
$ch = curl_init();
curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type:multipart/form-data"));
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$result=curl_exec ($ch);

//var_dump($result);
var_dump(curl_getinfo($ch));

if (curl_errno($ch)) {
    $msg = curl_error($ch);
}else {
    $msg = 'File uploaded successfully.';
}

echo $msg;
var_dump($result);

curl_close ($ch);

echo "\n";
