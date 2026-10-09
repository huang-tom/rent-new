<?php

namespace Modules\Sys\Repositories\Eloquent;

use Modules\Sys\Repositories\Contracts\MaterialGalleryRepository;
use Modules\Sys\Repositories\Models\MaterialGallery;
use Kuteshop\Core\Repository\Criteria\RequestCriteria;
use Kuteshop\Core\Repository\BaseRepository;

/**
 * Class MaterialGalleryRepositoryEloquent.
 *
 * @package Modules\System\Repositories\Eloquent
 */
class MaterialGalleryRepositoryEloquent extends BaseRepository implements MaterialGalleryRepository
{

    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return MaterialGallery::class;
    }

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

}
