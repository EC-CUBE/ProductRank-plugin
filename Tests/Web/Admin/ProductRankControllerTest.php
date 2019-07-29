<?php
/**
 * This file is part of EC-CUBE
 *
 * Copyright(c) EC-CUBE CO.,LTD. All Rights Reserved.
 * https://www.ec-cube.co.jp/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\ProductRank\Tests\Web\Admin;


class ProductRankControllerTest extends ProductRankCommon
{
    /**
     * @param $categoryId
     * @param $expected
     * @dataProvider dataIndexProvider
     */
    public function testRoutingIndex($categoryId, $expected)
    {
        if (is_null($categoryId)) {
            $crawler = $this->client->request('GET', $this->app->url('admin_product_product_rank'));
        } else {
            $crawler = $this->client->request('GET', $this->app->url('admin_product_product_rank_show', array('category_id' => $categoryId)));
        }

        $this->assertTrue($this->client->getResponse()->isSuccessful());

        $this->actual = $crawler->filter('.container-fluid')->html();
        $this->expected = $expected;

        $this->assertContains($this->expected, $this->actual);
    }

    public function dataIndexProvider()
    {
        return array(
            array(null, 'カテゴリを選択してください。'),
            array(1, 'パーコレーター'),
            array(2, 'インテリア'),
            array(2, 'カテゴリを選択してください。'),
            array(3, '食器'),
            array(3, 'カテゴリを選択してください。'),
            array(4, '調理器具'),
            array(4, 'パーコレーター'),
            array(5, 'フォーク'),
            array(5, 'ディナーフォーク'),
        );
    }

    public function testDown()
    {
        // GIVE
        $categoryId = 6;
        $Product = $this->createProduct('Product003');

        $ProductCategory = $this->app['eccube.plugin.product_rank.repository.product_rank']->find(array('product_id' => $Product->getId(), 'category_id' => $categoryId));

        // WHEN
        $this->client->request('PUT', $this->app->url('admin_product_product_rank_down', array('category_id' => $categoryId, 'product_id' => $Product->getId())));

        // check redirect
        $this->assertTrue($this->client->getResponse()->isRedirect($this->app->url('admin_product_product_rank_show', array('category_id' => $categoryId))));

        // verify data
        $ProductCategories = $this->app['eccube.plugin.product_rank.repository.product_rank']->findBy(array('category_id' => $categoryId));
        $maxRank = count($ProductCategories);
        $this->expected = $maxRank - 1;

        $this->actual = $ProductCategory->getRank();
        $this->verify();
    }

    public function testUp()
    {
        // GIVE
        $categoryId = 6;
        $this->createProduct('Product003');
        $ProductCategory = $this->app['eccube.plugin.product_rank.repository.product_rank']->find(array('product_id' => 1, 'category_id' => $categoryId));
        $oldRank = $ProductCategory->getRank();

        // WHEN
        $this->client->request('PUT', $this->app->url('admin_product_product_rank_up', array('category_id' => $categoryId, 'product_id' => $ProductCategory->getProductId())));

        // check redirect
        $this->assertTrue($this->client->getResponse()->isRedirect($this->app->url('admin_product_product_rank_show', array('category_id' => $categoryId))));

        // verify data
        $this->expected = $oldRank + 1;

        $this->actual = $ProductCategory->getRank();
        $this->verify();
    }

    public function testMoveRank()
    {
        // GIVE
        $categoryId = 6;
        $Product = $this->createProduct('Product003');
        $inputRank = 999;

        // WHEN
        $this->client->request('POST',
            $this->app->url('admin_product_product_rank_move_rank',
                array('category_id' => $categoryId, 'product_id' => $Product->getId(), 'position' => $inputRank)),
            array('category_id' => $categoryId, 'product_id' => $Product->getId(), 'position' => $inputRank, '_token' => 'dummy')
        );

        // check redirect
        $this->assertTrue($this->client->getResponse()->isRedirect($this->app->url('admin_product_product_rank_show', array('category_id' => $categoryId))));

        // verify data
        $this->expected = 1;

        $ProductCategory = $this->app['eccube.plugin.product_rank.repository.product_rank']->find(array('product_id' => $Product->getId(), 'category_id' => $categoryId));
        $this->actual = $ProductCategory->getRank();
        $this->verify();
    }

    public function testMoveRankAjax()
    {
        // GIVE
        $categoryId = 6;
        $this->createProduct('Product003');
        $inputRank = 1;
        $productId = 1;
        $ProductCategory = $this->app['eccube.plugin.product_rank.repository.product_rank']->find(array('product_id' => $productId, 'category_id' => $categoryId));

        // WHEN
        $this->client->request('POST',
            $this->app->url('admin_product_product_rank_move_rank',
                array('category_id' => $categoryId, 'product_id' => $productId, 'position' => $inputRank)),
            array('category_id' => $categoryId, 'product_id' => $productId, 'position' => $inputRank, '_token' => 'dummy'),
            array(),
            array('HTTP_X-Requested-With' => 'XMLHttpRequest')
        );

        // check redirect
        $this->assertFalse($this->client->getResponse()->isRedirect($this->app->url('admin_product_product_rank_show', array('category_id' => $categoryId))));

        // verify data
        $ProductCategories = $this->app['eccube.plugin.product_rank.repository.product_rank']->findBy(array('category_id' => $categoryId));
        $maxRank = count($ProductCategories);
        $this->expected = $maxRank;

        $this->actual = $ProductCategory->getRank();
        $this->verify();
    }
}
