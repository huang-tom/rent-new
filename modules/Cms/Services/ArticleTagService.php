<?php

namespace Modules\Cms\Services;

use App\Exceptions\ErrorException;
use Kuteshop\Core\Service\BaseService;
use Modules\Cms\Repositories\Contracts\ArticleBaseRepository;
use Modules\Cms\Repositories\Contracts\ArticleTagRepository;

class ArticleTagService extends BaseService
{
    private $articleTagRepository;
    private $articleBaseRepository;

    public function __construct(
        ArticleTagRepository  $articleTagRepository,
        ArticleBaseRepository $articleBaseRepository
    )
    {
        $this->repository = $articleTagRepository;
        $this->articleTagRepository = $articleTagRepository;
        $this->articleBaseRepository = $articleBaseRepository;
    }

    /**
     * 删除标签
     * @param $tag_id
     * @return int
     * @throws ErrorException
     */
    public function removeTag($tag_id)
    {
        //todo 判断分类下是否有文章
        $articles = $this->articleBaseRepository->find([['article_tags', 'FIND_IN_SET', [$tag_id]]]);
        if (!empty($articles)) {
            throw new ErrorException(sprintf(__("有 %d 章文章使用，不可删除"), count($articles)));
        }

        $flag = $this->articleTagRepository->remove($tag_id);

        return $flag;
    }


    /**
     * 批量删标签
     * @param $request
     * @return int
     * @throws ErrorException
     */
    public function removeBatch($request)
    {
        $tag_id_str = $request->input('tag_id');
        $tag_ids = explode(',', $tag_id_str);

        $can_del_tag_ids = array_filter($tag_ids, function ($tag_id) {
            return empty($this->articleBaseRepository->find([['article_tags', 'FIND_IN_SET', [$tag_id]]]));
        });

        if (!empty($can_del_tag_ids)) {
            return $this->articleTagRepository->remove($can_del_tag_ids);
        }

        throw new ErrorException(__("无可删除的标签"));
    }
}
