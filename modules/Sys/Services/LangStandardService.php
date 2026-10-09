<?php

namespace Modules\Sys\Services;

use Kuteshop\Core\Service\BaseService;
use Modules\Sys\Repositories\Contracts\LangStandardRepository;

/**
 * Class LangStandardService.
 *
 * @package Modules\Sys\Services
 */
class LangStandardService extends BaseService
{

    public function __construct(LangStandardRepository $langStandardRepository)
    {
        $this->repository = $langStandardRepository;
    }

    function getList($request, $criteria)
    {
        $data = $this->list($request, $criteria);

        //适应后端，将所有键值转换成小写字母
        if (isset($data['data']) && is_array($data['data'])) {
            foreach ($data['data'] as $key => $item) {
                $data['data'][$key] = $this->arrayKeysToLower($item);
            }
        }

        return $data;
    }


    /**
     * 递归将数组的键名转换为小写
     *
     * @param array $array
     * @return array
     */
    function arrayKeysToLower(array $array): array
    {
        $result = [];
        foreach ($array as $key => $value) {
            $key = strtolower($key); // 将键转换为小写
            if (is_array($value)) {
                $value = $this->arrayKeysToLower($value); // 递归处理子数组
            }
            $result[$key] = $value;
        }

        return $result;
    }

}
