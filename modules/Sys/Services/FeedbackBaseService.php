<?php

namespace Modules\Sys\Services;

use App\Exceptions\ErrorException;
use Kuteshop\Core\Service\BaseService;
use Modules\Account\Repositories\Contracts\UserInfoRepository;
use Modules\Account\Repositories\Models\User;
use Modules\Sys\Repositories\Contracts\FeedbackBaseRepository;
use Modules\Sys\Repositories\Contracts\FeedbackCategoryRepository;
use Modules\Sys\Repositories\Contracts\FeedbackTypeRepository;
use Modules\Sys\Repositories\Models\FeedbackBase;

/**
 * Class FeedbackBaseService.
 *
 * @package Modules\Sys\Services
 */
class FeedbackBaseService extends BaseService
{

    private $feedbackTypeRepository;
    private $feedbackCategoryRepository;
    private $userInfoRepository;

    public function __construct(
        FeedbackBaseRepository     $feedbackBaseRepository,
        FeedbackTypeRepository     $feedbackTypeRepository,
        FeedbackCategoryRepository $feedbackCategoryRepository,
        UserInfoRepository         $userInfoRepository
    )
    {
        $this->repository = $feedbackBaseRepository;
        $this->feedbackTypeRepository = $feedbackTypeRepository;
        $this->feedbackCategoryRepository = $feedbackCategoryRepository;
        $this->userInfoRepository = $userInfoRepository;
    }


    /**
     * 新增反馈工单
     *
     * [新增 2026-09-22] 补齐 POST /manage/sys/feedbackBase/add。
     * 前端 api/sys/feedbackBase.ts 导出 doAdd()，FeedbackBaseEdit.vue:75 在「非编辑态」会调它；
     * 但原厂模板把「添加」按钮注释掉了（views/sys/feedbackBase/index.vue:5），
     * 所以这是一条「代码里调得到、界面上点不到」的断链。
     *
     * 语义：由后台代替某个会员建一条反馈工单（例如电话客服转录入）。
     * 因此 user_id 必填，并从 account_user_info 回填昵称，保证后台列表里
     * 「会员」一列不是空的。
     *
     * @param $request
     * @return array
     * @throws ErrorException
     */
    public function addFeedback($request)
    {
        $user_id = (int)$request->input('user_id', 0);
        $feedback_question = trim((string)$request->input('feedback_question', ''));

        if ($user_id <= 0) {
            throw new ErrorException(__('请选择反馈会员'));
        }
        if ($feedback_question === '') {
            throw new ErrorException(__('请输入反馈内容'));
        }
        if (mb_strlen($feedback_question) > 255) {
            throw new ErrorException(__('反馈内容不能超过 255 个字符'));
        }

        $user_info = $this->userInfoRepository->find(['user_id' => $user_id]);
        if (empty($user_info)) {
            throw new ErrorException(__('会员不存在'));
        }

        $row = [
            'feedback_category_id'  => (int)$request->input('feedback_category_id', 1001),
            'user_id'               => $user_id,
            'user_nickname'         => (string)($user_info[0]['user_nickname'] ?? ''),
            'feedback_question'     => $feedback_question,
            'feedback_question_url' => (string)$request->input('feedback_question_url', ''),
            'feedback_question_status' => $request->boolean('feedback_question_status', true),
            'feedback_question_result' => (int)$request->input('feedback_question_result', 0),
            'item_id'               => (int)$request->input('item_id', 0),
            // 后台代建 → 直接记操作人，便于追溯是谁录入的
            'admin_id'              => (int)User::getUserId(),
        ];

        $feedback = FeedbackBase::create($row);
        if (!$feedback) {
            throw new ErrorException(__('操作失败'));
        }

        return $feedback->toArray();
    }


    /**
     * 编辑反馈工单（不含「回复」）
     *
     * [新增 2026-09-22] 补齐 POST /manage/sys/feedbackBase/edit。
     *
     * ⚠️ 故意不开放 feedback_question_answer / feedback_question_answer_time：
     *    回复必须走 answer()（=feedbackBase/editAnswer），它会写入 admin_id 与回复时间。
     *    如果这里也允许改答案，就能绕过那套留痕，事后无法判断是谁回复的。
     *    所以本方法只开放「工单属性」类字段的白名单更新。
     *
     * @param $request
     * @return array
     * @throws ErrorException
     */
    public function editFeedback($request)
    {
        $feedback_id = (int)$request->input('feedback_id', 0);
        if ($feedback_id <= 0) {
            throw new ErrorException(__('数据有误'));
        }

        $feedback = FeedbackBase::find($feedback_id);
        if (empty($feedback)) {
            throw new ErrorException(__('反馈不存在'));
        }

        $allow = [
            'feedback_category_id'     => 'int',
            'feedback_question'        => 'string',
            'feedback_question_url'    => 'string',
            'feedback_question_result' => 'int',
            'item_id'                  => 'int',
        ];

        $update = [];
        foreach ($allow as $field => $type) {
            if (!$request->has($field)) {
                continue;
            }
            $update[$field] = $type === 'int'
                ? (int)$request->input($field)
                : (string)$request->input($field);
        }

        if ($request->has('feedback_question_status')) {
            $update['feedback_question_status'] = $request->boolean('feedback_question_status');
        }

        if (empty($update)) {
            throw new ErrorException(__('没有需要更新的内容'));
        }

        $feedback->fill($update)->save();

        return $feedback->toArray();
    }


    /**
     * 删除反馈工单（支持单个 / 批量）
     *
     * [重写 2026-09-22] 覆盖 BaseService::remove()。
     *
     * 原因：前端「单个删除」和「批量删除」指向的是**同一条**路由
     * （api/sys/feedbackBase.ts 里 doRemove 与 doRemoveBatch 的 url 完全相同，
     *  见该文件第 46 / 54 行），批量时提交的是逗号拼接串：
     *     const feedback_id = state.selectRows.map((item) => item.feedback_id).join()
     *     doRemoveBatch({ feedback_id })
     * 而 BaseService::remove() 最终落到被混淆的 BaseRepository::remove()，
     * 它对「逗号串」这种入参的行为无法从源码确认（4 个 core 基类被混淆）。
     * 与其赌，不如在这里显式拆分成 ID 数组并用 whereIn 删除，行为 100% 可控。
     *
     * @param $feedback_id 单个 ID 或逗号拼接串或数组
     * @return bool
     * @throws ErrorException
     */
    public function remove($feedback_id)
    {
        $ids = is_array($feedback_id) ? $feedback_id : explode(',', (string)$feedback_id);
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));

        if (empty($ids)) {
            throw new ErrorException(__('请选择要删除的数据'));
        }

        $deleted = FeedbackBase::whereIn('feedback_id', $ids)->delete();
        if ($deleted > 0) {
            return true;
        }

        throw new ErrorException(__('删除失败'));
    }


    /**
     * 回复反馈
     * @param $request
     * @return mixed
     */
    public function answer($request)
    {
        $user_id = User::getUserId();
        $result = $this->repository->edit($request['feedback_id'], [
            'admin_id' => $user_id,
            'feedback_question_answer' => $request['feedback_question_answer'],
            'feedback_question_answer_time' => date('Y-m-d H:i:s')
        ]);

        return $result;
    }


    /**
     * 获取反馈类型分类
     * @return array
     */
    public function getCategory()
    {
        $type_rows = $this->feedbackTypeRepository->find(['feedback_type_enable' => 1]);
        if (!empty($type_rows)) {
            $type_ids = array_column($type_rows, 'feedback_type_id');
            $category_rows = $this->feedbackCategoryRepository->find([
                ['feedback_type_id', 'IN', $type_ids],
                ['feedback_category_enable', '=', 1]
            ]);

            $feedback_type_category = [];
            foreach ($category_rows as $category_row) {
                if (!array_key_exists($category_row['feedback_type_id'], $feedback_type_category)) {
                    $feedback_type_category[$category_row['feedback_type_id']] = [];
                }
                $feedback_type_category[$category_row['feedback_type_id']][] = $category_row;
            }

            foreach ($type_rows as $k => $type_row) {
                $type_rows[$k]['rows'] = $feedback_type_category[$type_row['feedback_type_id']];
            }

        }

        return array_values($type_rows);
    }

}
