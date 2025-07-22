<?php

namespace App\Event;

use App\Controller\Admin\EasyAdminCrudTrait;
use App\Entity\InformationProduct;
use App\Message\InformationProductFiler;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterEntityPersistedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class EasyAdminPersistSubscriber implements EventSubscriberInterface
{
    use EasyAdminCrudTrait;

    public function __construct(private MessageBusInterface $messageBus)
    {
    }

    public function onAfterEntityPersistedEvent(AfterEntityPersistedEvent $event): void
    {
        $informationProduct = $event->getEntityInstance();

        if (!$informationProduct instanceof InformationProduct) {
            return;
        }

        // Dispatch a message to process the information product after it has been persisted
        $this->messageBus->dispatch(new InformationProductFiler($informationProduct->getId()));
    }

    public static function getSubscribedEvents(): array
    {
        return [
            AfterEntityPersistedEvent::class => 'onAfterEntityPersistedEvent',
        ];
    }
}
