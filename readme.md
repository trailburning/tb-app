# Trailburning REST API

## Installation notes

Composer is used for library dependancy management. To download/update all third-party libraries, this needs to be run:

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