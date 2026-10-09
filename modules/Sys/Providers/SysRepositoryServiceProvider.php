<?php

namespace Modules\Sys\Providers;

use Prettus\Repository\Providers\LumenRepositoryServiceProvider;

class SysRepositoryServiceProvider extends LumenRepositoryServiceProvider
{
    /**
     * Bootstrap services.
     */
    public function register()
    {

        $bindings = [
            \Modules\Sys\Repositories\Contracts\NumberSeqRepository::class =>
                \Modules\Sys\Repositories\Eloquent\NumberSeqRepositoryEloquent::class,

            //页面
            \Modules\Sys\Repositories\Contracts\PageBaseRepository::class =>
                \Modules\Sys\Repositories\Eloquent\PageBaseRepositoryEloquent::class,
            \Modules\Sys\Repositories\Contracts\PageModuleRepository::class =>
                \Modules\Sys\Repositories\Eloquent\PageModuleRepositoryEloquent::class,
            \Modules\Sys\Repositories\Contracts\PageMobileEntranceRepository::class =>
                \Modules\Sys\Repositories\Eloquent\PageMobileEntranceRepositoryEloquent::class,

            //PC页面导航
            \Modules\Sys\Repositories\Contracts\PagePcNavRepository::class =>
                \Modules\Sys\Repositories\Eloquent\PagePcNavRepositoryEloquent::class,

            //分类导航
            \Modules\Sys\Repositories\Contracts\PageCategoryNavRepository::class =>
                \Modules\Sys\Repositories\Eloquent\PageCategoryNavRepositoryEloquent::class,

            //地区表
            \Modules\Sys\Repositories\Contracts\DistrictBaseRepository::class =>
                \Modules\Sys\Repositories\Eloquent\DistrictBaseRepositoryEloquent::class,

            //配置表
            \Modules\Sys\Repositories\Contracts\ConfigBaseRepository::class =>
                \Modules\Sys\Repositories\Eloquent\ConfigBaseRepositoryEloquent::class,

            //配置类型表
            \Modules\Sys\Repositories\Contracts\ConfigTypeRepository::class =>
                \Modules\Sys\Repositories\Eloquent\ConfigTypeRepositoryEloquent::class,

            //素材表
            \Modules\Sys\Repositories\Contracts\MaterialBaseRepository::class =>
                \Modules\Sys\Repositories\Eloquent\MaterialBaseRepositoryEloquent::class,

            //素材分类表
            \Modules\Sys\Repositories\Contracts\MaterialGalleryRepository::class =>
                \Modules\Sys\Repositories\Eloquent\MaterialGalleryRepositoryEloquent::class,

            //快递公司
            \Modules\Sys\Repositories\Contracts\ExpressBaseRepository::class =>
                \Modules\Sys\Repositories\Eloquent\ExpressBaseRepositoryEloquent::class,

            //反馈类型
            \Modules\Sys\Repositories\Contracts\FeedbackTypeRepository::class =>
                \Modules\Sys\Repositories\Eloquent\FeedbackTypeRepositoryEloquent::class,

            //反馈分类
            \Modules\Sys\Repositories\Contracts\FeedbackCategoryRepository::class =>
                \Modules\Sys\Repositories\Eloquent\FeedbackCategoryRepositoryEloquent::class,

            //反馈列表
            \Modules\Sys\Repositories\Contracts\FeedbackBaseRepository::class =>
                \Modules\Sys\Repositories\Eloquent\FeedbackBaseRepositoryEloquent::class,

            //计划任务
            \Modules\Sys\Repositories\Contracts\CrontabBaseRepository::class =>
                \Modules\Sys\Repositories\Eloquent\CrontabBaseRepositoryEloquent::class,

            //操作日志
            \Modules\Sys\Repositories\Contracts\LogActionRepository::class =>
                \Modules\Sys\Repositories\Eloquent\LogActionRepositoryEloquent::class,

            //错误日志
            \Modules\Sys\Repositories\Contracts\LogErrorRepository::class =>
                \Modules\Sys\Repositories\Eloquent\LogErrorRepositoryEloquent::class,

            //保障服务
            \Modules\Sys\Repositories\Contracts\ContractTypeRepository::class =>
                \Modules\Sys\Repositories\Eloquent\ContractTypeRepositoryEloquent::class,

            //消息模板
            \Modules\Sys\Repositories\Contracts\MessageTemplateRepository::class =>
                \Modules\Sys\Repositories\Eloquent\MessageTemplateRepositoryEloquent::class,

            //字典分类
            \Modules\Sys\Repositories\Contracts\DictBaseRepository::class =>
                \Modules\Sys\Repositories\Eloquent\DictBaseRepositoryEloquent::class,

            //字典项
            \Modules\Sys\Repositories\Contracts\DictItemRepository::class =>
                \Modules\Sys\Repositories\Eloquent\DictItemRepositoryEloquent::class,

            //货币
            \Modules\Sys\Repositories\Contracts\CurrencyBaseRepository::class =>
                \Modules\Sys\Repositories\Eloquent\CurrencyBaseRepositoryEloquent::class,

            //语言
            \Modules\Sys\Repositories\Contracts\LangStandardRepository::class =>
                \Modules\Sys\Repositories\Eloquent\LangStandardRepositoryEloquent::class,
            \Modules\Sys\Repositories\Contracts\LangMetaRepository::class =>
                \Modules\Sys\Repositories\Eloquent\LangMetaRepositoryEloquent::class
        ];

        foreach ($bindings as $interface => $implementation) {
            $this->app->bind($interface, $implementation);
        }
    }

}
