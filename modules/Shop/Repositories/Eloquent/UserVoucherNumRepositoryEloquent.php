<?php

namespace Modules\Shop\Repositories\Eloquent;

use Kuteshop\Core\Repository\BaseRepository;
use Kuteshop\Core\Repository\Criteria\RequestCriteria;
use Modules\Shop\Repositories\Contracts\UserVoucherNumRepository;
use Modules\Shop\Repositories\Models\UserVoucherNum;

/**
 * Class UserVoucherNumRepositoryEloquent.
 *
 * @package Modules\Shop\Repositories\Eloquent
 */
class UserVoucherNumRepositoryEloquent extends BaseRepository implements UserVoucherNumRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return UserVoucherNum::class;
    }

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

}
