# Notification Workflow for an App that sends Notifications

## Example story

Let's assume the following example scenario. Our app is the files_sharing app. We want
to notify the user when a remote share has to be accepted/declined. If the user has dealt
with it, we want to remove the notification again.

### Creating a new Notification

  1. Grab a new notification object (`\OCP\Notification\INotification`) from the manager
  (`\OCP\Notification\IManager`):
```php
$manager = \OC::$server->get(\OCP\Notification\IManager::class);
$notification = $manager->createNotification();
```

  2. Set the necessary information for the notification:
```php
$acceptAction = $notification->createAction();
$acceptAction->setLabel('accept')
    ->setLink('remote_shares', 'POST');

$declineAction = $notification->createAction();
$declineAction->setLabel('decline')
    ->setLink('remote_shares', 'DELETE');

$notification->setApp('files_sharing')
    ->setUser('recipient1')
    ->setDateTime(new \DateTime())
    ->setObject('remote', '1337') // $type and $id
    ->setSubject('remote_share', ['name' => '/fancyFolder']) // $subject and $parameters
    ->addAction($acceptAction)
    ->addAction($declineAction)
;
```
  Setting **app, user, timestamp, object and subject** are mandatory. You should not use a
  translated subject, message or action label. Use something like a "language key", to avoid
  length problems with translations in the storage of a notification app. Translation is done
  via invocation of your notifier by the manager when the notification is prepared for display.

  You should also try to avoid setting links and image paths here already, use keys again instead.
  This allows you to change the image/url of your application and also the admin can move the instance
  to another domain later, without breaking pending notifications. Also make sure, all your URLs are
  absolute URLs, so the notification icon and link also work from the desktop and mobile clients.

  3. Send the notification back to the manager:
```php
$manager->notify($notification);
```

### Preparing a notification for display

  1. In `app.php` register your Notifier (`\OCP\Notification\INotifier`) interface to the manager,
  using a `\Closure` returning the Notifier and a `\Closure` returning an array of the id and name:
```php
$manager = \OC::$server->get(\OCP\Notification\IManager::class);
$manager->registerNotifierService(\OCA\Files_Sharing\Notification\Notifier::class);
```

  2. The manager will execute the closure and then call the `prepare()` method on your notifier.
  If the notification is not known by your app, just throw an `\InvalidArgumentException`,
  but if it is actually from your app, you must set the parsed subject, message and action labels:
```php

class Notifier implements \OCP\Notification\INotifier {
	protected $factory;
	protected $url;

	public function __construct(\OCP\L10N\IFactory $factory,
								\OCP\IURLGenerator $urlGenerator) {
		$this->factory = $factory;
		$this->url = $urlGenerator;
	}

	/**
	 * Identifier of the notifier, only use [a-z0-9_]
	 * @return string
	 */
	public function getID(): string {
		return 'files_sharing';
	}

	/**
	 * Human readable name describing the notifier
	 * @return string
	 */
	public function getName(): string {
		return $this->factory->get('files_sharing')->t('File sharing');
	}

	/**
	 * @param INotification $notification
	 * @param string $languageCode The code of the language that should be used to prepare the notification
	 */
	public function prepare(INotification $notification, string $languageCode): INotification {
		if ($notification->getApp() !== 'files_sharing') {
			// Not my app => throw
			throw new \InvalidArgumentException();
		}

		// Read the language from the notification
		$l = $this->factory->get('files_sharing', $languageCode);

		switch ($notification->getSubject()) {
			// Deal with known subjects
			case 'remote_share':
				try {
					$this->shareManager->getShareById($notification->getObjectId(), $notification->getUser());
				} catch (ShareNotFound $e) {
					// Throw AlreadyProcessedException exception when the notification has already been solved and can be removed.
					throw new \OCP\Notification\AlreadyProcessedException();
				}

				$notification->setIcon($this->url->getAbsoluteURL($this->url->imagePath('core', 'actions/share.svg')))
					->setLink($this->url->linkToRouteAbsolute('files_sharing.RemoteShare.overview', ['id' => $notification->getObjectId()]));

				// Set rich subject, see https://github.com/nextcloud/server/issues/1706 for more information
				// and https://github.com/nextcloud/server/blob/master/lib/public/RichObjectStrings/Definitions.php
				// for a list of defined objects and their parameters.
				$parameters = $notification->getSubjectParameters();
				$notification->setRichSubject($l->t('You received the remote share "{share}"'), [
					'share' => [
						'type' => 'pending-federated-share',
						'id' => $notification->getObjectId(),
						'name' => $parameters['name'],
					]
				]);

				// Deal with the actions for a known subject
				foreach ($notification->getActions() as $action) {
					switch ($action->getLabel()) {
						case 'accept':
							$action->setParsedLabel($l->t('Accept'))
								->setLink($this->url->linkToRouteAbsolute('files_sharing.RemoteShare.accept', ['id' => $notification->getObjectId()]), 'POST');
							break;

						case 'decline':
							$action->setParsedLabel($l->t('Decline'))
								->setLink($this->url->linkToRouteAbsolute('files_sharing.RemoteShare.decline', ['id' => $notification->getObjectId()]), 'DELETE');
							break;
					}

					$notification->addParsedAction($action);
				}

				// Set the plain text subject automatically
				$this->setParsedSubjectFromRichSubject($notification);
				return $notification;

			default:
				// Unknown subject => Unknown notification => throw
				throw new \InvalidArgumentException();
		}
	}

	// This is a little helper function which automatically sets the simple parsed subject
	// based on the rich subject you set.
	protected function setParsedSubjectFromRichSubject(INotification $notification) {
		$placeholders = $replacements = [];
		foreach ($notification->getRichSubjectParameters() as $placeholder => $parameter) {
			$placeholders[] = '{' . $placeholder . '}';
			if ($parameter['type'] === 'file') {
				$replacements[] = $parameter['path'];
			} else {
				$replacements[] = $parameter['name'];
			}
		}

		$notification->setParsedSubject(str_replace($placeholders, $replacements, $notification->getRichSubject()));
	}
}
```

### Marking a notification as read/deleted/processed/obsoleted

If the user accepted the share or the share was removed/unshared, we want to remove
the notification, because no user action is needed anymore. To do this, we simply have to
call the `markProcessed()` method on the manager with the necessary information on a
notification object:

```php
$manager = \OC::$server->get(\OCP\Notification\IManager::class);
$notification->setApp('files_sharing')
    ->setObject('remote', 1337)
    ->setUser('recipient1');
$manager->markProcessed($notification);
```

Only the app name is mandatory: so if you don't set the user, the notification
will be marked as processed for all users that have it. So the following example will
remove all notifications for the app files_sharing on the object "remote #1337":

```php
$manager = \OC::$server->get(\OCP\Notification\IManager::class);
$notification->setApp('files_sharing')
    ->setObject('remote', 1337);
$manager->markProcessed($notification);
```

### Defer and flush

Sometimes you might send multiple notifications in one request.
In that case it makes sense to defer the sending, so in the end only one connection
is done to the push server instead of 1 per notification.
```php
$manager = \OC::$server->get(\OCP\Notification\IManager::class);
$shouldFlush = $manager->defer();

// Your application code generating notifications …

if ($shouldFlush) {
	// Only flush when defer() returned true, otherwise another app is already deferring
	$manager->flush();
}
```
