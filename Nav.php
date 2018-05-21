<?php
namespace Plugin\ProductRank;

use Eccube\Common\EccubeNav;

class Nav implements EccubeNav
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
                'name' => 'admin.product_rank.move_rank.sub_title',
                'url' => 'admin_product_product_rank'
            ]
        ];
    }
}