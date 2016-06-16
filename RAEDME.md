# Shout API

This is simplistic twitter clone example api written in PHP

## Preparation

To test this API clone the project in your server and make sure the included .httaccess file is applyed by Apache then run all the migration inside the folder db_migration to setup the db and last configure the db connection rnaming the file SecretConfig-sample.php to SecretConfig.php and replacing the placeholders with your value

## Endpoints

this API has 9 end points:
### Register:
to REgister a new user it will return the username and token of the new user, the token will be valid for 2 day's and any other call will need to have username and token in the header
#### Ajax:
    var settings = {
      "async": true,
      "crossDomain": true,
      "url": <url>"/api/v1/register",
      "method": "POST",
      "headers": {
        "cache-control": "no-cache",
        "content-type": "application/x-www-form-urlencoded"
      },
      "data": {
        "username": <Username>,
        "encrypted_password": <password>
      }
    }

    $.ajax(settings).done(function (response) {
      console.log(response);
    });

#### Curl:
    curl -X POST -H "Cache-Control: no-cache" -H "Content-Type: application/x-www-form-urlencoded" -d 'username=<username>&encrypted_password=<password>' <url>"/api/v1/register"

#### Response:
    {"id":<id>,"username":<Username>,"token":<token>}

### Login:
Create a new token for the user
#### Ajax:
    var settings = {
      "async": true,
      "crossDomain": true,
      "url": <url>"/api/v1/login",
      "method": "POST",
      "headers": {
        "cache-control": "no-cache",
        "content-type": "application/x-www-form-urlencoded"
      },
      "data": {
        "username": <Username>,
        "encrypted_password": <password>
      }
    }

    $.ajax(settings).done(function (response) {
      console.log(response);
    });

#### curl:
    curl -X POST -H "Cache-Control: no-cache" -H "Content-Type: application/x-www-form-urlencoded" -d 'username=<username>&encrypted_password=<password>' <url>"/api/v1/login"

#### Response:
    {"id":<id>,"username":<Username>,"token":<token>}

### Logout:
Destroy the token so will force the user to relogin on the next call
#### Ajax:
    var settings = {
      "async": true,
      "crossDomain": true,
      "url": <url>"/api/v1/logout",
      "method": "POST",
      "headers": {
        "username": <Username>,
        "token": <token>,
        "cache-control": "no-cache",
        "content-type": "application/x-www-form-urlencoded"
      }
    }

    $.ajax(settings).done(function (response) {
      console.log(response);
    });

#### Curl:
    curl -X POST -H "username: <username>" -H "token: <token>" -H "Cache-Control: no-cache" -H "Content-Type: application/x-www-form-urlencoded" -d '' <url>"/api/v1/logout"

#### Response:
    {"status":"ok","message":"user logged out"}

### New Shout:
Used to create a ne Shout, only loggen in user can call this endpoint it wil return the new shout
#### Ajax:
    var settings = {
      "async": true,
      "crossDomain": true,
      "url": <url>"/api/v1/shout",
      "method": "POST",
      "headers": {
        "username": <Username>,
        "token": <token>,
        "cache-control": "no-cache",
        "content-type": "application/x-www-form-urlencoded"
      },
      "data": {
        "body": <body>
      }
    }

    $.ajax(settings).done(function (response) {
      console.log(response);
    });

#### Curl:
    curl -X POST -H "username: <username>" -H "token: <token>" -H "Cache-Control: no-cache" -H "Content-Type: application/x-www-form-urlencoded" -d 'body=<body>' <url>"/api/v1/shout"

#### Response:
    {"id":<id>,"body":<body>,"user_id":<user_id>,"vote":<vote>,"shouted_on":<shouted_on>}

### Get All Shout:
will return all the shouts
#### Ajax:
    var settings = {
      "async": true,
      "crossDomain": true,
      "url": <url>"/api/v1/shout",
      "method": "GET",
      "headers": {
        "cache-control": "no-cache"
      }
    }

    $.ajax(settings).done(function (response) {
      console.log(response);
    });

#### Curl:
    curl -X GET -H "Cache-Control: no-cache" <url>"/api/v1/shout"

#### Response:
    {"status":"ok","result":[{"id":<id>,"body":<body>,"user_id":<user_id>,"vote":<vote>,"shouted_on":<shouted_on>},{"id":<id>,"body":<body>,"user_id":<user_id>,"vote":<vote>,"shouted_on":<shouted_on>}]}

### Get Shout:
Will return a specific shout
#### Ajax:
    var settings = {
      "async": true,
      "crossDomain": true,
      "url": <url>"/api/v1/shout/1",
      "method": "GET",
      "headers": {
        "cache-control": "no-cache"
      }
    }

    $.ajax(settings).done(function (response) {
      console.log(response);
    });

#### Curl:
    curl -X GET -H "Cache-Control: no-cache" <url>"/api/v1/shout/1"

#### Response:
    {"id":<id>,"body":<body>,"user_id":<user_id>,"vote":<vote>,"shouted_on":<shouted_on>}

### Upvote Shout:
will in crease the vote of the sout by 1
#### Ajax:
    var settings = {
      "async": true,
      "crossDomain": true,
      "url": <url>"/api/v1/shout/<id>/upvote",
      "method": "GET",
      "headers": {
        "cache-control": "no-cache"
      }
    }

    $.ajax(settings).done(function (response) {
      console.log(response);
    });

#### Curl:
    curl -X GET -H "Cache-Control: no-cache" <url>"/api/v1/shout/<id>/upvote"

#### Response:
    {"id":<id>,"body":<body>,"user_id":<user_id>,"vote":<vote>,"shouted_on":<shouted_on>}

### Downvote Shout:
will decrease the vote of the shout by 1
#### Ajax:
    var settings = {
      "async": true,
      "crossDomain": true,
      "url": <url>"/api/v1/shout/<id>/downvote",
      "method": "GET",
      "headers": {
        "cache-control": "no-cache"
      }
    }

    $.ajax(settings).done(function (response) {
      console.log(response);
    });

#### Curl:
    curl -X GET -H "Cache-Control: no-cache" <url>"/api/v1/shout/<id>/downvote"

#### Response:
    {"id":<id>,"body":<body>,"user_id":<user_id>,"vote":<vote>,"shouted_on":<shouted_on>}

### Delete Shout:
Will delete the shout, only the owner of the shout can delete it
#### Ajax:
    var settings = {
      "async": true,
      "crossDomain": true,
      "url": <url>"/api/v1/shout/5",
      "method": "DELETE",
      "headers": {
        "username": <Username>,
        "token": <token>,
        "cache-control": "no-cache"
        "content-type": "application/x-www-form-urlencoded"
      }
    }

    $.ajax(settings).done(function (response) {
      console.log(response);
    });

#### Curl:
    curl -X DELETE -H "username: <username>" -H "token: <token>" -H "Cache-Control: no-cache" -H "Content-Type: application/x-www-form-urlencoded" -d '' <url>"/api/v1/shout/<id>"

#### Response:
    {"status":"ok","message":"Shout deleted"}

