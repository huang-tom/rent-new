<?php

namespace Modules\Invoicing\Repositories\Eloquent;

use Kuteshop\Core\Repository\BaseRepository;
use Kuteshop\Core\Repository\Criteria\RequestCriteria;
use Modules\Invoicing\Repositories\Contracts\StockBillItemRepository;
use Modules\Invoicing\Repositories\Models\StockBillItem;

/**
 * Class StockBillItemRepositoryEloquent.
 *
 * @package Modules\Invoicing\Repositories\Eloquent
 */
class StockBillItemRepositoryEloquent extends BaseRepository implements StockBillItemRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return StockBillItem::class;
    }

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

}
