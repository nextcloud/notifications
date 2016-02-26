# Notification Workflow for an App that sends Notifications

## Example story

Let's assume the following example scenario. Our app is the files_sharing app. We want
to notify the user when a remote share has to be accepted/declined. If the user has dealt
with it, we want to remove the notification again.

### Creating a new Notification

  1. Grab a new notification object (`\OCP\Notification\INotification`) from the manager
  (`\OCP\Notification\IManager`):
    ```php
    $manager = \OC::$server->getNotificationManager();
    $notification = $manager->createNotification();
    ```

  2. Set the necessary information for the notification:
    ```php
    $acceptAction = $notification->createAction();
    $acceptAction->setLabel('accept')
        ->setLink('/apps/files_sharing/api/v1/remote_shares/1337', 'POST');

    $declineAction = $notification->createAction();
    $declineAction->setLabel('decline')
        ->setLink('/apps/files_sharing/api/v1/remote_shares/1337', 'DELETE');

    $notification->setApp('files_sharing')
        ->setUser('recipient1')
        ->setDateTime(new DateTime())
        ->setObject('remote', '1337') // $type and $id
        ->setSubject('remote_share', ['/fancyFolder']) // $subject and $parameters
        ->addAction($acceptAction)
        ->addAction($declineAction)
    ;
    ```
  Setting **app, user, timestamp, object and subject** are mandatory. You should not use a
  translated subject, message or action label. Use something like a "language key", to avoid
  length problems with translations in the storage of a notification app. Translation is done
  via invocation of your notifier by the manager when the notification is prepared for display.

  3. Send the notification back to the manager:
    ```php
    $manager->notify($notification);
    ```

### Preparing a notification for display

  1. In `app.php` register your Notifier (`\OCP\Notification\INotifier`) interface to the manager,
  using a `\Closure`:
    ```php
    $manager = \OC::$server->getNotificationManager();
    $manager->registerNotifier(function() {
        return new \OCA\Files_Sharing\Notifier(
            \OC::$server->getL10NFactory()
        );
    });
    ```

  2. The manager will execute the closure and then call the `prepare()` method on your notifier.
  If the notification is not known by your app, just throw an `\InvalidArgumentException`,
  but if it is actually from your app, you must set the parsed subject, message and action labels:
    ```php
    protected $factory;

    public function __construct(\OCP\L10N\IFactory $factory) {
        $this->factory = $factory;
    }

    /**
     * @param INotification $notification
     * @param string $languageCode The code of the language that should be used to prepare the notification
     */
    public function prepare(INotification $notification, $languageCode) {
        if ($notification->getApp() !== 'files_sharing') {
            // Not my app => throw
            throw new \InvalidArgumentException();
        }

        // Read the language from the notification
        $l = $this->factory->get('myapp', $languageCode);

        switch ($notification->getSubject()) {
            // Deal with known subjects
            case 'remote_share':
                $notification->setParsedSubject(
                    (string) $l->t('You received the remote share "%s"', $notification->getSubjectParameters())
                );
                
                // Deal with the actions for a known subject
                foreach ($notification->getActions() as $action) {
                    switch ($action->getLabel()) {
                        case 'accept':
                            $action->setParsedLabel(
                                (string) $l->t('Accept')
                            );
                        break;
 
                        case 'decline':
                            $action->setParsedLabel(
                                (string) $l->t('Decline')
                            );
                        break;
                    }

                    $notification->addParsedAction($action);
                }
                return $notification;
            break;

            default:
                // Unknown subject => Unknown notification => throw
                throw new \InvalidArgumentException();
        }
    }
    ```

    **Note:** currently no markup is allowed.

### Marking a notification as read/deleted/processed/obsoleted

If the user accepted the share or the share was removed/unshared, we want to remove
the notification, because no user action is needed anymore. To do this, we simply have to
call the `markProcessed()` method on the manager with the neccessary information on a
notification object:

```php
$manager = \OC::$server->getNotificationManager();
$notification->setApp('files_sharing')
    ->setObject('remote', 1337)
    ->setUser('recipient1');
$manager->markProcessed($notification);
```

Only the app name is mandatory: so if you don't set the user, the notification
will be marked as processed for all users that have it. So the following example will
remove all notifications for the app files_sharing on the object "remote #1337":

```php
$manager = \OC::$server->getNotificationManager();
$notification->setApp('files_sharing')
    ->setObject('remote', 1337);
$manager->markProcessed($notification);
```

