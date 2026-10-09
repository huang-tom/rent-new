<?php

namespace Modules\Pt\Services;

use Kuteshop\Core\Service\BaseService;
use Modules\Account\Repositories\Models\User;
use Modules\Pt\Repositories\Contracts\ProductCommentReplyRepository;
use App\Exceptions\ErrorException;
use Modules\Pt\Repositories\Contracts\ProductCommentRepository;

/**
 * Class ProductCommentService.
 *
 * @package Modules\Pt\Services
 */
class ProductCommentReplyService extends BaseService
{
    private $productCommentRepository;

    public function __construct(ProductCommentReplyRepository $productCommentReplyRepository, ProductCommentRepository $productCommentRepository)
    {
        $this->repository = $productCommentReplyRepository;
        $this->productCommentRepository = $productCommentRepository;
    }


    public function addCommentReply($request)
    {
        $comment_id = $request->get('comment_id', -1);
        $comment_row = $this->productCommentRepository->getOne($comment_id);
        if (empty($comment_row)) {
            throw new ErrorException(__('评论不存在'));
        }

        $user_row = User::getUser();
        $reply_row = [
            'comment_id' => $comment_id,
            'user_id' => $user_row['user_id'],
            'user_name' => $user_row['user_nickname'],
            'user_id_to' => $comment_row['user_id'],
            'user_name_to' => $comment_row['user_name'],
            'comment_reply_content' => $request->input('comment_reply_content', ''),
            'comment_reply_time' => getDateTime(),
            'comment_reply_enable' => true,
            'comment_reply_isadmin' => true
        ];

        return $this->repository->add($reply_row);

    }

}
