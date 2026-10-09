<?php

namespace Modules\Pt\Providers;

use Prettus\Repository\Providers\LumenRepositoryServiceProvider;

class PtRepositoryServiceProvider extends LumenRepositoryServiceProvider
{
    /**
     * Bootstrap services.
     */
    public function register()
    {

        $bindings = [
            //商品分类
            \Modules\Pt\Repositories\Contracts\ProductCategoryRepository::class =>
                \Modules\Pt\Repositories\Eloquent\ProductCategoryRepositoryEloquent::class,

            //商品类型
            \Modules\Pt\Repositories\Contracts\ProductTypeRepository::class =>
                \Modules\Pt\Repositories\Eloquent\ProductTypeRepositoryEloquent::class,

            //商品属性
            \Modules\Pt\Repositories\Contracts\ProductAssistRepository::class =>
                \Modules\Pt\Repositories\Eloquent\ProductAssistRepositoryEloquent::class,
            \Modules\Pt\Repositories\Contracts\ProductAssistItemRepository::class =>
                \Modules\Pt\Repositories\Eloquent\ProductAssistItemRepositoryEloquent::class,

            //商品品牌
            \Modules\Pt\Repositories\Contracts\ProductBrandRepository::class =>
                \Modules\Pt\Repositories\Eloquent\ProductBrandRepositoryEloquent::class,

            //商品规格
            \Modules\Pt\Repositories\Contracts\ProductSpecRepository::class =>
                \Modules\Pt\Repositories\Eloquent\ProductSpecRepositoryEloquent::class,
            \Modules\Pt\Repositories\Contracts\ProductSpecItemRepository::class =>
                \Modules\Pt\Repositories\Eloquent\ProductSpecItemRepositoryEloquent::class,

            //商品标签
            \Modules\Pt\Repositories\Contracts\ProductTagRepository::class =>
                \Modules\Pt\Repositories\Eloquent\ProductTagRepositoryEloquent::class,

            //商品Info
            \Modules\Pt\Repositories\Contracts\ProductInfoRepository::class =>
                \Modules\Pt\Repositories\Eloquent\ProductInfoRepositoryEloquent::class,

            //商品Base
            \Modules\Pt\Repositories\Contracts\ProductBaseRepository::class =>
                \Modules\Pt\Repositories\Eloquent\ProductBaseRepositoryEloquent::class,

            //商品Index
            \Modules\Pt\Repositories\Contracts\ProductIndexRepository::class =>
                \Modules\Pt\Repositories\Eloquent\ProductIndexRepositoryEloquent::class,

            //商品AssistIndex
            \Modules\Pt\Repositories\Contracts\ProductAssistIndexRepository::class =>
                \Modules\Pt\Repositories\Eloquent\ProductAssistIndexRepositoryEloquent::class,

            //商品Image
            \Modules\Pt\Repositories\Contracts\ProductImageRepository::class =>
                \Modules\Pt\Repositories\Eloquent\ProductImageRepositoryEloquent::class,

            //商品ValidPeriod
            \Modules\Pt\Repositories\Contracts\ProductValidPeriodRepository::class =>
                \Modules\Pt\Repositories\Eloquent\ProductValidPeriodRepositoryEloquent::class,

            //商品Item
            \Modules\Pt\Repositories\Contracts\ProductItemRepository::class =>
                \Modules\Pt\Repositories\Eloquent\ProductItemRepositoryEloquent::class,

            //商品评论
            \Modules\Pt\Repositories\Contracts\ProductCommentRepository::class =>
                \Modules\Pt\Repositories\Eloquent\ProductCommentRepositoryEloquent::class,
            //评论回复
            \Modules\Pt\Repositories\Contracts\ProductCommentReplyRepository::class =>
                \Modules\Pt\Repositories\Eloquent\ProductCommentReplyRepositoryEloquent::class,
            //评论点赞
            \Modules\Pt\Repositories\Contracts\ProductCommentHelpfulRepository::class =>
                \Modules\Pt\Repositories\Eloquent\ProductCommentHelpfulRepositoryEloquent::class

        ];

        foreach ($bindings as $interface => $implementation) {
            $this->app->bind($interface, $implementation);
        }
    }

}
