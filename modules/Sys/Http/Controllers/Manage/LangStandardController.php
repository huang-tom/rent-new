<?php

namespace Modules\Sys\Http\Controllers\Manage;

use App\Support\Respond;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use Modules\Sys\Repositories\Criteria\LangStandardCriteria;
use Modules\Sys\Repositories\Validators\LangStandardValidator;
use Modules\Sys\Services\LangStandardService;

class LangStandardController extends BaseController
{
    private $langStandardService;
    private $langStandardValidator;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(LangStandardService $langStandardService, LangStandardValidator $langStandardValidator)
    {
        $this->langStandardService = $langStandardService;
        $this->langStandardValidator = $langStandardValidator;
    }


    /**
     * 列表
     */
    public function list(Request $request)
    {
        $data = $this->langStandardService->getList($request, new LangStandardCriteria($request));

        return Respond::success($data);
    }


    /**
     * 格式化请求数据
     * @param $request
     * @return array
     */
    public function formatRequest($request)
    {

        $data = [
            'is_used' => $request->boolean('is_used', false),
            'frontend' => $request->boolean('frontend', false),
            'backend' => $request->boolean('backend', false),
            'java' => $request->boolean('java', false),
            'time' => $request->input('time', getTime())
        ];

        $lang_fields = ['zh_CN', 'zh_TW', 'en_GB', 'th_TH', 'es_MX', 'ar_SA', 'vi_VN', 'tr_TR', 'ja_JP', 'id_ID', 'de_DE', 'fr_FR', 'pt_PT', 'it_IT',
            'ru_RU', 'ro_RO', 'az_AZ', 'el_GR', 'fi_FI', 'lv_LV', 'nl_NL', 'da_DK', 'sr_RS', 'pl_PL', 'uk_UA', 'kk_KZ', 'my_MM', 'ko_KR', 'ms_MY'];
        foreach ($lang_fields as $field) {
            $key = strtolower($field);
            if ($request->has($key)) {
                $data[$field] = $request->input($key);
            }
        }

        return $data;
    }


    /**
     * 新增
     */
    public function add(Request $request)
    {
        $this->langStandardValidator->with($request->all())->passesOrFail('create');
        $data = $this->langStandardService->add($this->formatRequest($request));

        return Respond::success($data);
    }


    /**
     * 修改
     */
    public function edit(Request $request)
    {
        $zh_CN = $request->get('zh_cn', -1);
        $this->langStandardValidator->setId($zh_CN);
        $this->langStandardValidator->with($request->all())->passesOrFail('update');
        $data = $this->langStandardService->edit($zh_CN, $this->formatRequest($request));

        return Respond::success($data);
    }


    /**
     * 删除
     */
    public function remove(Request $request)
    {
        $zh_CN = $request->input('zh_CN', -1);
        $this->langStandardService->remove($zh_CN);

        return Respond::success([]);
    }

}
