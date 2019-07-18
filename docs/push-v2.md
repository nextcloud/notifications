# Push notifications as a Nextcloud client device



## Checking the capabilities of the Nextcloud server

In order to find out if notifications support push on the server you can run a request against the capabilities endpoint: `/ocs/v2.php/cloud/capabilities`

```
{
  "ocs": {
    ...
    "data": {
      ...
      "capabilities": {
        ...
        "notifications": {
          "push": [
            ...
            "devices",
            "object-data",
            "delete"
          ]
        }
      }
    }
  }
}
```



## Subscribing at the Nextcloud server

1. **Only on first registration on the server** The device generates a `rsa2048` key pair (`devicePrivateKey` and `devicePublicKey`).

2. The device generates the `PushToken` for *Apple Push Notification Service* (iOS) or *Firebase Cloud Messaging* (Android)

3. The device generates a `sha512` hash of the `PushToken` (`PushTokenHash`)

4. The device then sends the `devicePublicKey`, `PushTokenHash` and `proxyServerUrl` to the Nextcloud server:

   ```
   POST /ocs/v2.php/apps/notifications/api/v2/push

   {
     "pushTokenHash": "{{PushTokenHash}}",
     "devicePublicKey": "{{devicePublicKey}}",
     "proxyServer": "{{proxyServerUrl}}"
   }
   ```

   ​

### Response

The server replies with the following status codes:

| Status code | Meaning                                  |
| ----------- | ---------------------------------------- |
| 200         | No further action by the device required |
| 201         | Push token was created/updated and **needs to be sent to the `Proxy`** |
| 400         | Invalid device public key; device does not use a token to authenticate; the push token hash is invalid formatted; the proxy server URL is invalid; |
| 401         | Device is not logged in                  |



#### Body in case of success

In case of `200` and `201` the reply has more information in the body:

| Key              | Type         |                                          |
| ---------------- | ------------ | ---------------------------------------- |
| publicKey        | string (512) | rsa2048 public key of the user account on the instance |
| deviceIdentifier | string (128) | unique identifier encrypted with the users private key |
| signature        | string (512) | base64 encoded signature of the deviceIdentifier |



#### Body in case of an error

In case of `400` the following `message` can appear in the body:

| Error                    | Description                              |
| ------------------------ | ---------------------------------------- |
| `INVALID_PUSHTOKEN_HASH` | The hash of the push token was not a valid `sha512` hash. |
| `INVALID_SESSION_TOKEN`  | The authentication token of the request could not be identified. Check whether a password was used to login. |
| `INVALID_DEVICE_KEY`     | The device key does not match the one registered to the provided session token. |
| `INVALID_PROXY_SERVER`   | The proxy server was not a valid https URL. |



## Unsubcribing at the Nextcloud server

When an account is removed from a device, the device should unregister on the server. Otherwise the server sends unnecessary push notifications and might be blocked because of spam.



The device should then send a `DELETE` request to the Nextcloud server:

```
DELETE /ocs/v2.php/apps/notifications/api/v2/push
```



### Response

The server replies with the following status codes:

| Status code | Meaning                                  |
| ----------- | ---------------------------------------- |
| 200         | Push token was not registered on the server |
| 202         | Push token was deleted and **needs to be deleted from the `Proxy`** |
| 400         | Device does not use a token to authenticate |
| 401         | Device is not logged in                  |



#### Body in case of an error

In case of `400` the following `message` can appear in the body:

| Error                   | Description                              |
| ----------------------- | ---------------------------------------- |
| `INVALID_SESSION_TOKEN` | The authentication token of the request could not be identified. |



## Subscribing at the Push Proxy

The device sends the`PushToken` as well as the `deviceIdentifier`, `signature` and the user´s `publicKey`  (from the server´s response) to the Push Proxy:

```
POST /devices

{
  "pushToken": "{{PushToken}}",
  "deviceIdentifier": "{{deviceIdentifier}}",
  "deviceIdentifierSignature": "{{signature}}",
  "userPublicKey": "{{userPublicKey}}"
}
```



### Response

The server replies with the following status codes:

| Status code | Meaning                                  |
| ----------- | ---------------------------------------- |
| 200         | Push token was written to the databse    |
| 400         | Push token, public key or device identifier is malformed, the signature does not match |
| 403         | Device is not allowed to write the push token of the device identifier |
| 409         | In case of a conflict the device can retry with the additional field `cloudId` with the value `{{userid}}@{{serverurl}}` which allows the proxy to verify the public key and device identifier belongs to the given user on the instance |



## Unsubscribing at the Push Proxy

The device sends the `deviceIdentifier`, `deviceIdentifierSignature` and the user´s `publicKey`  (from the server´s response) to the Push Proxy:

```
DELETE /devices

{
  "deviceIdentifier": "{{deviceIdentifier}}",
  "deviceIdentifierSignature": "{{signature}}",
  "userPublicKey": "{{userPublicKey}}"
}
```



### Response

The server replies with the following status codes:

| Status code | Meaning                                  |
| ----------- | ---------------------------------------- |
| 200         | Push token was deleted from the database |
| 400         | Public key or device identifier is malformed |
| 403         | Device identifier and device public key didn't match or could not be found |



## Pushed notifications

The pushed notifications is defined by the [Firebase Cloud Messaging HTTP Protocol](https://firebase.google.com/docs/cloud-messaging/http-server-ref#send-downstream). The sample content of a Nextcloud push notification looks like the following:

```json
{
  "to" : "APA91bHun4MxP5egoKMwt2KZFBaFUH-1RYqx...",
  "notification" : {
    "body" : "NEW_NOTIFICATION",
    "body_loc_key" : "NEW_NOTIFICATION",
    "title" : "NEW_NOTIFICATION",
    "title_loc_key" : "NEW_NOTIFICATION"
  },
  "data" : {
    "subject" : "*Encrypted subject*",
    "signature" : "*Signature*"
  }
}
```

| Attribute   | Meaning                                  |
| ----------- | ---------------------------------------- |
| `subject`   | The subject is encrypted with the device´s *public key*. |
| `signature` | The signature is a sha512 signature over the encrypted subject using the user´s private key. |

### Encrypted subject data

#### Normal content notification

If you are missing any information necessary to parse the notification in a more usable way, use the `nid` to get the full notification information via [OCS API](ocs-endpoint-v2.md)

```json
{
  "app" : "spreed",
  "subject" : "Test mentioned you in a private conversation",
  "type" : "chat",
  "id" : "t0k3n",
  "nid" : 1337
}
```

| Attribute   | Meaning                                  | Capability |
| ----------- | ---------------------------------------- |------------|
| `app`   | The nextcloud app sending the notification | -|
| `subject`   | The subject of the actual notification | -|
| `type`   | Type of the object this notification is about | `object-data` |
| `id`   | Identifier of the object this notification is about | `object-data` |
| `nid`   | Numeric identifier of the notification in order to get more information via the [OCS API](ocs-endpoint-v2.md) | `object-data` |


#### Silent delete notification (single)

These notifications should not be shown to the user. Instead you should delete pending system notifications for the respective id

```json
{
  "delete" : true,
  "nid" : 1337
}
```

| Attribute   | Meaning                                  | Capability |
| ----------- | ---------------------------------------- |------------|
| `nid`   | Numeric identifier of the notification in order to get more information via the [OCS API](ocs-endpoint-v2.md) | `object-data` |
| `delete`   | Delete all notifications related to `nid` | `delete` |


#### Silent delete notification (all)

These notifications should not be shown to the user. Instead you should delete all pending system notifications for this account

```json
{
  "delete-all" : true
}
```

| Attribute   | Meaning                                  | Capability |
| ----------- | ---------------------------------------- |------------|
| `delete-all`   | Delete all notifications related to this account | `delete` |


### Verification
So a device should verify the signature using the user´s public key.
If the signature is okay, the subject can be decrypted using the device´s private key.
