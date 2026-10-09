<?php

namespace Modules\Sys\Http\Controllers\Front;

use App\Support\Respond;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use Modules\Sys\Services\PageBaseService;
use Modules\Sys\Services\PageCategoryNavService;
use Modules\Account\Repositories\Models\User;


class PageController extends BaseController
{
    private $pageBaseService;
    private $pageCategoryNavService;


    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        PageBaseService        $pageBaseService,
        PageCategoryNavService $pageCategoryNavService
    )
    {
        $this->pageBaseService = $pageBaseService;
        $this->pageCategoryNavService = $pageCategoryNavService;
    }


    /**
     * 首页装修数据
     */
    public function getPcPage(Request $request)
    {
        $page_type = $request->input('page_index', 'page_index');

        // 映射数组
        $page_mapping = [
            'page_index' => 'page_index',
            'page_sns' => 'page_sns',
            'page_article' => 'page_article',
            'page_point' => 'page_point',
            'page_upgrade' => 'page_upgrade',
            'page_zerobuy' => 'page_zerobuy',
            'page_higharea' => 'page_higharea',
            'page_taday' => 'page_taday',
            'page_everyday' => 'page_everyday',
            'page_secondkill' => 'page_secondkill',
            'page_secondday' => 'page_secondday',
            'page_rura' => 'page_rura',
            'page_likeyou' => 'page_likeyou',
            'page_exchange' => 'page_exchange',
            'page_new' => 'page_new',
            'page_newperson' => 'page_newperson',
        ];

        $where = ['page_type' => 2];

        // 动态设置条件
        if (isset($page_mapping[$page_type])) {
            $where[$page_mapping[$page_type]] = 1;
        } else {
            $where['page_index'] = 1; // 默认值
        }

        // 获取页面详情
        $page_rows = $this->pageBaseService->pcDetail($where);
        $data['floor'] = array_values($page_rows);

        return Respond::success($data);
    }


    /**
     * PC页面导航数据
     */
    public function pcLayout(Request $request)
    {
        $data = $this->pageCategoryNavService->getPcLayout($request);

        // 获取当前登录用户信息
        $user_row = User::getUser();
        if ($user_row) {
            $data['user_nickname'] = "Hi," . $user_row['user_nickname'] . "!";
            $data['user_avatar'] = $user_row['user_avatar'];
        }

        return Respond::success($data);
    }


    public function getMobilePage(Request $request)
    {
        $page_type = $request->input('page_index', 'page_index');

        // 定义字段映射
        $page_fields = [
            'page_index',
            'page_sns',
            'page_article',
            'page_point',
            'page_upgrade',
            'page_zerobuy',
            'page_higharea',
            'page_taday',
            'page_everyday',
            'page_secondkill',
            'page_secondday',
            'page_rura',
            'page_likeyou',
            'page_exchange',
            'page_new',
            'page_newperson',
        ];

        // 验证请求的类型是否有效
        if (in_array($page_type, $page_fields)) {
            $where = [$page_type => 1];
        } else {
            $where['page_index'] = 1; // 默认值
        }
        $where['page_type'] = 3;

        $page_base_res = $this->pageBaseService->mobileDetail($where);

        return Respond::success($page_base_res);
    }

}
