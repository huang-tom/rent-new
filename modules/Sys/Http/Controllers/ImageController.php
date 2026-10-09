<?php

namespace Modules\Sys\Http\Controllers;

use App\Exceptions\ErrorException;
use App\Support\Image;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;


class ImageController extends BaseController
{
    private $rootPath;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->rootPath = storage_path();
    }

    /**
     * 设置 Header 头
     * @param $type
     * @return void
     */
    public function setHeader($type)
    {
        switch ($type){
            case 'jpg':
            case 'jpeg':
                return ['Content-Type' => 'image/jpeg'];
            case 'gif':
                return ['Content-Type' => 'image/gif'];
            default:
                return ['Content-Type' => 'image/png'];
        }

    }


    /**
     * 获取图片
     * @param Request $request
     * @return \Illuminate\Http\Response
     * @throws ErrorException
     */
    public function get(Request $request)
    {
        $file = $request->path();
        $ext = strtolower(pathinfo($file,PATHINFO_EXTENSION)); //extension

        if(!in_array($ext,array('jpg','jpeg','gif','png')))
        {
            throw new ErrorException('非图片类型');
        }

        $file_row = explode('!', $file);
        $original_path = $this->rootPath.ltrim($file_row[0],'image.php'); //原图路径

        //缩略图形式
        if (isset($file_row[1]))
        {
            $thumb_img = str_replace('uploads','thumb',$original_path).'!'.$file_row[1]; //缩略图路径
            if(file_exists($thumb_img))
            {
                $img = file_get_contents($thumb_img); //已经存在 直接返回
            }else{

                //todo 判断原图文件是否存在
                if (!is_file($original_path))
                {
                    throw new ErrorException('不存在的图像文件');
                }

                //todo 获取缩略图的宽高
                $width  = 100;
                $height = 100;
                $size_rows = explode('.',$file_row[1]); //数组：['100x100','jpg']
                if(!empty($size_rows))
                {
                    $size_row = explode('x',$size_rows[0]);
                    $width    = isset($size_row[0]) ? $size_row[0] : 100;
                    $height   = isset($size_row[1]) ? $size_row[1] : 100;
                }

                //todo 生成缩略图
                $image = new Image($original_path);
                $img = $image->thumb($width,$height,$thumb_img);

            }

        }else{
            //todo 判断文件是否存在
            if (!is_file($original_path))
            {
                throw new ErrorException('不存在的图像文件');
            }

            //todo 获取原图
            $img = file_get_contents($original_path);

        }

        $header = $this->setHeader($ext);
        return Response()->make($img,200,$header);
    }


}
