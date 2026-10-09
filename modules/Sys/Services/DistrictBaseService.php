<?php

namespace Modules\Sys\Services;

use Kuteshop\Core\Service\BaseService;
use Modules\Sys\Repositories\Contracts\DistrictBaseRepository;
use Modules\Sys\Repositories\Models\DistrictBase;
use App\Exceptions\ErrorException;

/**
 * Class DistrictBaseService.
 *
 * @package Modules\Sys\Services
 */
class DistrictBaseService extends BaseService
{

    public function __construct(DistrictBaseRepository $districtBaseRepository)
    {
        $this->repository = $districtBaseRepository;
    }


    /**
     * 地址库
     * @param $request
     * @return array
     */
    public function tree($request)
    {
        $condition = [];
        if ($request->has('district_name') && $request['district_name']) {
            $condition[] = ['district_name', 'like', '%' . $request['district_name'] . '%'];
        }
        $data = $this->repository->find($condition);
        $data = ArrayToTree($data, 0, 'children', 'district_');

        return $data;
    }


    /**
     * 扁平列表（带分页）
     *
     * ⚠️⚠️ 方法名必须叫 flatList()，**不能叫 list()** ⚠️⚠️
     *   本类继承 Kuteshop\Core\Service\BaseService，而父类已经有一个
     *       public function list(Request $request, $criteria)
     *   PHP 在**类加载时**就强制「子类覆盖父类方法的签名必须兼容」。
     *   第一版我把方法命名成 list($request)，结果不是某个接口 500，而是
     *   整个 DistrictBaseService 一被加载就抛致命错误：
     *       Fatal error: Declaration of ...::list($request) must be compatible with
     *       Kuteshop\Core\Service\BaseService::list(Illuminate\Http\Request $GzbzM, $sAGPB)
     *   于是 /manage/sys/districtBase/{list,tree,add,edit,remove,removeBatch} 全部
     *   返回同一份 HTML 错误页（实测 1314 字节）。
     *
     *   ⇒ 凡是继承混淆的 Kuteshop\Core\Service\BaseService 的服务类，都不要用
     *     list / lists / find / count / add / formatData / edit / remove / get / gets
     *     这十个名字定义自己的方法，除非签名与父类完全一致。
     *     （FeedbackBaseService::remove() 就是刻意保持同签名覆盖的。）
     *
     * 与 tree() 的分工：tree() 返回嵌套结构，后台表格用它渲染树；本方法返回扁平列表，
     * 供「逐级加载的地区选择器 / 数据核对 / 导出」类场景使用。
     *
     * [新增 2026-09-22] 修的是「路由声明了、方法却不存在」的静默断链：
     *   GET /manage/sys/districtBase/list 从 Sys/Routes/web.php 起就一直有声明，
     *   但 DistrictBaseController 从来没有 list() 方法。Lumen 找不到 action 会抛
     *   NotFoundHttpException → 对外表现成 404「接口不存在」，和「路由没注册」完全
     *   同表现，所以此前的路由存在性扫描扫不出来（是 route_method_audit.py 扫出来的）。
     *   前端 api/sys/districtBase.ts 早已导出 getList()，此前无处可用。
     *
     * @param $request
     * @return array
     */
    public function flatList($request)
    {
        $page = max(1, (int)$request->input('page', 1));
        $size = (int)$request->input('size', 100);
        $size = ($size > 0 && $size <= 2000) ? $size : 100;

        $query = DistrictBase::query();

        if ($request->filled('district_name')) {
            $query->where('district_name', 'like', '%' . $request->input('district_name') . '%');
        }
        // 指定父级时只返回直接下级（地区选择器逐级加载的用法）
        if ($request->filled('district_parent_id')) {
            $query->where('district_parent_id', (int)$request->input('district_parent_id'));
        }
        if ($request->filled('district_level')) {
            $query->where('district_level', (int)$request->input('district_level'));
        }

        $records = (clone $query)->count();
        $items = $query->orderBy('district_sort', 'ASC')
            ->orderBy('district_id', 'ASC')
            ->forPage($page, $size)
            ->get()
            ->toArray();

        // items / records 是本项目分页接口的通用形状（前端读 data.items + data.records），
        // total 一并给出，兼容按 total 取值的老页面。
        return [
            'items'   => $items,
            'records' => $records,
            'total'   => $records,
            'page'    => $page,
            'size'    => $size,
        ];
    }


    /**
     * 批量删除
     *
     * [新增 2026-09-22] 前端「地区管理」的「批量删除」按钮一直在调
     * POST /manage/sys/districtBase/removeBatch，但后端从未注册过这条路由 → 点一次 404 一次。
     *
     * 直接走 Model 而不用混淆的 BaseRepository：本项目 4 个 core 基类被混淆（见排查报告），
     * 其 remove() 对数组入参的行为不透明，这里用 whereIn 显式表达，行为完全可控。
     *
     * @param $request
     * @return bool
     * @throws ErrorException
     */
    public function removeBatch($request)
    {
        // 前端传的是逗号拼接串（selectRows.map(i => i.district_id).join()），兼容数组形态
        $ids = $request['district_id'];
        $ids = is_array($ids) ? $ids : explode(',', (string)$ids);
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));

        if (empty($ids)) {
            throw new ErrorException(__('请选择要删除的数据'));
        }

        // 存在下级地区的不允许删 —— 否则树结构断裂、留下孤儿节点。
        // ⚠️ 必须排除「子节点本身也在待删列表里」的情形：
        //    树形层级里父子常常被一起勾选（如勾了父级，前端把子级也带上），
        //    若不排除，子节点会因为它父亲在 ids 里而被算作"有下级"，导致父子一起删永远失败。
        $has_children = DistrictBase::whereIn('district_parent_id', $ids)
            ->whereNotIn('district_id', $ids)
            ->count();
        if ($has_children > 0) {
            throw new ErrorException(sprintf(__('选中的地区中有 %d 项存在下级地区，请先删除下级'), $has_children));
        }

        $deleted = DistrictBase::whereIn('district_id', $ids)->delete();
        if ($deleted > 0) {
            return true;
        }

        throw new ErrorException(__('删除失败'));
    }

}
