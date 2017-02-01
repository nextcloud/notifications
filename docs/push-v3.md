# Push notifications as a Nextcloud client device



## Checking the capabilities of the Nextcloud server

In order to find out if notifications support push on the server you can run a request against the capabilities endpoint: `/ocs/v2.php/cloud/capabilities`

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
            ...
            "push"
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

4. The device then sends the `devicePublicKey` and `PushTokenHash` to the Nextcloud server:

   ```json
   POST /ocs/v2.php/apps/notifications/api/v3/push

   {
     "pushTokenHash": "{{PushTokenHash}}",
     "devicePublicKey": "{{devicePublicKey}}"
   }
   ```

   ​

### Response

The server replies with the following status codes:

| Status code | Meaning                                  |
| ----------- | ---------------------------------------- |
| 200         | No further action by the device required |
| 201         | Push token was created/updated and **needs to be sent to the `Proxy`** |
| 400         | Invalid public key, device does not use a token to authenticate or the push token hash is invalid formatted |
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



## Unsubcribing at the Nextcloud server

When an account is removed from a device, the device should unregister on the server. Otherwise the server sends unnecessary push notifications and might be blocked because of spam.



The device should then send the `devicePublicKey` and `PushTokenHash` to the Nextcloud server: 

```json
DELETE /ocs/v2.php/apps/notifications/api/v3/push

{
  "devicePublicKey": "{{devicePublicKey}}"
}
```



### Response

The server replies with the following status codes:

| Status code | Meaning                                  |
| ----------- | ---------------------------------------- |
| 202         | Push token was deleted and **needs to be deleted from the `Proxy`** |
| 400         | Invalid public key or device does not use a token to authenticate |
| 401         | Device is not logged in                  |



#### Body in case of an error

In case of `400` the following `message` can appear in the body:

| Error                   | Description                              |
| ----------------------- | ---------------------------------------- |
| `INVALID_SESSION_TOKEN` | The authentication token of the request could not be identified. |
| `INVALID_DEVICE_KEY`    | The device key does not match the one registered to the provided session token. |



## Subscribing at the Push Proxy

The device sends the`PushToken` as well as the `deviceIdentifier`, `signature` and the user´s `publicKey`  (from the server´s response) to the Push Proxy:

```json
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

```json
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
| 200         | Push token was deleted from the databse  |
| 400         | Public key or device identifier is malformed |
| 403         | Device identifier and device public key didn't match or could not be found |



