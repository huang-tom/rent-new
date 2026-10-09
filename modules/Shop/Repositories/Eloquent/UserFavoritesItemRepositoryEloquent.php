<?php

namespace Modules\Shop\Repositories\Eloquent;

use Kuteshop\Core\Repository\BaseRepository;
use Kuteshop\Core\Repository\Criteria\RequestCriteria;
use Modules\Shop\Repositories\Contracts\UserFavoritesItemRepository;
use Modules\Shop\Repositories\Models\UserFavoritesItem;

/**
 * Class UserFavoritesItemRepositoryEloquent.
 *
 * @package Modules\Shop\Repositories\Eloquent
 */
class UserFavoritesItemRepositoryEloquent extends BaseRepository implements UserFavoritesItemRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return UserFavoritesItem::class;
    }

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

}
