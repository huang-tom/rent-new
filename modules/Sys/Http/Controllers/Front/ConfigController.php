<?php

namespace Modules\Sys\Http\Controllers\Front;

use Laravel\Lumen\Routing\Controller as BaseController;
use App\Support\Respond;
use Illuminate\Http\Request;
use Modules\Sys\Services\ConfigBaseService;

class ConfigController extends BaseController
{
    private $configBaseService;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(ConfigBaseService $configBaseService)
    {
        $this->configBaseService = $configBaseService;
    }


    public function publicKey()
    {
        $data['public_key'] = env('PUBLICK_KEY');

        return Respond::success($data);
    }


    /**
     * 网站配置信息
     */
    public function info()
    {
        $data = $this->configBaseService->getSiteInfo('');
        return Respond::success($data);
    }


    /**
     * 底部帮助导航
     */
    public function getPcHelp(Request $request)
    {
        $key = 'page_pc_help';
        $data = $this->configBaseService->getPcHelp($key);

        return Respond::success($data);
    }


    /**
     * 获取多语言配置语言信息
     */
    public function listTranslateLang()
    {
        $data = $this->configBaseService->listTranslateLang();

        return Respond::success($data);
    }

}
