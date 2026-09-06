<?php

/**
 * Subject (Observable) in the Observer pattern.
 * Holds a list of observers and broadcasts ad status changes to all of them,
 * so the AdModel never needs to know how each notification is delivered.
 */
final class AdStatusSubject
{
    /** @var AdStatusObserver[] */
    private array $observers = [];

    public function attach(AdStatusObserver $observer): void
    {
        $this->observers[] = $observer;
    }

    public function notify(int $adId, int $sellerId, string $status, string $title, string $detail = ''): void
    {
        foreach ($this->observers as $observer) {
            $observer->onStatusChanged($adId, $sellerId, $status, $title, $detail);
        }
    }
}
