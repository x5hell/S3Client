# S3FsClient
Клиент для работы с файловым хранилищем S3.
Интерфейс полностью соответствует файловому сереру
(сигнатура методов download, delete, upload идентична),
поэтому можно безболезненно заменять файловый сервер на S3FsClient


## Пример использования:
```php
use Aws\S3\Exception\NoSuchKeyException;
use Chslovo\S3\S3FsClient;

include __DIR__ . '/vendor/autoload.php';

$endpoint = 'https://s3.kz.dobrynin.docker';
$accessKey = 'accessKey1';
$secretKey = 'verySecretKey1';

$s3client = new S3FsClient($endpoint, $accessKey, $secretKey);

/* Загрузка файла */
$hash = $s3client->upload('test.php', 'test.php');

var_export(["hash" => $hash]);

/* Получение содержимого файла */
$fileContent = $s3client->download($hash);

var_export(["fileContent" => $fileContent]);

/* Удаление файла */
$s3client->delete($hash);

try {
    $fileContent = $s3client->download($hash);
    var_export(["fileContent" => $fileContent]);
} catch ( NoSuchKeyException $exception){
    echo "файл не существует";
}
```