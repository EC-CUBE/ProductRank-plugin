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

namespace Plugin\ProductRank\Tests\Web\Admin;

use Eccube\Tests\Web\Admin\AbstractAdminWebTestCase;
use Eccube\Repository\ProductCategoryRepository;

class ProductRankControllerTest extends AbstractAdminWebTestCase
{
    /**
     * @var ProductCategoryRepository
     */
    protected $productCategoryRepository;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        parent::setUp();

        $this->productCategoryRepository = $this->container->get(ProductCategoryRepository::class);
    }

    /**
     * @param $categoryId
     * @param $expected
     * @dataProvider dataIndexProvider
     */
    public function testRoutingIndex($categoryId, $expected)
    {
        if (is_null($categoryId)) {
            $crawler = $this->client->request(
                'GET',
                $this->generateUrl('admin_product_product_rank')
            );
        } else {
            $crawler = $this->client->request(
                'GET',
                $this->generateUrl('admin_product_product_rank_show', ['category_id' => $categoryId])
            );
        }

        $this->assertTrue($this->client->getResponse()->isSuccessful());

        $this->actual = $crawler->filter('.c-contentsArea')->html();
        $this->expected = $expected;

        $this->assertContains($this->expected, $this->actual);
    }

    public function dataIndexProvider()
    {
        return [
            [null, 'カテゴリを選択してください。'],
            [1, 'ジェラート'],
            [2, '新入荷'],
            [2, 'チェリーアイスサンド'],
            [3, '彩のデザート'],
            [3, '彩のジェラートCUBE'],
            [4, 'CUBE'],
            [4, '彩のジェラートCUBE'],
            [5, 'アイスサンド'],
            [5, 'チェリーアイスサンド'],
        ];
    }

    public function testDown()
    {
        // GIVE
        $categoryId = 6;
        $Product = $this->createProduct('Product003');

        $ProductCategory = $this->productCategoryRepository->find([
            'product_id' => $Product->getId(),
            'category_id' => $categoryId,
        ]);

        // WHEN
        $url = $this->generateUrl('admin_product_product_rank_down', [
            'category_id' => $categoryId,
            'product_id' => $Product->getId(),
        ]);
        $this->client->request('PUT', $url);

        // check redirect
        $rUrl = $this->generateUrl('admin_product_product_rank_show', ['category_id' => $categoryId]);

        $this->assertTrue($this->client->getResponse()->isRedirect($rUrl));

        // verify data
        $ProductCategories = $this->productCategoryRepository->findBy(['category_id' => $categoryId]);
        $max = count($ProductCategories);
        $this->expected = $max - 1;

        $this->actual = $ProductCategory->getProductRankSortNo();
        $this->verify();
    }

    public function testUp()
    {
        // GIVE
        $categoryId = 6;
        $Product = $this->createProduct('Product003');

        $ProductCategories = $this->productCategoryRepository->findBy(['category_id' => $categoryId]);
        $count = count($ProductCategories);
        foreach ($ProductCategories as $pc) {
            $pc->setProductRankSortNo($count--);
        }

        $ProductCategory = $this->productCategoryRepository->find(['product_id' => $Product->getId(), 'category_id' => $categoryId]);
        $oldSortNo = $ProductCategory->getProductRankSortNo();

        // WHEN
        $url = $this->generateUrl('admin_product_product_rank_up', [
            'category_id' => $categoryId,
            'product_id' => $ProductCategory->getProductId(),
        ]);
        $this->client->request('PUT', $url);

        // check redirect
        $rUrl = $this->generateUrl('admin_product_product_rank_show', ['category_id' => $categoryId]);
        $this->assertTrue($this->client->getResponse()->isRedirect($rUrl));

        // verify data
        $this->expected = $oldSortNo + 1;
        $this->actual = $ProductCategory->getProductRankSortNo();
        $this->verify();
    }

    public function testMoveRank()
    {
        // GIVE
        $Product = $this->createProduct('Product003');
        $categoryId = $Product->getProductCategories()->current()->getCategoryId();
        $inputRank = 999;

        // WHEN
        $url = $this->generateUrl('admin_product_product_rank_move_rank', [
            'category_id' => $categoryId,
            'product_id' => $Product->getId(),
            'position' => $inputRank,
        ]);
        $this->client->request('POST',
            $url,
            [
                'category_id' => $categoryId,
                'product_id' => $Product->getId(),
                'position' => $inputRank,
                '_token' => 'dummy',
            ]
        );

        // check redirect
        $rUrl = $this->generateUrl('admin_product_product_rank_show', ['category_id' => $categoryId]);
        $this->assertTrue($this->client->getResponse()->isRedirect($rUrl));

        // verify data
        $this->expected = 1;

        $ProductCategory = $this->productCategoryRepository->find([
            'product_id' => $Product->getId(),
            'category_id' => $categoryId,
        ]);
        $this->actual = $ProductCategory->getProductRankSortNo();
        $this->verify();
    }

    public function testMoveRankAjax()
    {
        // GIVE
        $Product = $this->createProduct('Product003');
        $categoryId = $Product->getProductCategories()->current()->getCategoryId();
        $inputRank = 1;
        $productId = $Product->getId();
        $ProductCategory = $this->productCategoryRepository->find([
            'product_id' => $productId,
            'category_id' => $categoryId,
        ]);

        // WHEN
        $url = $this->generateUrl('admin_product_product_rank_move_rank', [
            'category_id' => $categoryId,
            'product_id' => $productId,
            'position' => $inputRank,
        ]);
        $this->client->request(
            'POST',
            $url,
            ['category_id' => $categoryId, 'product_id' => $productId, 'position' => $inputRank, '_token' => 'dummy'],
            [],
            ['HTTP_X-Requested-With' => 'XMLHttpRequest']
        );

        // check redirect
        $rUrl = $this->generateUrl('admin_product_product_rank_show', ['category_id' => $categoryId]);
        $this->assertFalse($this->client->getResponse()->isRedirect($rUrl));

        // verify data
        $ProductCategories = $this->productCategoryRepository->findBy(['category_id' => $categoryId]);
        $maxRank = count($ProductCategories);
        $this->expected = $maxRank;

        $this->actual = $ProductCategory->getProductRankSortNo();
        $this->verify();
    }
}
