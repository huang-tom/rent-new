<?php

namespace App\Support;

use App\Exceptions\ErrorException;
use Intervention\Image\ImageManager;

class Image
{

    private $img;
    private $manager;
    private $rootPath;

    /**
     * 构造方法，可用于打开一张图像
     *
     * @param string $original_path 图像路径
     */
    public function __construct($original_path = null)
    {
        $this->rootPath = storage_path();

        //todo 判断原图文件是否存在
        if (!is_file($original_path))
        {
            throw new ErrorException('原图不存在!');
        }

        $this->manager = new ImageManager();
        $this->img = $this->manager->make($original_path);
    }


    /**
     * 生成缩略图
     * @param $width      宽度
     * @param $height     高度
     * @param $save_path  保存路径
     * @return \Intervention\Image\Image
     * @throws ErrorException
     */
    public function thumb($width, $height,$save_path)
    {

        if (empty($this->img)) {
            throw new ErrorException(__('创建图像资源失败'));
        }

        //todo 检验保存路径
        $Uploader = new Uploader();
        $thumb_path = pathinfo($save_path,PATHINFO_DIRNAME);
        if(!$Uploader->checkSavePath($thumb_path))
        {
            throw new ErrorException($Uploader->getError());
        }

        //todo 调整图像大小 && 保存
        $this->img->fit($width, $height);
        $this->img->save($save_path);

        return $this->img;
    }

}
