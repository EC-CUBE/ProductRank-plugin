<?php
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
            0 => new OrderByClause('pct.sort_no', 'DESC')
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