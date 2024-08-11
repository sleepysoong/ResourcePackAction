<?php

const VERSION = "1.0.3";

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

$manifest_path = 'manifest.json';
$manifest = json_decode(file_get_contents($manifest_path), true);
$manifest['header']['uuid'] = generateUuid();
$manifest['modules']['uuid'] = generateUuid();
file_put_contents($manifest_path, json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

$name = $manifest['header']['name'];
$version = implode('.', $manifest['header']['version']);

$zip = new ZipArchive();
if($zip->open('output.zip', ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE){
    exit("압축 파일을 열 수 없습니다.\n");
}

$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source_dir), RecursiveIteratorIterator::LEAVES_ONLY);
foreach($files as $file){
    if(!$file->isDir()){
        $file_path = $file->getRealPath();
        $relative_path = substr($file_path, strlen($source_dir) + 1);
        $zip->addFile($file_path, $relative_path);
    }
}

$zip->close();
echo "성공적으로 리소스팩을 빌드했어요 >____<\n";

if(!copy($zip_file, 'output.mcworld')){
    exit("파일 복사에 실패했습니다.\n");
}

echo "빌드된 리소스팩을 성공적으로 복사했어요 >____<\n";
