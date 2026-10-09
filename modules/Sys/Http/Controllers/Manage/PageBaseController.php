<?php

namespace Modules\Sys\Http\Controllers\Manage;

use App\Support\Respond;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use Modules\Sys\Repositories\Criteria\PageBaseCriteria;
use Modules\Sys\Repositories\Validators\PageBaseValidator;
use Modules\Sys\Services\PageBaseService;

class PageBaseController extends BaseController
{
    private $pageBaseService;
    private $pageBaseValidator;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(PageBaseService $pageBaseService, PageBaseValidator $pageBaseValidator)
    {
        $this->pageBaseService = $pageBaseService;
        $this->pageBaseValidator = $pageBaseValidator;
    }


    /**
     * 列表
     */
    public function list(Request $request)
    {
        $data = $this->pageBaseService->list($request, new PageBaseCriteria($request));

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
            'page_name' => $request['page_name'],  //名称
            'page_type' => $request['page_type'],  //页面类型
            'page_tpl' => $request['page_tpl'],   //页面布局模板
            'page_code' => $request['page_code'],  //页面代码
            'page_nav' => $request['page_nav'],   //导航数据
        ];

        return $data;
    }


    /**
     * 新增
     */
    public function add(Request $request)
    {
        $this->pageBaseValidator->with($request->all())->passesOrFail('create');
        $data = $this->pageBaseService->add($this->formatRequest($request));

        return Respond::success($data);
    }


    /**
     * 修改
     */
    public function edit(Request $request)
    {
        $page_id = $request['page_id'];
        if (!$request->filled('page_name')) {
            $data = $this->pageBaseService->editState($request, $page_id);
        } else {
            $this->pageBaseValidator->setId($page_id);
            $this->pageBaseValidator->with($request->all())->passesOrFail('update');
            $data = $this->pageBaseService->edit($page_id, $this->formatRequest($request));
        }

        return Respond::success($data);
    }


    /**
     * 删除
     */
    public function remove(Request $request)
    {
        $data = $this->pageBaseService->remove($request['page_id']);

        return Respond::success($data);
    }


    /**
     * 修改状态
     */
    public function editState(Request $request)
    {
        $page_id = $request->input('page_id', -1);
        $data = $this->pageBaseService->editState($page_id, $request);

        return Respond::success($data);
    }


    /**
     * 手机页面列表
     */
    public function listMobile(Request $request)
    {
        $request['size'] = 999;
        $data = $this->pageBaseService->list($request, new PageBaseCriteria($request));
        if ($data['data']) {
            foreach ($data['data'] as $k => $v) {
                $data['data'][$k]['AppId'] = $v['app_id'];
                $data['data'][$k]['Id'] = $v['page_id'];
                $data['data'][$k]['IsActivity'] = $v['page_activity'];
                $data['data'][$k]['IsArticle'] = $v['page_article'];
                $data['data'][$k]['IsGb'] = $v['page_gb'];
                $data['data'][$k]['IsHome'] = $v['page_index'];
                $data['data'][$k]['IsPoint'] = $v['page_point'];
                $data['data'][$k]['IsRelease'] = $v['page_release'];
                $data['data'][$k]['IsSns'] = $v['page_sns'];
                $data['data'][$k]['IsUpgrade'] = $v['page_upgrade'];
                $data['data'][$k]['PageCode'] = $v['page_code'];
                $data['data'][$k]['PageNav'] = $v['page_nav'];
                $data['data'][$k]['PageQRCode'] = $v['page_qrcode'];
                $data['data'][$k]['PageTitle'] = $v['page_name'];
                $data['data'][$k]['ShareImg'] = $v['page_share_image'];
                $data['data'][$k]['ShareTitle'] = $v['page_share_title'];
                $data['data'][$k]['StoreId'] = $v['store_id'];
            }
        }

        return Respond::success($data);
    }


    /**
     * 保存手机模板
     * 从原多商户copy过来的代码
     */
    public function saveMobile(Request $request)
    {
        $app_page_list = $request->input('app_page_list');
        $app_page_list_rows = json_decode($app_page_list, true);
        $store_id = $request->input('store_id', 0);
        $subsite_id = $request->input('subsite_id', 0);
        $tpl_id = $request->input('tpl_id');

        foreach ($app_page_list_rows as $page_nav_row) {
            $data = array();
            $data['store_id'] = $store_id;
            $data['page_tpl'] = $tpl_id;
            if ($page_nav_row['Id']) {
                $data['page_id'] = $page_nav_row['Id'];
            }

            $data['page_type'] = 3;
            $data['page_index'] = intval($page_nav_row['IsHome']);
            $data['page_name'] = $page_nav_row['PageTitle']; // 模块名称
            $data['page_code'] = $page_nav_row['PageCode'];
            $data['page_nav'] = $page_nav_row['PageNav'];
            if (isset($page_nav_row['PageConfig']) && $page_nav_row['PageConfig']) {
                $data['page_config'] = $page_nav_row['PageConfig'];
            }

            $data['page_share_title'] = $page_nav_row['ShareTitle'];
            $data['page_share_image'] = $page_nav_row['ShareImg'];
            $data['subsite_id'] = $subsite_id;

            $result = $this->pageBaseService->saveMobile($data);
        }

        return Respond::success($data);
    }


    public function getDataInfo(Request $request)
    {
        $data = $this->pageBaseService->getDataInfo($request);

        return Respond::success($data);
    }

}
