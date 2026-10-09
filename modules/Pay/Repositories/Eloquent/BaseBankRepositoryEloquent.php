<?php

namespace Modules\Pay\Repositories\Eloquent;

use Kuteshop\Core\Repository\BaseRepository;
use Kuteshop\Core\Repository\Criteria\RequestCriteria;
use Modules\Pay\Repositories\Contracts\BaseBankRepository;
use Modules\Pay\Repositories\Models\BaseBank;

/**
 * Class BaseBankRepositoryEloquent.
 *
 * @package Modules\Pay\Repositories\Eloquent
 */
class BaseBankRepositoryEloquent extends BaseRepository implements BaseBankRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return BaseBank::class;
    }

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

}
