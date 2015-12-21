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

namespace Plugin\ProductRank;

use Eccube\Application;
use Eccube\Entity\Master\ProductListOrderBy;
use Eccube\Plugin\AbstractPluginManager;

class PluginManager extends AbstractPluginManager
{

    public function __construct()
    {
    }

    public function install($config, $app)
    {
    }

    public function uninstall($config, $app)
    {
        $this->removeProductListOrderBy($app);
    }

    public function enable($config, $app)
    {
        $this->addProductListOrderBy($app);
    }

    public function disable($config, $app)
    {
        $this->removeProductListOrderBy($app);
    }

    public function update($config, $app)
    {

    }

    /**
     * @param Application $app
     */
    private function addProductListOrderBy(Application $app) {
        // this up() migration is auto-generated, please modify it to your needs

        /** @var \Eccube\Entity\Master\ProductListOrderBy $plob */
        $ProductListOrderBy = new ProductListOrderBy();
        $ProductListOrderBy->setId(0);
        $ProductListOrderBy->setName('未選択');
        $ProductListOrderBy->setRank(-1);

        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $app['orm.em'];
        $em->persist($ProductListOrderBy);
        $em->flush();
    }

    /**
     * @param Application $app
     */
    private function removeProductListOrderBy(Application $app) {
        // this down() migration is auto-generated, please modify it to your needs

        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $app['orm.em'];
        $repos = $em->getRepository('Eccube\Entity\Master\ProductListOrderBy');
        $ProductListOrderBy = $repos->createQueryBuilder('plob')
            ->where('plob.id = :id')
            ->getQuery()
            ->setParameters(array(
                'id' => 0,
            ))
            ->getSingleResult();

        if ($ProductListOrderBy) {
            $em->remove($ProductListOrderBy);
            $em->flush();
        }
    }

}
