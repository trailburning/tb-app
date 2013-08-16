CREATE EXTENSION IF NOT EXISTS POSTGIS; 
CREATE EXTENSION IF NOT EXISTS HSTORE;


DROP TABLE routes_medias;
DROP TABLE mediaversions;
DROP TABLE media;
DROP TABLE routepoints;
DROP TABLE routes;
DROP TABLE gpxfiles;

CREATE TABLE IF NOT EXISTS gpxfiles (
  id SERIAL PRIMARY KEY,
  path varchar(100)
);

CREATE TABLE IF NOT EXISTS routes(
  id SERIAL PRIMARY KEY,
  gpxfileid SERIAL REFERENCES gpxfiles(id) ON DELETE CASCADE,
  name VARCHAR(20),
  centroid GEOMETRY
);

CREATE TABLE IF NOT EXISTS routepoints (
  id  SERIAL PRIMARY KEY,
  routeid SERIAL REFERENCES routes(id) ON DELETE CASCADE NOT NULL ,
  pointnumber INTEGER NOT NULL,
  coords GEOMETRY, -- POINT(X, Y)
  tags HSTORE      -- elevation, speed, date/time...
);

CREATE TABLE IF NOT EXISTS media (
  id serial PRIMARY KEY,
  coords GEOMETRY, 
  tags HSTORE
);

CREATE TABLE IF NOT EXISTS mediaversions (
  id serial PRIMARY KEY,
  mediaid SERIAL REFERENCES media(id) ON DELETE CASCADE NOT NULL,
  mediasize SMALLINT,
  path VARCHAR(50)
);

CREATE TABLE IF NOT EXISTS routes_medias(
  routeid SERIAL REFERENCES routes(id) ON DELETE CASCADE NOT NULL,
  mediaid SERIAL REFERENCES media(id) ON DELETE CASCADE NOT NULL,
  linear_position FLOAT
);

CREATE RULE delete_routes_medias AS ON DELETE TO routes_medias do (
  DELETE FROM media where media.id=old.mediaid
);  