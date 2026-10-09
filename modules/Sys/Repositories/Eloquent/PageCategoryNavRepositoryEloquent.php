<?php

namespace Modules\Sys\Repositories\Eloquent;

use Kuteshop\Core\Repository\BaseRepository;
use Kuteshop\Core\Repository\Criteria\RequestCriteria;
use Modules\Sys\Repositories\Contracts\PageCategoryNavRepository;
use Modules\Sys\Repositories\Models\PageCategoryNav;

/**
 * Class PageCategoryNavRepositoryEloquent.
 *
 * @package Modules\Sys\Repositories\Eloquent
 */
class PageCategoryNavRepositoryEloquent extends BaseRepository implements PageCategoryNavRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return PageCategoryNav::class;
    }

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

}
