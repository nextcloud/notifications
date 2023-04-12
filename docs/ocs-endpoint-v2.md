# Reading and deleting notifications as a Client v2

## New in APIv2

* :new: Support for icons was added with capability-entry `icons`
* :new: Support for [Rich Object Strings](https://github.com/nextcloud/server/issues/1706) was added with capability-entry `rich-strings`
* :new: ETag/If-None-Match are now evaluated and respond with `304 Not Modified` and empty body when unchanged.

## Checking the capabilities of the server

In order to find out if notifications is installed/enabled on the server you can run a request against the capabilities endpoint: `/ocs/v2.php/cloud/capabilities`

```json
{
  "ocs": {
    ...
    "data": {
      ...
      "capabilities": {
        ...
        "notifications": {
          "ocs-endpoints": [
            "list",
            "get",
            "delete",
            "delete-all",
            "icons",
            "rich-strings",
            "action-web",
            "user-status"
          ]
        }
      }
    }
  }
}
```


## Getting the notifications of a user

The user needs to be identified/logged in by the server. Then you can just run a simple GET request against `/ocs/v2.php/apps/notifications/api/v2/notifications` to grab a list of notifications:

```json
{
  "ocs": {
    "meta": {
      "status": "ok",
      "statuscode": 200,
      "message": null
    },
    "data": [
      {
        "notification_id": 61,
        "app": "files_sharing",
        "user": "admin",
        "datetime": "2004-02-12T15:19:21+00:00",
        "object_type": "remote_share",
        "object_id": "13",
        "subject": "You received admin@localhost as a remote share from test",
        "subjectRich": "You received {share} as a remote share from {user}",
        "subjectRichParameters": {
          "share": {
            "type": "pending-federated-share",
            "id": "1",
            "name": "test"
          },
          "user": {
            "type": "user",
            "id": "test1",
            "name": "User One",
            "server": "http:\/\/nextcloud11.local"
          }
        },
        "message": "",
        "messageRich": "",
        "messageRichParameters": [],
        "link": "http://localhost/index.php/apps/files_sharing/pending",
        "icon": "http://localhost/img/icon.svg",
        "shouldNotify": true,
        "actions": [
          {
            "label": "Accept",
            "link": "http:\/\/localhost\/ocs\/v1.php\/apps\/files_sharing\/api\/v1\/remote_shares\/13",
            "type": "POST",
            "primary": true
          },
          {
            "label": "Decline",
            "link": "http:\/\/localhost\/ocs\/v1.php\/apps\/files_sharing\/api\/v1\/remote_shares\/13",
            "type": "DELETE",
            "primary": false
          }
        ]
      }
    ]
  }
}
```

### Response codes

Status | Explanation
---|---
`204 No Content` | please slow down the polling to once per hour, since there are no apps that can generate notifications
`304 Not Modified` | The provided `If-None-Match` matches the ETag, response body is empty

### Headers

Status | Explanation
---|---
`ETag` | See https://tools.ietf.org/html/rfc7232#section-2.3
`X-Nextcloud-User-Status` | Only available with the `user-status` capability. The user status (`away`, `dnd`, `offline`, `online`) should be taken into account and in case of `dnd` no notifications should be directly shown.

### Specification

Optional elements are still set in the array, the value is just empty:

Type | Empty value
---- | -----------
string | `""`
array | `[]`

#### Notification Element

Field name | Type | Since | Value description
---------- | ---- | ----- | -----------------
notification_id | int | v1 | Unique identifier of the notification, can be used to dismiss a notification
app | string | v1 | Name of the app that triggered the notification
user | string | v1 | User id of the user that receives the notification
datetime | string | v1 | ISO 8601 date and time when the notification was published
object_type | string | v1 | Type of the object the notification is about, that can be used in php to mark a notification as resolved
object_id | string | v1 | ID of the object the notification is about, that can be used in php to mark a notification as resolved
subject | string | v1 | Translated short subject that should be presented to the user
subjectRich | string | v2 :new: | (Optional) Translated subject string with placeholders (see [Rich Object String](https://github.com/nextcloud/server/issues/1706))
subjectRichParameters | array | v2 :new: | (Optional) Subject parameters for `subjectRich` (see [Rich Object String](https://github.com/nextcloud/server/issues/1706))
message | string | v1 | (Optional) Translated potentially longer message that should be presented to the user
messageRich | string | v2 :new: | (Optional) Translated message string with placeholders (see [Rich Object String](https://github.com/nextcloud/server/issues/1706))
messageRichParameters | array | v2 :new: | (Optional) Message parameters for `messageRich` (see [Rich Object String](https://github.com/nextcloud/server/issues/1706))
link | string | v1 | (Optional) A link that should be followed when the subject/message is clicked
icon | string | v2 :new: | (Optional) A link to an icon that should be shown next to the notification.
actions | array | v1 | (Optional) An array of action elements


#### Action Element

Field name | Type | Value description
---------- | ---- | -----------------
label | string | Translated short label of the action/button that should be presented to the user
link | string | A link that should be followed when the action is performed/clicked
type | string | HTTP method that should be used for the request against the link: GET, POST, DELETE, PUT or WEB. In case of WEB a redirect should happen instead.
primary | bool | If the action is the primary action for the notification or not


## Get a single notification for a user

In order to get a single notification, you can send a GET request against `/ocs/v2.php/apps/notifications/api/v2/notifications/{id}`


## Deleting a notification for a user

In order to delete a notification, you can send a DELETE request against `/ocs/v2.php/apps/notifications/api/v2/notifications/{id}`



## Deleting all notifications for a user

In order to delete all notifications, you can send a DELETE request against `/ocs/v2.php/apps/notifications/api/v2/notifications`

**Note:** This endpoint was added for Nextcloud 14, so check for the `delete-all` capability first.


## Check existance of notifications for a user

In order to check whether a set of notification ids (max. 200 items per request) still exist for a user,
a client can send a POST request against `/ocs/v2.php/apps/notifications/api/v2/notifications/exists` with
the integer list provided as `ids` field on the POST body.

**Note:** This endpoint was added for Nextcloud 27, so check for the `exists` capability first.
