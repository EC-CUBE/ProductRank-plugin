<?php
namespace Plugin\ProductRank;

use Eccube\Common\EccubeNav;

class ProductRankNav implements EccubeNav
{
    /**
     * {@inheritdoc}
     *
     * @return array
     */
    public static function getNav()
    {
        return [
            'product' => [
                'id' => 'product_rank',
                'name' => 'product_rank.admin.move_rank.sub_title',
                'url' => 'admin_product_product_rank'
            ]
        ];
    }
}