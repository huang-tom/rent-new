<?php

namespace Modules\Cms\Providers;

use Prettus\Repository\Providers\LumenRepositoryServiceProvider;

class CmsRepositoryServiceProvider extends LumenRepositoryServiceProvider
{
    /**
     * Bootstrap services.
     */
    public function register()
    {

        //文章分类表
        $this->app->bind(\Modules\Cms\Repositories\Contracts\ArticleCategoryRepository::class,
            \Modules\Cms\Repositories\Eloquent\ArticleCategoryRepositoryEloquent::class);

        //标签表
        $this->app->bind(\Modules\Cms\Repositories\Contracts\ArticleTagRepository::class,
            \Modules\Cms\Repositories\Eloquent\ArticleTagRepositoryEloquent::class);

        //文章表
        $this->app->bind(\Modules\Cms\Repositories\Contracts\ArticleBaseRepository::class,
            \Modules\Cms\Repositories\Eloquent\ArticleBaseRepositoryEloquent::class);

        //文章评论表
        $this->app->bind(\Modules\Cms\Repositories\Contracts\ArticleCommentRepository::class,
            \Modules\Cms\Repositories\Eloquent\ArticleCommentRepositoryEloquent::class);
    }
}
