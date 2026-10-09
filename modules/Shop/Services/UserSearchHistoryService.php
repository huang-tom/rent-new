<?php

namespace Modules\Shop\Services;

use Kuteshop\Core\Service\BaseService;
use Modules\Shop\Repositories\Contracts\UserSearchHistoryRepository;
use Modules\Sys\Repositories\Contracts\ConfigBaseRepository;

/**
 * Class UserFavoritesItemService.
 *
 * @package Modules\Shop\Services
 */
class UserSearchHistoryService extends BaseService
{
    private $configBaseRepository;

    public function __construct(UserSearchHistoryRepository $userSearchHistoryRepository, ConfigBaseRepository $configBaseRepository)
    {
        $this->repository = $userSearchHistoryRepository;
        $this->configBaseRepository = $configBaseRepository;
    }

    /**
     * 返回搜索关键词
     *
     * @return SearchInfoRes
     */
    public function getSearchInfo($user_id)
    {
        $search_info = [];

        $suggest_search_words = $this->configBaseRepository->getConfig("suggest_search_words", '');
        $search_hot_words = $this->configBaseRepository->getConfig("search_hot_words", '');
        $search_info['suggest_search_words'] = explode(',', $suggest_search_words);
        $search_info['search_hot_words'] = explode(',', $search_hot_words);

        if ($user_id) {
            // 查询用户搜索记录
            $user_search_rows = $this->repository->find(['user_id' => $user_id], ['search_time' => 'DESC']);
            $keywords = [];
            if (!empty($user_search_rows)) {
                $keywords = array_column_unique($user_search_rows, 'search_keyword');
            }

            $search_info['search_history_words'] = $keywords;
        }

        return $search_info;
    }


}
