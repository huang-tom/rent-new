<?php

namespace Modules\Sys\Services;

use Kuteshop\Core\Service\BaseService;
use Modules\Pt\Repositories\Contracts\ProductCategoryRepository;
use Modules\Sys\Repositories\Contracts\PageCategoryNavRepository;
use Modules\Sys\Repositories\Contracts\ConfigBaseRepository;
use Modules\Sys\Repositories\Contracts\PagePcNavRepository;
use Modules\Sys\Repositories\Criteria\PageCategoryNavCriteria;


/**
 * Class PageCategoryNavService.
 *
 * @package Modules\Sys\Services
 */
class PageCategoryNavService extends BaseService
{

    private $configBaseRepository;
    private $productCategoryRepository;
    private $pagePcNavRepository;


    public function __construct(
        PageCategoryNavRepository $pageCategoryNavRepository,
        ProductCategoryRepository $productCategoryRepository,
        ConfigBaseRepository      $configBaseRepository,
        PagePcNavRepository       $pagePcNavRepository,
    )
    {
        $this->repository = $pageCategoryNavRepository;
        $this->productCategoryRepository = $productCategoryRepository;
        $this->configBaseRepository = $configBaseRepository;
        $this->pagePcNavRepository = $pagePcNavRepository;
    }


    public function getPcLayout($request)
    {
        //todo 1、获取分类导航数据
        $request['size'] = 999;
        $request['category_nav_enable'] = 1;
        $nav_category_rows = $this->list($request, new PageCategoryNavCriteria($request));
        $page_nav_category = $nav_category_rows['data'];

        //todo 获取商品分类
        $product_category_rows = $this->productCategoryRepository->find(['category_is_enable' => 1], ['category_sort' => 'ASC']);
        $product_category_trees = ArrayToTree($product_category_rows, 0, 'children', 'category_');
        $product_category_trees = array_column($product_category_trees, null, 'category_id');

        $item_ids = [];
        foreach ($page_nav_category as $nav_key => $nav_category) {
            $cur_item_ids = array_filter(explode(',', $nav_category['item_ids']));
            if (!empty($cur_item_ids)) {
                $item_ids = array_merge($item_ids, $cur_item_ids);
            }
            $page_nav_category[$nav_key]['item_ids'] = array_unique($cur_item_ids);

            //分类树形结构
            $page_nav_category[$nav_key]['product_category_tree'] = [];
            if (isset($product_category_trees[$nav_category['category_ids']])) {
                $page_nav_category[$nav_key]['product_category_tree'] = $product_category_trees[$nav_category['category_ids']];
            }
        }

        $data['category_nav'] = $page_nav_category;
        $data['all_item_ids'] = $item_ids;

        //todo 2、获取PC导航数据
        $page_pc_navs = $this->pagePcNavRepository->find(['nav_enable' => 1]);
        $data['page_pc_nav'] = array_values($page_pc_navs);

        //todo 3、获取首页底部帮助导航
        $data['footer_article'] = [];
        $page_pc_help = $this->configBaseRepository->getConfig('page_pc_help', '');
        if ($page_pc_help) {
            $data['footer_article'] = json_decode($page_pc_help, true);
        }

        return $data;
    }

}
