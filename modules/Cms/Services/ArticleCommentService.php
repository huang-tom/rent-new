<?php

namespace Modules\Cms\Services;

use Kuteshop\Core\Service\BaseService;
use Modules\Cms\Repositories\Contracts\ArticleCommentRepository;

class ArticleCommentService extends BaseService
{

    public function __construct(ArticleCommentRepository $articleCommentRepository)
    {
        $this->repository = $articleCommentRepository;
    }

}
