<?php

namespace Modules\Pt\Repositories\Eloquent;

use Kuteshop\Core\Repository\BaseRepository;
use Kuteshop\Core\Repository\Criteria\RequestCriteria;
use Modules\Pt\Repositories\Contracts\NewProductCommentRepository;
use Modules\Pt\Repositories\Models\NewProductComment;

class NewProductCommentRepositoryEloquent extends BaseRepository implements NewProductCommentRepository
{
    public function model()
    {
        return NewProductComment::class;
    }

    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }
}
