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
                'name' => '商品並び替え',
                'url' => 'admin_product_product_rank'
            ]
        ];
    }
}