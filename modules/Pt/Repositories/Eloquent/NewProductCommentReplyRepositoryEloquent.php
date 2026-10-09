<?php

namespace Modules\Pt\Repositories\Eloquent;

use Kuteshop\Core\Repository\BaseRepository;
use Kuteshop\Core\Repository\Criteria\RequestCriteria;
use Modules\Pt\Repositories\Contracts\NewProductCommentReplyRepository;
use Modules\Pt\Repositories\Models\NewProductCommentReply;

class NewProductCommentReplyRepositoryEloquent extends BaseRepository implements NewProductCommentReplyRepository
{
    public function model()
    {
        return NewProductCommentReply::class;
    }

    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }
}
