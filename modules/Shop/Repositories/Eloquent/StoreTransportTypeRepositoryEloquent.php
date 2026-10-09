<?php

namespace Modules\Shop\Repositories\Eloquent;

use Kuteshop\Core\Repository\BaseRepository;
use Kuteshop\Core\Repository\Criteria\RequestCriteria;
use Modules\Shop\Repositories\Contracts\StoreTransportTypeRepository;
use Modules\Shop\Repositories\Models\StoreTransportType;

/**
 * Class StoreTransportTypeRepositoryEloquent.
 *
 * @package Modules\Shop\Repositories\Eloquent
 */
class StoreTransportTypeRepositoryEloquent extends BaseRepository implements StoreTransportTypeRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return StoreTransportType::class;
    }

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

}
