<?php

/*
 * This file is part of EC-CUBE
 *
 * Copyright(c) LOCKON CO.,LTD. All Rights Reserved.
 *
 * http://www.lockon.co.jp/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\ProductRank\Entity;

use Eccube\Repository\QueryKey;
use Eccube\Doctrine\Query\OrderByCustomizer;
use Eccube\Entity\Master\ProductListOrderBy;
use Eccube\Common\EccubeConfig;
use Eccube\Doctrine\Query\OrderByClause;

class ProductRankCustomizer extends OrderByCustomizer
{
    /**
     * @var EccubeConfig
     */
    protected $eccubeConfig;

    /**
     * ProductRankCustomizer constructor.
     *
     * @param EccubeConfig $eccubeConfig
     */
    public function __construct(EccubeConfig $eccubeConfig)
    {
        $this->eccubeConfig = $eccubeConfig;
    }

    /**
     * {@inheritdoc}
     *
     * @param array $params
     * @param $queryKey
     *
     * @return OrderByClause[]
     */
    protected function createStatements($params, $queryKey)
    {
        if (!isset($params['orderby']) || !$params['orderby'] instanceof ProductListOrderBy) {
            return [];
        }
        /** @var ProductListOrderBy $OrderBy */
        $OrderBy = $params['orderby'];
        if ($OrderBy->getId() != $this->eccubeConfig->get('product_rank.product_list_order_id')) {
            return [];
        }

        return [
            0 => new OrderByClause('pct.product_rank_sort_no', 'DESC'),
        ];
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getQueryKey()
    {
        return QueryKey::PRODUCT_SEARCH;
    }
}
