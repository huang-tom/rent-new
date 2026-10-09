<?php

namespace Modules\Pt\Repositories\Eloquent;

use Kuteshop\Core\Repository\BaseRepository;
use Kuteshop\Core\Repository\Criteria\RequestCriteria;
use Modules\Pt\Repositories\Contracts\NewProductPromoRepository;
use Modules\Pt\Repositories\Models\NewProductPromo;

class NewProductPromoRepositoryEloquent extends BaseRepository implements NewProductPromoRepository
{
    public function model()
    {
        return NewProductPromo::class;
    }

    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }
}
