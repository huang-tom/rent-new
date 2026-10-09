<?php

namespace Modules\Sys\Repositories\Eloquent;

use Kuteshop\Core\Repository\BaseRepository;
use Kuteshop\Core\Repository\Criteria\RequestCriteria;
use Modules\Sys\Repositories\Contracts\PageMobileEntranceRepository;
use Modules\Sys\Repositories\Models\PageMobileEntrance;

/**
 * Class PageMobileEntranceRepositoryEloquent.
 *
 * @package Modules\Sys\Repositories\Eloquent
 */
class PageMobileEntranceRepositoryEloquent extends BaseRepository implements PageMobileEntranceRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return PageMobileEntrance::class;
    }

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

}
