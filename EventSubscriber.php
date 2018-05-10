<?php
namespace Plugin\ProductRank;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Eccube\Event\EccubeEvents;
use Eccube\Event\EventArgs;
use Plugin\ProductRank\Event\Event;
use Knp\Component\Pager\Event\ItemsEvent;

class EventSubscriber implements EventSubscriberInterface
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
            EccubeEvents::FRONT_PRODUCT_INDEX_SEARCH => ['onFrontProductIndexSearch', 10],
            'knp_pager.items' => ['onPaginationIterator', 10]
        ];
    }

    /**
     * Modify query builder order by sort_no
     *
     * @param EventArgs $event
     */
    public function onFrontProductIndexSearch(EventArgs $event)
    {
        try {
            $this->event->onFrontProductIndexSearch($event);
        } catch (\Exception $e) {
            log_error('ProductRank Plugin', [$e]);
        }
    }

    /**
     * Modify options of pagination
     *
     * @param EventArgs $event
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
