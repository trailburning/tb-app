CREATE EXTENSION IF NOT EXISTS POSTGIS; 
CREATE EXTENSION IF NOT EXISTS HSTORE;

DROP TABLE IF EXISTS routes_medias;
DROP TABLE IF EXISTS mediaversions;
DROP TABLE IF EXISTS media;
DROP TABLE IF EXISTS routepoints;
DROP TABLE IF EXISTS routes;
DROP TABLE IF EXISTS gpxfiles;

CREATE TABLE IF NOT EXISTS gpxfiles (
  id SERIAL PRIMARY KEY,
  path varchar(100)
);

CREATE TABLE IF NOT EXISTS routes(
  id SERIAL PRIMARY KEY,
  gpxfileid SERIAL REFERENCES gpxfiles(id) ON DELETE CASCADE,
  name VARCHAR(50),
  length INTEGER
);
SELECT AddGeometryColumn('routes', 'centroid', 4326, 'POINT', 2 );

CREATE TABLE IF NOT EXISTS routepoints (
  id  SERIAL PRIMARY KEY,
  routeid SERIAL REFERENCES routes(id) ON DELETE CASCADE NOT NULL ,
  pointnumber INTEGER NOT NULL,
  tags HSTORE      -- elevation, speed, date/time...
);
SELECT AddGeometryColumn('routepoints', 'coords', 4326, 'POINT', 2 );

CREATE TABLE IF NOT EXISTS media (
  id serial PRIMARY KEY,
  tags HSTORE
);
SELECT AddGeometryColumn('media', 'coords', 4326, 'POINT', 2 );

CREATE TABLE IF NOT EXISTS mediaversions (
  id serial PRIMARY KEY,
  mediaid SERIAL REFERENCES media(id) ON DELETE CASCADE NOT NULL,
  mediasize SMALLINT,
  path VARCHAR(100)
);

CREATE TABLE IF NOT EXISTS routes_medias(
  routeid SERIAL REFERENCES routes(id) ON DELETE CASCADE NOT NULL,
  mediaid SERIAL REFERENCES media(id) ON DELETE CASCADE NOT NULL,
  linear_position FLOAT
);

CREATE RULE delete_routes_medias AS ON DELETE TO routes_medias do (
  DELETE FROM media where media.id=old.mediaid
);