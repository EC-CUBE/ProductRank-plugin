<?php
/*
* This file is part of EC-CUBE
*
* Copyright(c) 2000-2015 LOCKON CO.,LTD. All Rights Reserved.
* http://www.lockon.co.jp/
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Plugin\ProductRank\ServiceProvider;

use Plugin\ProductRank\Repository\ProductRankRepository;
use Silex\Application as BaseApplication;
use Silex\ServiceProviderInterface;

class ProductRankServiceProvider implements ServiceProviderInterface
{

    /**
     * ナビに新しい項目を追加します.
     * @param array $nav ナビの配列参照
     * @param array $addNavi 追加するナビ配列
     * @param array $ids 追加するナビのid配列
     */
    private static function addNavi(array &$nav, array $addNavi, array $ids = array())
    {
        $targetId = array_shift($ids);
        if (!$targetId) {
            // IDが無ければトップレベルの最後に追加
            $nav[] = $addNavi;
        }

        foreach ($nav as $key => $val) {
            if (strcmp($targetId, $val["id"]) == 0) {
                if (count($ids) > 0) {
                    return self::addNavi($nav[$key]['child'], $addNavi, $ids);
                }
                // 最後に追加
                $nav[$key]['child'][] = $addNavi;
                return true;
            }
        }

        return false;
    }

    public function register(BaseApplication $app)
    {
        // リポジトリ
        $app['eccube.plugin.product_rank.repository.product_rank'] = $app->share(function () use ($app) {
            return new ProductRankRepository($app['orm.em'], $app['orm.em']->getClassMetadata('\Eccube\Entity\ProductCategory'), $app);
        });

        $basePath = '/' . $app["config"]["admin_route"];
        // 一覧
        $app->match($basePath . '/product/product_rank/', '\\Plugin\\ProductRank\\Controller\\ProductRankController::index')
            ->bind('admin_product_product_rank');
        // 一覧：上
        $app->put($basePath . '/product/product_rank/{category_id}/{product_id}/up', '\\Plugin\\ProductRank\\Controller\\ProductRankController::up')
            ->assert('category_id', '\d+')
            ->assert('product_id', '\d+')
            ->bind('admin_product_product_rank_up');
        // 一覧：下
        $app->put($basePath . '/product/product_rank/{category_id}/{product_id}/down', '\\Plugin\\ProductRank\\Controller\\ProductRankController::down')
            ->assert('category_id', '\d+')
            ->assert('product_id', '\d+')
            ->bind('admin_product_product_rank_down');
        // 一覧：N番目へ移動
        $app->post($basePath . '/product/product_rank/moveRank', '\\Plugin\\ProductRank\\Controller\\ProductRankController::moveRank')
            ->bind('admin_product_product_rank_move_rank');
        // カテゴリ選択
        $app->match($basePath . '/product/product_rank/{category_id}', '\\Plugin\\ProductRank\\Controller\\ProductRankController::index')
            ->assert('category_id', '\d+')
            ->bind('admin_product_product_rank_show');

        // メッセージ登録
        $app['translator'] = $app->share($app->extend('translator', function ($translator, \Silex\Application $app) {
            $translator->addLoader('yaml', new \Symfony\Component\Translation\Loader\YamlFileLoader());

            $file = __DIR__ . '/../Resource/locale/message.' . $app['locale'] . '.yml';
            if (file_exists($file)) {
                $translator->addResource('yaml', $file, $app['locale']);
            }

            return $translator;
        }));

        // メニュー登録
        $app['config'] = $app->share($app->extend('config', function ($config) {
            $addNavi['id'] = "product_rank";
            $addNavi['name'] = "商品並び替え";
            $addNavi['url'] = "admin_product_product_rank";

            self::addNavi($config['nav'], $addNavi, array('product'));

            return $config;
        }));
    }

    public function boot(BaseApplication $app)
    {
    }
}
