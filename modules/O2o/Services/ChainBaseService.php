<?php

namespace Modules\O2o\Services;

use Kuteshop\Core\Service\BaseService;
use Modules\O2o\Repositories\Contracts\ChainBaseRepository;


/**
 * Class ChainBaseService.
 *
 * @package Modules\O2o\Services
 */
class ChainBaseService extends BaseService
{

    public function __construct(ChainBaseRepository $chainBaseRepository)
    {
        $this->repository = $chainBaseRepository;
    }

    public function getNearChain($request)
    {
        $latitude = $request->input('lat', 0);
        $longitude = $request->input('lng', 0);
        try {
            $near_chain = $this->repository->getNearChain(abs($latitude), abs($longitude), 200000000000);
            if ($near_chain->isEmpty()) {
                $near_chain = [];
            }
            return [
                'data' => $near_chain,
                'total' => count($near_chain),
                'current_page' => 1,
                'limit' => 10,
                'last_page' => 1
            ];
        } catch (\Exception $e) {
            return [];
        }
    }

}
