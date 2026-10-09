<?php

namespace Modules\Pt\Repositories\Eloquent;

use Kuteshop\Core\Repository\BaseRepository;
use Kuteshop\Core\Repository\Criteria\RequestCriteria;
use Modules\Pt\Repositories\Contracts\NewProductPromoCateRepository;
use Modules\Pt\Repositories\Models\NewProductPromoCate;

class NewProductPromoCateRepositoryEloquent extends BaseRepository implements NewProductPromoCateRepository
{
    public function model()
    {
        return NewProductPromoCate::class;
    }

    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }
}
