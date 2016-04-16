# xauthbot for Telegram

Use my bot to connect your webapp or software to Telegram and let users log in with their Telegram accounts.

### How to use it
 * Register your app first ([here](https://xauth.ldf.de/registerapp.php))
 * To connect a user, forward them to https://xauth.ldkf.de/connect.php?id=YOURAPPID&ret=RET
 * After successfully authenticating, your user will be forwarded to https://yourdomain.tld/RET?id= (or http if you didn't select https at registration) where id is the user's id.
 * You'll need this id when you want to make requests to the API

### The API

 * URL: https://xauth.ldkf.de/api.php
 * For a successful request you need the following GET parameters:
    * appid - you get it the registration of your app
    * id - the user id, you get when the user returns after connecting
    * action - what you want to do with the API (see below)
    * hash - the sha256 hash of the following data without separation between: secret (from app registration), appid, id, action - a string to hash could look like this: 6O4BZl1yj9btgqyjRFBH43username

#### Actions

* username - to get the user's username on Telegram (keep in mind that not everyone has one - this could return an empty string)
* more to come

#### Response

* The API will return data json encoded
* A response will always have a statuscode and status
* Statuscodes used at the moment are
    * 200 - Success
    * 400 - Syntax Error (this could be a wrong hash, missing parameters or non-existing users)
    * 401 - if an action is not yet fully implemented
* In case of 200, the requested data will be in the corresponding field (ie the username in the username field)

### How it works

* When visiting the connect page, the user will get an entry with an authentication code
* If they click on the link to the bot, the authentication code will be sent to the bot via deep linking


### To-do
* Style
* Auto-forward after contacting the bot
* more API actions
* Revoking of permissions in Telegram
* Sending of messages to users through the API
