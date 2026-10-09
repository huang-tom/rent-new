<?php

namespace Modules\Sys\Services;

use Kuteshop\Core\Service\BaseService;
use Modules\Sys\Repositories\Contracts\NumberSeqRepository;

/**
 * Class NumberSeqService.
 *
 * @package Modules\Sys\Services
 */
class NumberSeqService extends BaseService
{

    public function __construct(NumberSeqRepository $numberSeqRepository)
    {
        $this->repository = $numberSeqRepository;
    }


    /**
     * 得到下一个Id
     * @param int $prefix
     * @return string $number_str 下一个序列号
     * @access public
     */
    public function createNextSeq($prefix)
    {
        $prefix = sprintf('%s-%s', $prefix, date('Ymd'));
        $number = $this->getNextSeq($prefix);

        return sprintf('%s-%s', $prefix, $number);
    }


    /**
     * 根据主键值，从数据库读取当前Number
     *
     * @param int $prefix
     * @return string $number_str 下一个序列号
     * @access public
     */
    public function getNextSeq($prefix)
    {
        $rows = $this->repository->gets($prefix);
        if (!$rows) {
            $number = 1;

            $data['prefix'] = $prefix; // 前缀
            $data['number'] = $number;

            $add_flag = $this->repository->add($data);
            if (!$add_flag) {
                $number = 0;
            }
        } else {
            $number = $rows[$prefix]['number'];
            $number = $number + 1;
        }

        if (!is_array($prefix)) {
            $prefix = [$prefix];
        }
        $this->repository->incrementFieldByIds($prefix, 'number');

        return $number;
    }

}
