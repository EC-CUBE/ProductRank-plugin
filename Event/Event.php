<?php
namespace Plugin\ProductRank\Event;

use Knp\Component\Pager\Event\ItemsEvent;
use Doctrine\ORM\QueryBuilder;

class Event
{
    /**
     * Need to add wrap-queries into pagination
     * @see https://github.com/KnpLabs/knp-components/blob/master/doc/pager/config.md
     *
     * @param ItemsEvent $event
     */
    public function onPaginationIterator(ItemsEvent $event)
    {
        if (!$event->target instanceof QueryBuilder) {
            return;
        }

        /** @var \Doctrine\ORM\Query\Expr\OrderBy[] $orderByParts */
        $orderByParts = $event->target->getDQLPart('orderBy');
        foreach ($orderByParts as $orderBy) {
            if (in_array('pct.sort_no DESC', $orderBy->getParts())) {
                $event->options['wrap-queries'] = true;
                return;
            }
        }
    }
}