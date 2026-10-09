<?php

namespace Modules\Sys\Repositories\Eloquent;

use Kuteshop\Core\Repository\BaseRepository;
use Kuteshop\Core\Repository\Criteria\RequestCriteria;
use Modules\Sys\Repositories\Contracts\PagePcNavRepository;
use Modules\Sys\Repositories\Models\PagePcNav;

/**
 * Class PagePcNavRepositoryEloquent.
 *
 * @package Modules\Sys\Repositories\Eloquent
 */
class PagePcNavRepositoryEloquent extends BaseRepository implements PagePcNavRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return PagePcNav::class;
    }

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

}
