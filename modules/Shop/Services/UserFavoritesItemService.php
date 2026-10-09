<?php

namespace Modules\Shop\Services;

use Kuteshop\Core\Service\BaseService;
use Modules\Pt\Repositories\Contracts\ProductBaseRepository;
use Modules\Pt\Repositories\Contracts\ProductItemRepository;
use Modules\Shop\Repositories\Contracts\UserFavoritesItemRepository;
use App\Exceptions\ErrorException;

/**
 * Class UserFavoritesItemService.
 *
 * @package Modules\Shop\Services
 */
class UserFavoritesItemService extends BaseService
{
    private $productItemRepository;
    private $productBaseRepository;

    public function __construct(
        UserFavoritesItemRepository $userFavoritesItemRepository,
        ProductItemRepository       $productItemRepository,
        ProductBaseRepository       $productBaseRepository
    )
    {
        $this->repository = $userFavoritesItemRepository;
        $this->productItemRepository = $productItemRepository;
        $this->productBaseRepository = $productBaseRepository;
    }


    /**
     * 获取列表
     * @return array
     */
    public function list($request, $criteria)
    {
        $limit = $request->get('size') ?? 10;
        $data = $this->repository->list($criteria, $limit);
        if (!empty($data['data'])) {
            $items = $data['data'];
            $item_ids = array_column($items, 'item_id');
            $product_items = $this->productItemRepository->gets($item_ids);
            $product_ids = array_column($product_items, 'product_id');
            $product_rows = $this->productBaseRepository->gets($product_ids);
            foreach ($items as $k => $item) {
                if (isset($product_items[$item['item_id']])) {
                    $product_item = $product_items[$item['item_id']];
                    $items[$k]['item_unit_price'] = $product_item['item_unit_price'];
                    $product_id = $product_item['product_id'];
                    if (isset($product_rows[$product_id])) {
                        $items[$k]['product_image'] = $product_rows[$product_id]['product_image'];
                        $items[$k]['product_item_name'] = $product_rows[$product_id]['product_name'] . $product_item['item_name'];
                    }
                }
            }

            $data['data'] = $items;
        }

        return $data;
    }


    /**
     * 根据商品SKU删除
     * @param $user_id
     * @param $item_id
     * @return bool
     * @throws ErrorException
     */
    public function removeByItemId($user_id = 0, $item_id = 0)
    {
        if (!$user_id || !$item_id) {
            throw new ErrorException(__('数据有误！'));
        }

        $favorites_item_ids = $this->repository->findKey([
            'item_id' => $item_id,
            'user_id' => $user_id
        ]);

        if ($favorites_item_ids) {
            $this->repository->remove($favorites_item_ids);

            return true;
        }

        throw new ErrorException(__('删除失败'));
    }

}
