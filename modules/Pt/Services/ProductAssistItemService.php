<?php

namespace Modules\Pt\Services;

use Illuminate\Support\Facades\DB;
use Kuteshop\Core\Service\BaseService;
use Modules\Pt\Repositories\Contracts\ProductAssistItemRepository;
use App\Exceptions\ErrorException;
use Modules\Pt\Repositories\Contracts\ProductAssistRepository;

/**
 * Class ProductAssistItemService.
 *
 * @package Modules\Pt\Services
 */
class ProductAssistItemService extends BaseService
{

    private $productAssistRepository;

    public function __construct(ProductAssistItemRepository $productAssistItemRepository, ProductAssistRepository $productAssistRepository)
    {
        $this->repository = $productAssistItemRepository;
        $this->productAssistRepository = $productAssistRepository;
    }


    /**
     * 新增
     * @param $request
     * @return bool
     * @throws ErrorException
     */
    public function add($request)
    {
        DB::beginTransaction();

        try {
            $assist_item = [
                'assist_id' => $request['assist_id'],     //属性ID
                'assist_item_name' => $request['assist_item_name'], //选项名称
                'assist_item_sort' => $request->input('assist_item_sort', 0)   //排序
            ];
            $result = $this->repository->add($assist_item);
            $assist_item_id = $result->getKey();

            $assist_id = $this->getAssistId($assist_item_id);
            $this->updateAssistItem($assist_id);

            DB::commit();
            return true;
        } catch (\Exception $e) {

            DB::rollBack();
            throw new ErrorException(__('添加失败: ') . $e->getMessage());
        }
    }


    /**
     * 删除
     * @param $request
     * @return bool
     * @throws ErrorException
     */
    public function remove($assist_item_id)
    {
        DB::beginTransaction();

        $assist_id = $this->getAssistId($assist_item_id);
        $this->repository->remove($assist_item_id);
        $result = $this->updateAssistItem($assist_id);

        if ($result) {
            DB::commit();
            return true;
        } else {
            DB::rollBack();
            throw new ErrorException(__('删除失败'));
        }
    }


    /**
     * 获取assist_id
     * @param $assist_item_id
     * @return int|mixed
     */
    public function getAssistId($assist_item_id = null)
    {
        $assist_id = 0;
        if ($assist_item_id) {
            $assist_row = $this->repository->getOne($assist_item_id);
            $assist_id = $assist_row['assist_id'];
        }

        return $assist_id;
    }


    /**
     * 更新属性中的assist_item
     * @param $assist_id
     * @return bool|mixed
     */
    public function updateAssistItem($assist_id = null)
    {
        if ($assist_id) {
            $assist_items = $this->repository->find(['assist_id' => $assist_id]);
            $assist_item_names = array_column($assist_items, 'assist_item_name');
            $assist_item = implode(',', $assist_item_names);
            return $this->productAssistRepository->edit($assist_id, ['assist_item' => $assist_item]);
        }

        return true;
    }

}
