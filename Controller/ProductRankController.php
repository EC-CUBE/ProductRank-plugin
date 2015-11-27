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

namespace Plugin\ProductRank\Controller;

use Doctrine\ORM\QueryBuilder;
use Eccube\Entity\Category;
use Plugin\ProductRank\Form\Type\ProductRankType;
use Eccube\Application;
use Eccube\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception as HttpException;

class ProductRankController extends AbstractController
{
    private $main_title;
    private $sub_title;

    public function __construct()
    {
    }

    public function index(Application $app, Request $request, $category_id = null)
    {
        if ($category_id) {
            // カテゴリが選択されている場合は親になるカテゴリを取得しておく
            $Parent = $app['eccube.repository.category']->find($category_id);
            if (!$Parent) {
                throw new NotFoundHttpException();
            }
        } else {
            $Parent = null;
        }

        $TargetCategory = new \Eccube\Entity\Category();
        $TargetCategory->setParent($Parent);
        if ($Parent) {
            $TargetCategory->setLevel($Parent->getLevel() + 1);
        } else {
            $TargetCategory->setLevel(1);
        }

        $Children = $app['eccube.repository.category']->getList(null);
        $ProductCategorys = $app['eccube.plugin.product_rank.repository.product_rank']
            ->findBySearchData($Parent);

        $TopCategories = $app['eccube.repository.category']->findBy(array('Parent' => null), array('rank' => 'DESC'));
        $category_count = $app['eccube.repository.category']->getTotalCount();

        return $app->render('ProductRank/Resource/template/admin//product_rank.twig', array(
            'Children' => $Children,
            'Parent' => $Parent,
            'ProductCategorys' => $ProductCategorys,
            'TopCategories' => $TopCategories,
            'TargetCategory' => $TargetCategory,
            'category_count' => $category_count,
        ));
    }

    public function up(Application $app, Request $request, $category_id, $product_id)
    {
        $this->isTokenValid($app);

        $em = $app['orm.em'];
        $TargetProductCategory = $em->getRepository('\Eccube\Entity\ProductCategory')
            ->findOneBy(array('category_id' => $category_id, 'product_id' => $product_id));
        if (!$TargetProductCategory) {
            throw new NotFoundHttpException();
        }

        $status = $app['eccube.plugin.product_rank.repository.product_rank']
            ->up($TargetProductCategory);

        if ($status === true) {
            $app->addSuccess('admin.product_rank.up.complete', 'admin');
        } else {
            $app->addError('admin.product_rank.up.error', 'admin');
        }

        return $app->redirect($app->url('admin_product_product_rank_show', array('category_id' => $category_id)));
    }

    public function down(Application $app, Request $request, $category_id, $product_id)
    {
        $this->isTokenValid($app);

        $em = $app['orm.em'];
        $repos = $em->getRepository('\Eccube\Entity\ProductCategory');

        $TargetProductCategory = $repos->findOneBy(array('category_id' => $category_id, 'product_id' => $product_id));
        if (!$TargetProductCategory) {
            throw new NotFoundHttpException();
        }

        $status = $app['eccube.plugin.product_rank.repository.product_rank']
            ->down($TargetProductCategory);

        if ($status === true) {
            $app->addSuccess('admin.product_rank.down.complete', 'admin');
        } else {
            $app->addError('admin.product_rank.down.error', 'admin');
        }

        return $app->redirect($app->url('admin_product_product_rank_show', array('category_id' => $category_id)));
    }

    public function moveRank(Application $app, Request $request)
    {
        $pos = $request->get("new_rank");
        $category_id = $request->get("category_id");
        if (ctype_digit(strval($pos))) {
            $product_id = $request->get("product_id");

            $status = $app['eccube.plugin.product_rank.repository.product_rank']
                ->moveRank($category_id, $product_id, $pos);

            if ($status === true) {
            } else {
                $app->addError('admin.product_rank.move_rank.error', 'admin');
            }
        }

        return $app->redirect($app->url('admin_product_product_rank_show', array('category_id' => $category_id)));
    }

}
