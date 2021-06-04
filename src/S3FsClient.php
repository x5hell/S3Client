<?php


namespace Chslovo\S3;

/*
 * Клиент для работы с файловым хранилищем S3.
 * Интерфейс полностью соответствует файловому сереру
 * (сигнатура методов download, delete, upload идентична),
 * поэтому можно безболезненно заменять файловый сервер на S3FsClient
 */

use Aws\S3\S3Client;

class S3FsClient
{
    const PATH_STYLE = 'PathStyle';

    const BUCKET = 'Bucket';

    const KEY = 'Key';

    const BODY = 'Body';

    /** @var string url s3 сервера */
    private $endpoint;

    /** @var string ключ доступа */
    private $accessKey;

    /** @var string секретный ключ */
    private $secretKey;

    /** @var string ведро (имя хранилища) */
    private $bucket;

    /** @var S3Client клиент для операций с s3 сервером */
    private $s3client;

    /**
     * @param string $endpoint url s3 сервера
     * @param string $accessKey ключ доступа
     * @param string $secretKey секретный ключ
     * @param string $bucket ведро (имя хранилища)
     */
    public function __construct($endpoint, $accessKey, $secretKey, $bucket = 'file-server')
    {
        $this->endpoint = $endpoint;
        $this->accessKey = $accessKey;
        $this->secretKey = $secretKey;
        $this->bucket = $bucket;
        $this->initS3Client();
        $this->createBucketIfNotExists();
    }

    private function initS3Client()
    {
        $this->s3client = S3Client::factory([
            'endpoint' => $this->endpoint,
            'credentials' => [
                'key'      => $this->accessKey,
                'secret'   => $this->secretKey
            ]
        ]);
    }

    /**
     * @return S3Client
     */
    public function getS3client()
    {
        return $this->s3client;
    }

    private function createBucketIfNotExists()
    {
        $s3client = $this->getS3client();
        $bucketExist = $s3client
            ->doesBucketExist(
                $this->bucket,
                true,
                [self::PATH_STYLE => true]
            );
        if(!$bucketExist){
            $s3client->createBucket([
                self::BUCKET => $this->bucket,
                self::PATH_STYLE => true
            ]);
        }
    }

    /**
     * Скачивание файла
     * @param string $hash хеш файла
     * @param string $savePath путь для сохранения файла
     * @return string содержимое файла
     */
    public function download($hash, $savePath = null)
    {
        $result = $this->getS3client()->getObject([
            self::BUCKET => $this->bucket,
            self::KEY => $hash,
            self::PATH_STYLE => true
        ]);
        $content = strval($result['Body']);
        if(isset($savePath)){
            file_put_contents($savePath, $content);
        }
        return $content;
    }

    /**
     * Удаление файла по хешу
     * @param string $hash хеш файла
     */
    public function delete($hash)
    {
        $this->getS3client()->deleteObject([
            self::BUCKET => $this->bucket,
            self::KEY => $hash,
            self::PATH_STYLE => true
        ]);
    }

    /**
     * Сохраенение файла
     * @param string $filePath путь к загружаемому файлу
     * @param string $postName наименование загружаемого файла
     * @return string хеш файла
     */
    public function upload($filePath, $postName = '')
    {
        $fileContent = file_get_contents($filePath);
        $hash = sha1($fileContent);
        $this->getS3client()->putObject([
            self::BUCKET => $this->bucket,
            self::KEY => $hash,
            self::BODY => $fileContent,
            self::PATH_STYLE => true
        ]);
        return $hash;
    }
}