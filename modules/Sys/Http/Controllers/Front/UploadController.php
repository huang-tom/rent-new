<?php

namespace Modules\Sys\Http\Controllers\Front;

use App\Exceptions\ErrorException;
use App\Support\Uploader;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use Modules\Account\Repositories\Models\User;
use Modules\Sys\Services\ConfigBaseService;
use Modules\Sys\Services\MaterialBaseService;
use Modules\Sys\Services\OssService;

class UploadController extends BaseController
{

    public $materialBaseService = null;
    public $ossService = null;
    public $savePath = null;
    public $userId = 10001;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(MaterialBaseService $materialBaseService, OssService $ossService)
    {
        $this->materialBaseService = $materialBaseService;
        $this->ossService = $ossService;

        $this->userId = User::getUserId();
        $this->savePath = '/' . $this->userId;
    }


    /**
     * 上传接口
     * @param Request $request
     * @return void
     * @throws ErrorException
     */
    public function index(Request $request)
    {

        //获取上传的文件信息
        $file = $request->file('upfile');
        $file_name = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();

        $request['material_mime_type'] = $this->materialBaseService->getContentType($extension);
        $request['material_name'] = $file_name;
        $request['material_alt'] = $file_name;

        $image_max_filesize = ConfigBaseService::getConfig('upload_max_filesize') * 1024;
        $config = [
            'maxSize' => $image_max_filesize,
            'savePath' => $this->savePath
        ];

        $is_simulate = $request->input('is_simulate', 0);
        $upload_type = ConfigBaseService::getConfig('upload_type');
        if ($upload_type == 1) {
            //阿里云存储
            $result = $this->ossService->ossUploadObject($file);
            $material_url = $result['url'];
            $material_size = $result['size_upload'];
            $result['file_url'] = $result['url'];
            $result['file_name'] = $file_name;
            $result['file_path'] = $material_url;
            $result['file_size'] = $material_size;
            $result['file_type'] = $extension;
            $result['mime_type'] = $request['material_mime_type'];
            $result['type'] = $material_url;
        } else {
            $uploader = new Uploader($config);
            $res = $uploader->upload($file);
            if (empty($res) || !$res) {
                throw new ErrorException('上传失败' . $uploader->getError());
            }

            if ($res[0]['state'] == 'SUCCESS') {
                $result = $res[0];
                $material_url = $result['url'];
                $material_size = $result['size'];
                $result['file_url'] = $result['url'];
                $result['file_name'] = $file_name;
                $result['file_path'] = $material_url;
                $result['file_size'] = $material_size;
                $result['file_type'] = $extension;
                $result['mime_type'] = $request['material_mime_type'];
                $result['type'] = $material_url;
                $request['material_path'] = $result['url_path'];
            }
        }

        $request['material_size'] = $material_size;
        $request['user_id'] = $this->userId;

        if ($material_url) {
            $request['material_url'] = $material_url;
            //todo 添加到素材表
            $this->materialBaseService->addMaterial($request);
        }

        echo json_encode([
            'status' => 200,
            'data' => $result,
            'code' => 200,
            'msg' => ''
        ]);
        die;

    }

}
