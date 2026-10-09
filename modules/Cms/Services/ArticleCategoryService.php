<?php

namespace Modules\Cms\Services;

use App\Exceptions\ErrorException;
use Illuminate\Support\Facades\DB;
use Kuteshop\Core\Service\BaseService;
use Modules\Cms\Repositories\Contracts\ArticleBaseRepository;
use Modules\Cms\Repositories\Contracts\ArticleCategoryRepository;
use Modules\Cms\Repositories\Criteria\ArticleCategoryCriteria;
use Modules\Cms\Repositories\Models\ArticleCategory;

class ArticleCategoryService extends BaseService
{
    private $articleBaseRepository;

    public function __construct(
        ArticleCategoryRepository $articleCategoryRepository,
        ArticleBaseRepository     $articleBaseRepository
    )
    {
        $this->repository = $articleCategoryRepository;
        $this->articleBaseRepository = $articleBaseRepository;
    }


    /**
     * 获取分类列表 平台后端
     */
    public function tree($request)
    {
        $this->repository->pushCriteria(new ArticleCategoryCriteria($request));
        $rows = $this->repository->orderBy('category_order', 'ASC')->orderBy('category_id', 'ASC')->all()->toArray();

        //todo 绑定上下级关系
        $data = ArrayToTree($rows, 0, 'children', 'category_');

        return $data;
    }


    /**
     * 创建
     */
    public function addCategory($request)
    {
        try {
            DB::beginTransaction();

            //todo 1、添加分类
            $this->repository->add($request);

            //todo 2、修改上级叶节点状态
            if ($request['category_parent_id']) {
                $this->repository->edit($request['category_parent_id'], ['category_is_leaf' => 0]);
            }

            //todo 3、提交事务
            DB::commit();
            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            throw new ErrorException(__('添加失败: ') . $e->getMessage());
        }

    }


    /**
     * 删除分类 支持批量删除
     * @param $category_id
     * @return int
     * @throws ErrorException
     */
    public function removeCategory($category_id)
    {
        $row = $this->repository->getOne($category_id);
        if ($category_id && empty($row)) {
            throw new ErrorException(__('分类不存在'));
        }

        $sub_category = $this->repository->find(['category_parent_id' => $category_id]);
        if (!empty($sub_category)) {
            throw new ErrorException(__('该分类下有子分类,不允许删除'));
        }

        //todo 判断分类下是否有文章
        $article_base = $this->articleBaseRepository->find([['category_id', 'IN', [$category_id]]]);
        if (!empty($article_base)) {
            throw new ErrorException(__('该分类下有文章引用！'));
        }

        $del_category_parent_ids = array();
        $row['category_parent_id'] && array_push($del_category_parent_ids, $row['category_parent_id']);

        try {
            DB::beginTransaction();

            //todo 1、执行删除操作
            $this->repository->remove($category_id);

            //todo 2、修改上级叶节点状态
            $this->changeLeaf($del_category_parent_ids);

            //todo 3、提交事务
            DB::commit();
            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            throw new ErrorException(__('删除失败: ') . $e->getMessage());
        }
    }

    //todo 更改叶结点状态
    public function changeLeaf($ids)
    {
        $flag_row = [];
        foreach ($ids as $id) {
            $exists = $this->repository->findWhere(['category_parent_id' => $id])->toArray();
            if (empty($exists)) {
                $flag_row[] = $this->repository->editWhere(['category_id' => $id], ['category_is_leaf' => 1]);
            }
        }

        return is_ok($flag_row);
    }


    /**
     * 批量删除分类
     *
     * [新增 2026-09-23] 补 `/manage/cms/articleCategory/removeBatch`（原先 404）。
     * 前端 views/cms/articleCategory/index.vue:222 传的是**逗号串** `{category_id: "1,2,3"}`。
     *
     * 为什么不直接循环调用 removeCategory()：
     *   1) 会出现"父子一起勾选就永远删不掉"的自锁 ——
     *      循环到父级时，子级此时**还没被删**（取决于循环顺序，甚至删完又出现），
     *      父级的"有子分类"校验必然命中。这与地区管理那个坑是同一个（见技能 §16.4）。
     *      所以这里先做**整体**校验：只有当子分类「不在本次待删列表里」时才拒绝。
     *   2) 删除后要统一回写上级的 category_is_leaf，逐个删除会重复触发、且中途报错会留下半个状态。
     *
     * @param $category_ids 逗号串或数组
     * @return bool
     * @throws ErrorException
     */
    public function removeCategoryBatch($category_ids)
    {
        $ids = is_array($category_ids) ? $category_ids : explode(',', (string)$category_ids);
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));

        if (empty($ids)) {
            throw new ErrorException(__('请选择要删除的数据'));
        }

        $rows = $this->repository->find([['category_id', 'IN', $ids]]);
        if (count($rows) !== count($ids)) {
            throw new ErrorException(__('选中的分类中有不存在的记录，请刷新后重试'));
        }

        // 校验 1：存在下级（**排除同样在待删列表里的子分类**）
        // 若不排除，父子一起勾选时父级会因为"有子分类"被拒，导致永远删不掉。
        // 这里用 Model 显式查询而不是 repository->find([['x','NOT IN',$ids]])：
        // 全项目检索发现**没有一处**用过 'NOT IN' 这种操作符字符串，
        // 混淆过的 BaseRepository 是否支持无法确认 —— 不拿"可能不支持"去赌。
        $outside_children = ArticleCategory::whereIn('category_parent_id', $ids)
            ->whereNotIn('category_id', $ids)
            ->count();
        if ($outside_children > 0) {
            throw new ErrorException(sprintf(__('选中的分类中有 %d 项存在下级分类，请先删除下级'), $outside_children));
        }

        // 校验 2：分类下挂着文章
        $article_base = $this->articleBaseRepository->find([['category_id', 'IN', $ids]]);
        if (!empty($article_base)) {
            throw new ErrorException(sprintf(__('选中的分类中有 %d 篇文章引用，不可删除'), count($article_base)));
        }

        // 记录受影响的上父级，删除后统一回写叶节点标记
        $parent_ids = array_values(array_unique(array_filter(array_column($rows, 'category_parent_id'))));

        try {
            DB::beginTransaction();

            foreach ($ids as $category_id) {
                $this->repository->remove($category_id);
            }

            $this->changeLeaf($parent_ids);

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw new ErrorException(__('删除失败: ') . $e->getMessage());
        }
    }

}
