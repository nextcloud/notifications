# Reading and deleting notifications as a Client

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
            "delete"
          ]
        }
      }
    }
  }
}
```


## Getting the notifications of a user

The user needs to be identified/logged in by the server. Then you can just run a simple GET request against `/ocs/v2.php/apps/notifications/api/v1/notifications` to grab a list of notifications:

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
        "message": "",
        "link": "http://localhost/index.php/apps/files_sharing/pending",
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

**Note:** If the HTTP status code is `204` (No content), you can slow down the polling to once per hour. This status code means that there is no app that can generate notifications.

### Specification

Optional elements are still set in the array, the value is just empty:

Type | Empty value
---- | -----------
string | `""`
array | `[]`

#### Notification Element

Field name | Type | Value description
---------- | ---- | -----------------
notification_id | int | Unique identifier of the notification, can be used to dismiss a notification
app | string | Name of the app that triggered the notification
user | string | User id of the user that receives the notification
datetime | string | ISO 8601 date and time when the notification was published
object_type | string | Type of the object the notification is about, that can be used in php to mark a notification as resolved
object_id | string | ID of the object the notification is about, that can be used in php to mark a notification as resolved
subject | string | Translated short subject that should be presented to the user
message | string | (Optional) Translated potentially longer message that should be presented to the user
link | string | (Optional) A link that should be followed when the subject/message is clicked
actions | array | (Optional) An array of action elements


#### Action Element

Field name | Type | Value description
---------- | ---- | -----------------
label | string | Translated short label of the action/button that should be presented to the user
link | string | A link that should be followed when the action is performed/clicked
type | string | HTTP method that should be used for the request against the link: GET, POST, DELETE
primary | bool | If the action is the primary action for the notification or not


## Get a single notification for a user

In order to get a single notification, you can send a GET request against `/ocs/v2.php/apps/notifications/api/v1/notifications/{id}`


## Deleting a notification for a user

In order to delete a notification, you can send a DELETE request against `/ocs/v2.php/apps/notifications/api/v1/notifications/{id}`

