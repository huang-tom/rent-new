<?php

namespace Modules\Sys\Services;

use Illuminate\Support\Facades\DB;
use Kuteshop\Core\Service\BaseService;
use Modules\Sys\Repositories\Contracts\PageModuleRepository;
use App\Exceptions\ErrorException;
use Modules\Sys\Repositories\Models\PageModule;


/**
 * Class PageModuleService.
 *
 * @package Modules\Sys\Services
 */
class PageModuleService extends BaseService
{

    public function __construct(PageModuleRepository $pageModuleRepository)
    {
        $this->repository = $pageModuleRepository;
    }


    /**
     * 楼层排序
     * @param $request
     * @return string[]|void
     * @throws ErrorException
     */
    public function sort($request)
    {
        $pm_id_string = $request->input('pm_id_string');
        $pm_ids = explode(',', $pm_id_string);

        // 获取指定的页面模块列表
        $page_module_rows = $this->repository->gets($pm_ids);
        if (empty($page_module_rows)) {
            throw new ErrorException(__('未找到楼层'));
        }

        DB::beginTransaction();
        try {
            foreach ($page_module_rows as $page_module_row) {
                $pm_id = $page_module_row['pm_id'];
                $pm_order = array_search($pm_id, $pm_ids);
                $this->repository->edit($pm_id, ['pm_order' => $pm_order]);
            }

            DB::commit();

            return $pm_ids;
        } catch (\Exception $e) {
            DB::rollBack();
        }
    }

}
