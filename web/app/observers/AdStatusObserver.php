<?php

/**
 * Observer interface (Gang of Four - Behavioral).
 * Any class that wants to react to an ad status change implements this.
 */
interface AdStatusObserver
{
    /**
     * @param int    $adId      The ad whose status changed.
     * @param int    $sellerId  The owner of the ad.
     * @param string $status    The new status (approved, rejected, sold, ...).
     * @param string $title     The ad title, for message text.
     * @param string $detail    Optional extra detail (e.g. AI summary).
     */
    public function onStatusChanged(int $adId, int $sellerId, string $status, string $title, string $detail = ''): void;
}
