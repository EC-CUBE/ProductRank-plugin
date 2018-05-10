<?php
namespace Plugin\ProductRank\Event;

use Eccube\Event\EventArgs;
use Eccube\Repository\Master\ProductListOrderByRepository;
use Eccube\Common\EccubeConfig;
use Eccube\Entity\Master\ProductListOrderBy;
use Knp\Component\Pager\Event\ItemsEvent;
use Doctrine\ORM\QueryBuilder;

class Event
{
    /**
     * @var QueryBuilder
     */
    protected $queryBuilder;

    /**
     * @var ProductListOrderByRepository
     */
    protected $productOrderByRepository;

    /**
     * @var EccubeConfig
     */
    protected $eccubeConfig;

    /**
     * Event constructor.
     *
     * @param EccubeConfig $eccubeConfig
     * @param ProductListOrderByRepository $productOrderByRepository
     */
    public function __construct(
        EccubeConfig $eccubeConfig,
        ProductListOrderByRepository $productOrderByRepository
    ) {
        $this->eccubeConfig = $eccubeConfig;
        $this->productOrderByRepository = $productOrderByRepository;
    }

    /**
     * Modify query builder order by sort_no
     *
     * @param EventArgs $event
     */
    public function onFrontProductIndexSearch(EventArgs $event)
    {
        /** @var \Doctrine\ORM\QueryBuilder $qb */
        $qb = $event->getArgument('qb');
        $searchData = $event->getArgument('searchData');

        if (!isset($searchData['orderby']) || !$searchData['orderby'] instanceof ProductListOrderBy) {
            return;
        }
        /** @var ProductListOrderBy $OrderBy */
        $OrderBy = $searchData['orderby'];
        if ($OrderBy->getId() != $this->eccubeConfig->get('plugin.product_rank.product_list_order_id')) {
            return;
        }

        $orderByParts = $qb->getDQLPart('orderBy');
        $qb->orderBy('pct.sort_no', 'DESC');
        foreach ($orderByParts as $orderByPart) {
            $qb->addOrderBy($orderByPart);
        }

        $this->queryBuilder = $qb;
    }

    /**
     * Need to add wrap-queries into pagination
     * @see https://github.com/KnpLabs/knp-components/blob/master/doc/pager/config.md
     *
     * @param ItemsEvent $event
     */
    public function onPaginationIterator(ItemsEvent $event)
    {
        if (!$this->queryBuilder instanceof QueryBuilder) {
            return;
        }

        if (!$event->target instanceof QueryBuilder) {
            return;
        }

        if ($this->queryBuilder->getDQL() !== $event->target->getDQL()) {
            return;
        }

        $event->options['wrap-queries'] = true;
    }
}