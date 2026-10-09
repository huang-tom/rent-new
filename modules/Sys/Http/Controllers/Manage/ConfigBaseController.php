<?php

namespace Modules\Sys\Http\Controllers\Manage;

use App\Support\Respond;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use Modules\Sys\Repositories\Criteria\ConfigBaseCriteria;
use Modules\Sys\Repositories\Validators\ConfigBaseValidator;
use Modules\Sys\Services\ConfigBaseService;
use Modules\Sys\Services\SmsRecordService;

class ConfigBaseController extends BaseController
{
    private $configBaseService;
    private $configBaseValidator;
    private $smsRecordService;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        ConfigBaseService $configBaseService,
        ConfigBaseValidator $configBaseValidator,
        SmsRecordService $smsRecordService
    ) {
        $this->configBaseService = $configBaseService;
        $this->configBaseValidator = $configBaseValidator;
        $this->smsRecordService = $smsRecordService;
    }


    /**
     * 短信记录
     *
     * [新增 2026-09-22] 补齐 GET /manage/sys/config/smsRecord。
     *
     * 后台「站点设置」页的「短信记录」抽屉一直在调它
     * （admin/src/views/sys/config/components/SmsRecord.vue:150），但这条路由从未注册 →
     * 点一次 404 一次。配套地新建了 sys_sms_record 表，并让短信下发链路落库
     * （见 Front/CaptchaController::sendMobileVerifyCode），所以这里返回的是真实记录，
     * 不是拼凑出来的空列表。
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function smsRecord(Request $request)
    {
        $data = $this->smsRecordService->list($request);

        return Respond::success($data);
    }


    /**
     * 列表
     */
    public function list(Request $request)
    {
        $data = $this->configBaseService->list($request, new ConfigBaseCriteria($request));

        return Respond::success($data);
    }


    /**
     * 获取配置表信息
     */
    public function index(Request $request)
    {
        $data['items'] = $this->configBaseService->getConfigList($request);

        return Respond::success($data);
    }


    /**
     * 新增
     */
    public function add(Request $request)
    {
        $this->configBaseValidator->with($request->all())->passesOrFail('create');
        $data = $this->configBaseService->add([
            'config_type_id' => $request->get('config_type_id'),
            'config_key' => $request->get('config_key'),
            'config_title' => $request->get('config_title', ''),
            'config_note' => $request->get('config_note', ''),
            'config_datatype' => $request->get('config_datatype'),
            'config_options' => $request->get('config_options', ''),
            'config_value' => $request->get('config_value', ''),
            'config_sort' => $request->get('config_sort', 0),
        ]);

        return Respond::success($data);
    }


    /**
     * 修改配置信息
     */
    public function edit(Request $request)
    {
        $config_key = $request->get('config_key', -1);
        $this->configBaseValidator->setId($config_key);
        $this->configBaseValidator->with($request->all())->passesOrFail('update');
        $data = $this->configBaseService->edit($config_key, [
            'config_type_id' => $request->get('config_type_id'),
            'config_title' => $request->get('config_title', ''),
            'config_note' => $request->get('config_note', ''),
            'config_datatype' => $request->get('config_datatype'),
            'config_options' => $request->get('config_options', ''),
            'config_value' => $request->get('config_value', ''),
            'config_sort' => $request->get('config_sort', 0),
        ]);

        return Respond::success($data);
    }


    public function remove(Request $request)
    {
        $config_key = $request->get('config_key', '-1');
        $data = $this->configBaseService->removeBase($config_key);

        return Respond::success($data);
    }


    /**
     * 修改配置信息
     */
    public function editSite(Request $request)
    {
        $configs = $request->input('configs');
        $success = $this->configBaseService->editSite($configs);

        return Respond::success($success);
    }


    /**
     * 修改状态
     */
    public function editState(Request $request)
    {
        $config_key = $request->input('config_key');
        $row = $this->configBaseService->get($config_key);
        if (!empty($row)) {
            $this->configBaseService->edit($config_key, ['config_enable' => $request->boolean('config_enable', false)]);
            return Respond::success($row);
        } else {
            return Respond::error(__('数据不存在'));
        }
    }


    /**
     * PC帮助导航
     */
    public function savePcHelp(Request $request)
    {
        $data = $this->configBaseService->edit('page_pc_help', ['config_value' => $request['pc_help']]);

        return Respond::success($data);
    }


    /**
     * 推广设置
     */
    public function getDetail(Request $request)
    {
        $config_key = $request->input('config_key', 'fx_level_config');
        $data = $this->configBaseService->get($config_key);

        return Respond::success($data);
    }


}
