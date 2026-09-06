<?php

/**
 * Concrete Observer: turns an ad status change into an in-app notification.
 */
final class NotificationObserver implements AdStatusObserver
{
    private NotificationModel $notifications;

    public function __construct()
    {
        $this->notifications = new NotificationModel();
    }

    public function onStatusChanged(int $adId, int $sellerId, string $status, string $title, string $detail = ''): void
    {
        switch ($status) {
            case 'approved':
                $t = 'Ad Approved';
                $m = 'Your ad "' . $title . '" passed the AI fair price check and is now live.';
                break;
            case 'rejected':
                $t = 'Ad Rejected';
                $m = 'Your ad "' . $title . '" was rejected because the price is outside the fair market range. ' . $detail;
                break;
            case 'sold':
                $t = 'Ad Marked Sold';
                $m = 'Your ad "' . $title . '" has been marked as sold.';
                break;
            default:
                $t = 'Ad Updated';
                $m = 'Your ad "' . $title . '" status changed to ' . $status . '.';
        }
        $this->notifications->push($sellerId, $t, trim($m), 'ad', $adId);
    }
}
