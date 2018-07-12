<?php
namespace Plugin\ProductRank;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Plugin\ProductRank\Event\Event;
use Knp\Component\Pager\Event\ItemsEvent;

class ProductRankEvent implements EventSubscriberInterface
{
    /**
     * @var Event
     */
    protected $event;

    /**
     * EventSubscriber constructor.
     *
     * @param Event $event
     */
    public function __construct(
        Event $event
    ) {
        $this->event = $event;
    }

    /**
     * {@inheritdoc}
     *
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            'knp_pager.items' => ['onPaginationIterator', 10]
        ];
    }


    /**
     * Modify options of pagination
     *
     * @param ItemsEvent $event
     */
    public function onPaginationIterator(ItemsEvent $event)
    {
        try {
            $this->event->onPaginationIterator($event);
        } catch (\Exception $e) {
            log_error('ProductRank Plugin', [$e]);
        }
    }
}
