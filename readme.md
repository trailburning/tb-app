# Trailburning REST API

## Installation notes

Composer is used for library dependancy management. To download/update all third-party libraries, this needs to be run:

```bash
php composer.phar update
```

## REST methods

### POST /v1/route/import/gpx

Uploads GPX file - imports it to PostGIS, and uploads to S3.
