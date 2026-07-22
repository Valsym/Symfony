<?php

namespace App\Service;

use Aws\S3\S3Client;

class S3ClientFactory
{
    public static function create(): S3Client
    {
        return new S3Client([
            'version' => '2006-03-01',
            'region' => $_ENV['AWS_REGION'] ?? 'us-east-1',
            'endpoint' => $_ENV['AWS_ENDPOINT'] ?? 'http://localhost:9000',
            'use_path_style_endpoint' => true,
            'credentials' => [
                'key' => $_ENV['AWS_ACCESS_KEY_ID'] ?? 'minioadmin',
                'secret' => $_ENV['AWS_SECRET_ACCESS_KEY'] ?? 'minioadmin',
            ],
        ]);
    }
}

