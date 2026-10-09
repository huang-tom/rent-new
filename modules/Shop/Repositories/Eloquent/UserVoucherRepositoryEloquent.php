<?php

namespace Modules\Shop\Repositories\Eloquent;

use App\Support\StateCode;
use Kuteshop\Core\Repository\BaseRepository;
use Kuteshop\Core\Repository\Criteria\RequestCriteria;
use Modules\Shop\Repositories\Contracts\UserVoucherRepository;
use Modules\Shop\Repositories\Models\UserVoucher;

/**
 * Class UserVoucherRepositoryEloquent.
 *
 * @package Modules\Shop\Repositories\Eloquent
 */
class UserVoucherRepositoryEloquent extends BaseRepository implements UserVoucherRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return UserVoucher::class;
    }

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }


    /**
     * 用户店铺可用优惠券
     * @param $store_row
     * @param $voucher_items
     * @return mixed
     */
    public function filterUserVouchers($store_row, $voucher_items)
    {
        return $voucher_items;
    }


    /**
     * 获取用户使用的优惠券信息
     * @param $user_voucher_ids
     * @param $voucher_items
     * @return array
     */
    public function getVoucherInfo($user_voucher_ids, $voucher_items)
    {
        $data = [
            'user_voucher_id' => 0,
            'voucher_price' => 0,
            'voucher_row' => []
        ];

        return $data;
    }


}
