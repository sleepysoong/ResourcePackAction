<?php

const VERSION = "1.0.13";

function generateUuid() : string{
    return sprintf(
        '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
}

if(!isset($argv[1])){
    exit("태그 이름이 전달되지 않았습니다.\n");
}

$tag = array_map('intval', explode(".", $argv[1]));

$manifest_path = 'manifest.json';
$manifest = json_decode(file_get_contents($manifest_path), true);
$manifest['header']['uuid'] = generateUuid();
$manifest['modules']['uuid'] = generateUuid();
$manifest['header']['version'] = $tag;
$manifest['modules']['version'] = $tag;
file_put_contents($manifest_path, json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

$zip = new ZipArchive();
$zipName = str_replace(" ", "", $manifest['header']['name'] . ".zip");
if($zip->open($zipName, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE){
    exit("압축 파일을 열 수 없습니다.\n");
}

$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(__DIR__), RecursiveIteratorIterator::LEAVES_ONLY);
foreach($files as $file){
    if(!$file->isDir()){
        $file_path = $file->getRealPath();
        $zip->addFile($file_path, substr($file_path, strlen(__DIR__) + 1));
    }
}

$zip->close();
echo "성공적으로 리소스팩을 빌드했어요 ({$zipName}) >____<\n";
