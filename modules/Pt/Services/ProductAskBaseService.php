<?php

namespace Modules\Pt\Services;

use App\Exceptions\ErrorException;
use Illuminate\Support\Carbon;
use Modules\Account\Repositories\Models\UserInfo;
use Modules\Pt\Repositories\Models\ProductAskBase;

/**
 * Class ProductAskBaseService.
 *
 * 商品咨询（问答）后台服务。
 *
 * [新增 2026-09-23]
 *   补齐 `/manage/pt/productAskBase/{list,add,edit,remove,removeBatch}` 五个接口。
 *   前端页面与权限都已随包下发（菜单 4040 + 权限 4287/4289/4290/4291，
 *   4 个角色均已授权），唯独后端整体缺失 → 该页此前只能打开、任何操作都 404。
 *
 * ⚠️ 刻意 **不继承** Kuteshop\Core\Service\BaseService。
 *   本项目里 BaseService 的签名是：
 *       public function list(Request $request, $criteria)
 *   而这里的列表只需要一个 $request（没有 Repository/Criteria）。
 *   若继承并把子类方法命名为 list($request)，PHP 会在**类加载时**直接抛
 *       Fatal error: Declaration of ...::list($request) must be compatible with
 *       BaseService::list(Illuminate\Http\Request $M, $criteria)
 *   这是**整个类**的致命错误 —— 该 Service 下所有接口都会变成 500 HTML 页，
 *   而不只是 list 一个方法。同项目 DistrictBaseService 就踩过这个坑。
 *   所以：不需要 Repository 体系时，就不继承 BaseService。
 *
 * @package Modules\Pt\Services
 */
class ProductAskBaseService
{
    /** ask_question / ask_answer 都是 varchar(255)，富文本很容易超长 */
    const TEXT_MAX = 255;

    /** 昵称类字段 varchar(50) */
    const NICKNAME_MAX = 50;


    /**
     * 咨询列表（后台）
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function list($request)
    {
        // 兼容 page/size 与 page/rows 两套分页参数命名（本项目两种都有页面在用）
        $page = (int)$request->input('page', 1);
        $size = (int)$request->input('size', $request->input('rows', 10));
        $page = $page > 0 ? $page : 1;
        $size = ($size > 0 && $size <= 200) ? $size : 10;

        $query = ProductAskBase::query();

        // 前端查询表单只提供「商品编号」，其余为可选扩展过滤
        if (($product_id = $request->input('product_id', '')) !== '' && $product_id !== null) {
            $query->where('product_id', (int)$product_id);
        }
        if (($user_id = $request->input('user_id', '')) !== '' && $user_id !== null) {
            $query->where('user_id', (int)$user_id);
        }
        if (($ask_status = $request->input('ask_status', '')) !== '' && $ask_status !== null) {
            $query->where('ask_status', (int)$ask_status);
        }
        if (($ask_enable = $request->input('ask_enable', '')) !== '' && $ask_enable !== null) {
            $query->where('ask_enable', (int)(bool)$ask_enable);
        }

        $total = (clone $query)->count();

        $rows = $query->orderBy('ask_time', 'DESC')
            ->orderBy('ask_id', 'DESC')
            ->offset(($page - 1) * $size)
            ->limit($size)
            ->get();

        $items = [];
        foreach ($rows as $row) {
            $items[] = $this->formatRow($row->toArray());
        }

        return [
            'items'   => $items,
            'records' => $total,
            'total'   => $total,
            'page'    => $page,
            'size'    => $size,
        ];
    }


    /**
     * 新增咨询（后台代录 / 补录）
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     * @throws ErrorException
     */
    public function addAsk($request)
    {
        $product_id = (int)$request->input('product_id', 0);
        if ($product_id <= 0) {
            throw new ErrorException(__('请选择商品'));
        }

        $ask_question = $this->textValue($request->input('ask_question', ''), __('咨询内容'));
        if ($ask_question === '') {
            throw new ErrorException(__('请输入咨询内容'));
        }

        // 注意：该表所有业务列 NOT NULL 且无默认值，必须逐个补齐，
        // 否则 STRICT_TRANS_TABLES 下直接 SQL 报错（不会写 ''）。
        $user_id = (int)$request->input('user_id', 0);
        $user_nickname = $this->nickname($request->input('user_nickname', ''));
        if ($user_nickname === '' && $user_id > 0) {
            $user_nickname = $this->nickname($this->nicknameOfUser($user_id));
        }

        $ask_answer = $this->textValue($request->input('ask_answer', ''), __('答案'));

        // 回复人：不传就留空（未回复状态）
        $answer_user_id = (int)$request->input('ask_answer_user_id', 0);
        $answer_user_nickname = $this->nickname($request->input('ask_answer_user_nickname', ''));
        if ($answer_user_nickname === '' && $answer_user_id > 0) {
            $answer_user_nickname = $this->nickname($this->nicknameOfUser($answer_user_id));
        }

        $data = [
            'ask_type_id'               => (int)$request->input('ask_type_id', 0),
            'product_id'                => $product_id,
            'store_id'                  => (int)$request->input('store_id', 0),
            'user_id'                   => $user_id,
            'user_nickname'             => $user_nickname,
            'ask_question'              => $ask_question,
            'ask_answer'                => $ask_answer,
            'ask_answer_user_id'        => $answer_user_id,
            'ask_answer_user_nickname'  => $answer_user_nickname,
            'ask_status'                => $ask_answer === ''
                ? ProductAskBase::STATUS_UNANSWERED
                : ProductAskBase::STATUS_ANSWERED,
            'ask_enable'                => (int)(bool)$request->input('ask_enable', false),
            'ask_helpful'               => (int)$request->input('ask_helpful', 0),
        ];

        // 有答案才写回答时间；否则留给列默认值（'0000-00-00 00:00:00'）
        if ($ask_answer !== '') {
            $data['ask_answer_time'] = Carbon::now()->toDateTimeString();
        }

        $row = ProductAskBase::create($data);

        return $this->formatRow($row->toArray());
    }


    /**
     * 编辑咨询（**支持局部更新**）
     *
     * 前端两处调用同一接口：
     *   1. 列表里的「是否展示」开关 —— doEdit({ask_id, ask_enable})，只带这两个字段；
     *   2. 编辑弹窗 —— doEdit(整个表单)。
     * 所以这里必须**只更新请求里真正出现的字段**，绝不能整体覆盖，
     * 否则开关一拨就会把咨询内容/答案清空。
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     * @throws ErrorException
     */
    public function editAsk($request)
    {
        $ask_id = (int)$request->input('ask_id', 0);
        if ($ask_id <= 0) {
            throw new ErrorException(__('数据有误'));
        }

        $row = ProductAskBase::find($ask_id);
        if (empty($row)) {
            throw new ErrorException(__('咨询不存在'));
        }

        $data = [];

        if ($request->has('ask_question')) {
            $ask_question = $this->textValue($request->input('ask_question', ''), __('咨询内容'));
            if ($ask_question === '') {
                throw new ErrorException(__('请输入咨询内容'));
            }
            $data['ask_question'] = $ask_question;
        }

        if ($request->has('ask_answer')) {
            $ask_answer = $this->textValue($request->input('ask_answer', ''), __('答案'));
            $data['ask_answer'] = $ask_answer;

            if ($ask_answer !== '') {
                // 回复了：置为已回复并记录回答时间
                $data['ask_answer_time'] = Carbon::now()->toDateTimeString();
                $data['ask_status'] = ProductAskBase::STATUS_ANSWERED;
            } else {
                // 清空答案：回到未回复
                $data['ask_status'] = ProductAskBase::STATUS_UNANSWERED;
            }
        }

        if ($request->has('ask_enable')) {
            $data['ask_enable'] = (int)(bool)$request->input('ask_enable');
        }
        if ($request->has('ask_helpful')) {
            $data['ask_helpful'] = max(0, (int)$request->input('ask_helpful'));
        }
        if ($request->has('ask_type_id')) {
            $data['ask_type_id'] = (int)$request->input('ask_type_id');
        }
        if ($request->has('store_id')) {
            $data['store_id'] = (int)$request->input('store_id');
        }
        if ($request->has('product_id')) {
            $product_id = (int)$request->input('product_id');
            if ($product_id <= 0) {
                throw new ErrorException(__('请选择商品'));
            }
            $data['product_id'] = $product_id;
        }
        if ($request->has('user_id')) {
            $data['user_id'] = (int)$request->input('user_id');
        }
        if ($request->has('user_nickname')) {
            $data['user_nickname'] = $this->nickname($request->input('user_nickname', ''));
        }
        if ($request->has('ask_answer_user_id')) {
            $data['ask_answer_user_id'] = (int)$request->input('ask_answer_user_id');
        }
        if ($request->has('ask_answer_user_nickname')) {
            $data['ask_answer_user_nickname'] = $this->nickname(
                $request->input('ask_answer_user_nickname', '')
            );
        }

        if (empty($data)) {
            throw new ErrorException(__('无修改数据'));
        }

        // 用户在弹窗里改了 user_id 但没同步昵称时，补一次昵称
        if (isset($data['user_id']) && !isset($data['user_nickname'])
            && (int)$data['user_id'] > 0 && empty($row->user_nickname)) {
            $data['user_nickname'] = $this->nickname($this->nicknameOfUser((int)$data['user_id']));
        }

        // ask_time / ask_id 一律不允许被改写（不在白名单内）
        $row->fill($data)->save();

        return $this->formatRow($row->fresh()->toArray());
    }


    /**
     * 删除咨询（单条 / 批量）
     *
     * 前端两种调用：
     *   - 单条：doRemove({ask_id: 5})
     *   - 批量：doRemoveBatch({ask_id: '5,6,7'})   ← 逗号串
     * 后端对 标量 / 逗号串 / 数组 三种形态都要能吃。
     *
     * @param \Illuminate\Http\Request $request
     * @return int
     * @throws ErrorException
     */
    public function removeAsk($request)
    {
        $ids = $this->normalizeIds($request->input('ask_id', null));
        if (empty($ids)) {
            throw new ErrorException(__('请选择要删除的数据'));
        }

        return ProductAskBase::whereIn('ask_id', $ids)->delete();
    }


    // ------------------------------------------------------------------
    // 内部工具
    // ------------------------------------------------------------------

    /**
     * 把 标量 / 逗号串 / 数组 统一成去重后的正整数数组
     *
     * @param mixed $value
     * @return array
     */
    private function normalizeIds($value)
    {
        if (is_array($value)) {
            $parts = $value;
        } elseif (is_string($value)) {
            $parts = explode(',', $value);
        } elseif ($value === null || $value === '') {
            $parts = [];
        } else {
            $parts = [$value];
        }

        $ids = [];
        foreach ($parts as $part) {
            $id = (int)$part;
            if ($id > 0) {
                $ids[] = $id;
            }
        }

        return array_values(array_unique($ids));
    }


    /**
     * 短文本字段取值 + 长度校验
     *
     * ⚠️ ask_question / ask_answer 都是 varchar(255)。前端这两个输入用的是
     *    富文本编辑器（MsRichEditor），一段带 HTML 标签的内容几十个字符就破 255。
     *    这里必须**先于 MySQL** 拦下来并给出看得懂的错误，
     *    否则 STRICT_TRANS_TABLES 会抛一条原始 SQL 错误给用户看。
     *
     * @param mixed  $value
     * @param string $label
     * @return string
     * @throws ErrorException
     */
    private function textValue($value, $label)
    {
        $text = trim((string)$value);

        if (mb_strlen($text, 'UTF-8') > self::TEXT_MAX) {
            throw new ErrorException(
                __(':label最多 :num 个字符（该字段为短文本，不支持富文本长内容）', [
                    'label' => $label,
                    'num'   => self::TEXT_MAX,
                ])
            );
        }

        return $text;
    }


    /**
     * 昵称截断（varchar(50)）
     *
     * @param mixed $value
     * @return string
     */
    private function nickname($value)
    {
        $name = trim((string)$value);

        return mb_substr($name, 0, self::NICKNAME_MAX, 'UTF-8');
    }


    /**
     * 按用户编号取昵称（account_user_info.user_nickname）
     *
     * @param int $user_id
     * @return string
     */
    private function nicknameOfUser($user_id)
    {
        if ($user_id <= 0) {
            return '';
        }

        return (string)UserInfo::where('user_id', $user_id)->value('user_nickname');
    }


    /**
     * 输出前统一整形
     *
     * 1. ask_answer_time 的空值字面量 '0000-00-00 00:00:00' 必须转成 ''，
     *    否则前端 formatDateTime 会 new Date('0000-00-00 00:00:00') 得到
     *    Invalid Date，表格里显示成 "NaN-NaN-NaN NaN:NaN:NaN"。
     *    （formatDefault 里 `data == 0` 会把空串判为真，直接返回 null → 单元格留空）
     * 2. tinyint 列统一成 int，避免前端 :active-value=true 的比较出现 '1' !== true 这类问题。
     *
     * @param array $row
     * @return array
     */
    private function formatRow(array $row)
    {
        if (!isset($row['ask_answer_time'])
            || $row['ask_answer_time'] === ProductAskBase::TIME_EMPTY
            || str_starts_with((string)$row['ask_answer_time'], '0000-00-00')) {
            $row['ask_answer_time'] = '';
        }

        foreach (['ask_id', 'ask_type_id', 'store_id', 'user_id', 'ask_answer_user_id', 'ask_helpful'] as $key) {
            if (isset($row[$key])) {
                $row[$key] = (int)$row[$key];
            }
        }
        foreach (['ask_status', 'ask_enable'] as $key) {
            if (isset($row[$key])) {
                $row[$key] = (int)$row[$key];
            }
        }

        foreach (['user_nickname', 'ask_question', 'ask_answer', 'ask_answer_user_nickname'] as $key) {
            if (isset($row[$key])) {
                $row[$key] = (string)$row[$key];
            }
        }

        return $row;
    }
}
