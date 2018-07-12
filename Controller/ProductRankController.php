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

namespace Plugin\ProductRank\Controller;

use Eccube\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityNotFoundException;
use Eccube\Repository\CategoryRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Eccube\Entity\Category;
use Plugin\ProductRank\Repository\ProductRankRepository;
use Eccube\Repository\ProductCategoryRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Eccube\Entity\ProductCategory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class ProductRankController extends AbstractController
{
    /**
     * @var CategoryRepository
     */
    protected $categoryRepository;

    /**
     * @var ProductRankRepository
     */
    protected $productRankRepository;

    /**
     * @var ProductCategoryRepository
     */
    protected $productCategoryRepository;

    /**
     * ProductRankController constructor.
     *
     * @param ProductRankRepository $productRankRepository
     * @param CategoryRepository $categoryRepository
     * @param ProductCategoryRepository $productCategoryRepository
     */
    public function __construct(
        ProductRankRepository $productRankRepository,
        CategoryRepository $categoryRepository,
        ProductCategoryRepository $productCategoryRepository
    ) {
        $this->productRankRepository = $productRankRepository;
        $this->categoryRepository = $categoryRepository;
        $this->productCategoryRepository = $productCategoryRepository;
    }

    /**
     * Product rank management
     *
     * @param null $category_id
     *
     * @return mixed
     *
     * @throws EntityNotFoundException
     *
     * @Route("/%eccube_admin_route%/product/product_rank", name="admin_product_product_rank")
     * @Route("/%eccube_admin_route%/product/product_rank/{category_id}",
     *     name="admin_product_product_rank_show",
     *     requirements={"category_id":"\d+"}
     * )
     * @Template("@ProductRank/admin/product_rank.twig")
     */
    public function index($category_id = null)
    {
        if ($category_id) {
            // カテゴリが選択されている場合は親になるカテゴリを取得しておく
            $Parent = $this->categoryRepository->find($category_id);
            if (!$Parent) {
                throw new EntityNotFoundException();
            }
        } else {
            $Parent = null;
        }

        $TargetCategory = new Category();
        $TargetCategory->setParent($Parent);
        if ($Parent) {
            $TargetCategory->setHierarchy($Parent->getHierarchy() + 1);
        } else {
            $TargetCategory->setHierarchy(1);
        }

        $Children = $this->categoryRepository->getList(null);
        $ProductCategories = $this->productRankRepository->findBySearchData($Parent);

        $TopCategories = $this->categoryRepository->findBy(['Parent' => null], ['sort_no' => 'DESC']);
        $category_count = $this->categoryRepository->getTotalCount();

        return [
            'Children' => $Children,
            'Parent' => $Parent,
            'ProductCategories' => $ProductCategories,
            'TopCategories' => $TopCategories,
            'TargetCategory' => $TargetCategory,
            'category_count' => $category_count,
            'category_id' => $category_id,
        ];
    }

    /**
     * Increase rank of product
     *
     * @param $category_id
     * @param $product_id
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @throws EntityNotFoundException
     * @throws \Doctrine\DBAL\ConnectionException
     *
     * @Method("PUT")
     * @Route("/%eccube_admin_route%/product/product_rank/{category_id}/{product_id}/up",
     *      name="admin_product_product_rank_up",
     *      requirements={"category_id":"\d+", "product_id":"\d+"}
     * )
     */
    public function up($category_id, $product_id)
    {
        $this->isTokenValid();

        /* @var $TargetProductCategory ProductCategory */
        $TargetProductCategory = $this->productCategoryRepository->findOneBy([
            'category_id' => $category_id,
            'product_id' => $product_id,
        ]);
        if (!$TargetProductCategory) {
            throw new EntityNotFoundException();
        }

        $status = $this->productRankRepository->renumber($TargetProductCategory->getCategory());

        if ($status === true) {
            $status = $this->productRankRepository->up($TargetProductCategory);
        }

        if ($status === true) {
            $this->addSuccess('product_rank.admin.up.complete', 'admin');
        } else {
            $this->addError('product_rank.admin.up.error', 'admin');
        }

        return $this->redirectToRoute('admin_product_product_rank_show', ['category_id' => $category_id]);
    }

    /**
     * Increase rank of product
     *
     * @param $category_id
     * @param $product_id
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @throws EntityNotFoundException
     * @throws \Doctrine\DBAL\ConnectionException
     *
     * @Method("PUT")
     * @Route("/%eccube_admin_route%/product/product_rank/{category_id}/{product_id}/down",
     *      name="admin_product_product_rank_down",
     *      requirements={"category_id":"\d+", "product_id":"\d+"}
     * )
     */
    public function down($category_id, $product_id)
    {
        $this->isTokenValid();

        /* @var $TargetProductCategory ProductCategory */
        $TargetProductCategory = $this->productCategoryRepository->findOneBy([
            'category_id' => $category_id,
            'product_id' => $product_id,
        ]);
        if (!$TargetProductCategory) {
            throw new EntityNotFoundException();
        }

        $status = $this->productRankRepository->renumber($TargetProductCategory->getCategory());

        if ($status === true) {
            $status = $this->productRankRepository->down($TargetProductCategory);
        }

        if ($status === true) {
            $this->addSuccess('product_rank.admin.down.complete', 'admin');
        } else {
            $this->addError('product_rank.admin.down.error', 'admin');
        }

        return $this->redirectToRoute('admin_product_product_rank_show', ['category_id' => $category_id]);
    }

    /**
     * Moving rank by ajax
     *
     * @param Request $request
     *
     * @return bool|RedirectResponse|JsonResponse
     *
     * @throws EntityNotFoundException
     * @throws \Doctrine\DBAL\ConnectionException
     *
     * @Method("POST")
     * @Route("/%eccube_admin_route%/product/product_rank/moveRank",
     *      name="admin_product_product_rank_move_rank",
     *      requirements={"category_id":"\d+", "product_id":"\d+", "position":"\d+"}
     * )
     */
    public function moveRank(Request $request)
    {
        $category_id = intval($request->get('category_id'));
        $product_id = intval($request->get('product_id'));
        $position = intval($request->get('position'));

        /** @var Category $Category */
        $Category = $this->categoryRepository->find($category_id);
        $status = $this->productRankRepository->renumber($Category);

        /** @var ProductCategory $TargetProductCategory */
        $TargetProductCategory = $this->productCategoryRepository->findOneBy([
            'category_id' => $category_id,
            'product_id' => $product_id,
        ]);
        if (!$TargetProductCategory) {
            throw new EntityNotFoundException();
        }
        if ($status === true) {
            $status = $this->productRankRepository->moveRank($TargetProductCategory, $position);
        }

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse($status);
        } else {
            if ($status === true) {
                $this->addSuccess('product_rank.admin.move_rank.complete', 'admin');
            } else {
                $this->addError('product_rank.admin.move_rank.error', 'admin');
            }

            return $this->redirectToRoute('admin_product_product_rank_show', ['category_id' => $category_id]);
        }
    }
}
