# Trailburning REST API

## Installation notes

You will need the following packages:
+ php5-pgsql
+ php5-curl


Mod_rewrite needs to be activated.

Composer is used for library dependancy management. 
First download composer.phar from http://getcomposer.org/ to the root directory of the project. Then to download/update all third-party libraries, run:

```bash
php composer.phar update
```

## REST methods

The REST methods return an HTTP error code and JSON data. All JSON messages contain a "message" variable that contains a description of the error/success.

### POST /v1/route/import/gpx

Uploads GPX file - imports it to PostGIS, and uploads to S3.

Parameters:
+ POST gpxfile  : Uploaded GPX File

Returns:
+ routeids: array of ids for the imported routes

### GET /v1/route/:routeid

Returns GPX file, currently only as JSON. Will output in format according to "Accept" header in the future.

Returns:
+ Currently, id, name, and an array of points [lat, lon]. This is not final.


# Codeception testing

+ Run ./vendor/bin/codecept run
