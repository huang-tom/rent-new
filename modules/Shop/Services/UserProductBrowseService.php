<?php

namespace Modules\Shop\Services;

use Illuminate\Support\Facades\Cache;
use Kuteshop\Core\Service\BaseService;
use Modules\Pt\Services\ProductIndexService;
use Modules\Shop\Repositories\Contracts\UserSearchHistoryRepository;

/**
 * Class UserProductBrowseService.
 *
 * @package Modules\Shop\Services
 */
class UserProductBrowseService extends BaseService
{

    public function __construct(UserSearchHistoryRepository $userSearchHistoryRepository)
    {
        $this->repository = $userSearchHistoryRepository;
    }


    /**
     * 浏览记录
     * @param $request
     * @return array
     */
    public function getList($request)
    {
        $data = [];
        $user_id = $request->get('user_id', 0);
        $cache_key = sprintf("user_id|%d", $user_id);
        $product_browse_rows = Cache::get($cache_key);
        $product_browse_rows = !empty($product_browse_rows) ? json_decode($product_browse_rows, true) : [];
        if (!empty($product_browse_rows)) {
            $item_ids = array_column($product_browse_rows, 'item_id');
            $item_rows = app(ProductIndexService::class)->getItems($item_ids);
            foreach ($product_browse_rows as $row) {
                if (isset($item_rows[$row['item_id']])) {
                    $data[] = $item_rows[$row['item_id']];
                }
            }
        }

        return $data;
    }


    /**
     * 添加
     * @param $item_id
     * @param $user_id
     * @return array|mixed
     */
    public function addBrowser($item_id, $user_id)
    {
        $product_browse = [
            'item_id' => $item_id,
            'browse_time' => getTime(),
        ];

        $cache_key = sprintf("user_id|%d", $user_id);
        $product_browse_rows = Cache::get($cache_key);
        $product_browse_rows = !empty($product_browse_rows) ? json_decode($product_browse_rows, true) : [];

        $product_browse_rows = array_filter($product_browse_rows, function ($browse) use ($item_id) {
            return $browse['item_id'] !== $item_id;
        });

        if (count($product_browse_rows) >= 10) {
            array_pop($product_browse_rows);
        }

        array_unshift($product_browse_rows, $product_browse);
        Cache::put($cache_key, json_encode($product_browse_rows));

        return $product_browse_rows;
    }


    /**
     * 删除
     * @param $item_id
     * @param $user_id
     * @return true
     */
    public function removeBrowser($item_id, $user_id)
    {
        $cache_key = sprintf("user_id|%d", $user_id);
        $product_browse_rows = Cache::get($cache_key);
        $product_browse_rows = !empty($product_browse_rows) ? json_decode($product_browse_rows, true) : [];

        $product_browse_rows = array_filter($product_browse_rows, function ($browse) use ($item_id) {
            return $browse['item_id'] !== $item_id;
        });

        Cache::put($cache_key, json_encode($product_browse_rows));

        return true;
    }

}
