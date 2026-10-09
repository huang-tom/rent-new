<?php

namespace Modules\Admin\Repositories\Eloquent;

use Kuteshop\Core\Repository\BaseRepository;
use Kuteshop\Core\Repository\Criteria\RequestCriteria;
use Modules\Admin\Repositories\Contracts\MenuBaseRepository;
use Modules\Admin\Repositories\Models\MenuBase;

/**
 * Class MenuRepositoryEloquent.
 *
 * @package namespace Modules\Admin\Repositories\Eloquent;
 */
class MenuBaseRepositoryEloquent extends BaseRepository implements MenuBaseRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return MenuBase::class;
    }


    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

}

