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

namespace Plugin\ProductRank\Event;

use Knp\Component\Pager\Event\ItemsEvent;
use Doctrine\ORM\QueryBuilder;

class Event
{
    /**
     * Need to add wrap-queries into pagination
     *
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
            if (in_array('pct.product_rank_sort_no DESC', $orderBy->getParts())) {
                $event->options['wrap-queries'] = true;

                return;
            }
        }
    }
}
