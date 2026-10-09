<?php

namespace Modules\Sys\Services;

use App\Exceptions\ErrorException;
use Illuminate\Support\Str;
use Modules\Sys\Repositories\Contracts\ConfigBaseRepository;
use OSS\OssClient as AliyunOssClient;

/**
 * Class OssService.
 *
 * @package Modules\Sys\Services
 */
class OssService
{
    private $configBaseRepository;

    public function __construct(ConfigBaseRepository $configBaseRepository)
    {
        $this->configBaseRepository = $configBaseRepository;
    }


    /**
     * 上传文件到阿里云OSS
     * @param $file
     * @return false
     * @throws \OSS\Core\OssException
     */
    public function ossUploadObject($file)
    {
        // 创建唯一的文件名，包括 UUID 和原始文件的扩展名
        $extension = $file->getClientOriginalExtension();
        $unique_name = Str::uuid() . '.' . $extension;

        $access_key_id = $this->configBaseRepository->getConfig('aliyun_access_key_id');
        $access_key_secret = $this->configBaseRepository->getConfig('aliyun_access_key_secret');
        $endpoint = $this->configBaseRepository->getConfig('aliyun_endpoint');

        $ossClient = new AliyunOssClient(
            $access_key_id,
            $access_key_secret,
            $endpoint
        );

        $bucket = $this->configBaseRepository->getConfig('aliyun_bucket');
        $default_dir = $this->configBaseRepository->getConfig('aliyun_default_dir');

        // 上传文件到阿里云OSS存储桶
        $res = $ossClient->uploadFile(
            $bucket,
            $default_dir . '/' . $unique_name,
            $file->getRealPath()
        );

        if ($res && $res['info']['url']) {
            return $res['info'];
        } else {
            throw new ErrorException(__('OSS上传失败，请重新上传'));
        }

    }

}
