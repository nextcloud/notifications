# Admin notifications

Allows admins to generate notifications for users via the console or an HTTP endpoint

## Console command

```
$ sudo -u www-data ./occ notification:generate \
  admin "Short message up to 255 characters" \
  -l "Optional: longer message with more details, up to 4000 characters"
```

### Help

```
$ sudo -u www-data ./occ notification:generate --help
Usage:
  notification:generate [options] [--] <user-id> <short-message>

Arguments:
  user-id                          User ID of the user to notify
  short-message                    Short message to be sent to the user (max. 255 characters)

Options:
  -l, --long-message=LONG-MESSAGE  Long mesage to be sent to the user (max. 4000 characters) [default: ""]

```

## HTTP request

*The URL had to be changed when upgrading from Nextcloud 20 to 21*

```
curl -H "OCS-APIREQUEST: true" -X POST \
  https://admin:admin@localhost/ocs/v2.php/apps/notifications/api/v2/admin_notifications/admin \
  -d "shortMessage=Short message up to 255 characters" \
  -d "longMessage=Optional: longer message with more details, up to 4000 characters"
```

### Help
```
curl -H "OCS-APIREQUEST: true" -X POST \
  https://<admin-user>:<admin-app-password-token>@<server-url>/ocs/v2.php/apps/notifications/api/v2/admin_notifications/<user-id> \
  -d "shortMessage=<short-message>" \
  -d "longMessage=<long-message>"
```

#### Placeholders

| Placeholder                  | Description                                                |
|------------------------------|------------------------------------------------------------|
| `<admin-user>`               | User ID of a user with admin privileges                    |
| `<admin-app-password-token>` | Password or an "app password" of the "admin-user"          |
| `<server-url>`               | URL with Webroot of your Nextcloud installation            |
| `<user-id>`                  | User ID of the user to notify                              |
| `<short-message>`            | Short message to be sent to the user (max. 255 characters) |
| `<long-message>`             | Long message to be sent to the user (max. 4000 characters) |

### Return codes

| Status | Description                                                |
|--------|------------------------------------------------------------|
| 200    | Notification was created successfully                      |
| 400    | Too long or empty `short-message`, too long `long-message` |
| 404    | Unknown user                                               |
| 500    | Unexpected server error                                    |
| 503    | Instance is in maintenance mode                            |

## Screenshot

Both the occ command and the HTTP request generate the same notification

![Admin notification triggered from console](https://raw.githubusercontent.com/nextcloud/notifications/master/docs/screenshot.png)
