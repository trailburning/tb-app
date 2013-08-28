CREATE EXTENSION IF NOT EXISTS POSTGIS; 
CREATE EXTENSION IF NOT EXISTS HSTORE;

DROP TABLE IF EXISTS route_medias CASCADE;
DROP TABLE IF EXISTS media_versions CASCADE;
DROP TABLE IF EXISTS medias CASCADE;
DROP TABLE IF EXISTS route_points CASCADE;
DROP TABLE IF EXISTS routes CASCADE;
DROP TABLE IF EXISTS gpx_files CASCADE;

CREATE TABLE IF NOT EXISTS gpx_files (
  id SERIAL PRIMARY KEY,
  path varchar(100)
);

CREATE TABLE IF NOT EXISTS routes (
  id SERIAL PRIMARY KEY,
  gpx_file_id SERIAL REFERENCES gpx_files(id) ON DELETE CASCADE,
  name VARCHAR(50),
  length INTEGER
);
SELECT AddGeometryColumn('routes', 'centroid', 4326, 'POINT', 2 );

CREATE TABLE IF NOT EXISTS route_points (
  id  SERIAL PRIMARY KEY,
  route_id SERIAL REFERENCES routes(id) ON DELETE CASCADE NOT NULL ,
  point_number INTEGER NOT NULL,
  tags HSTORE      -- elevation, speed, date/time...
);
SELECT AddGeometryColumn('route_points', 'coords', 4326, 'POINT', 2 );

CREATE TABLE IF NOT EXISTS medias (
  id serial PRIMARY KEY,
  tags HSTORE
);
SELECT AddGeometryColumn('medias', 'coords', 4326, 'POINT', 2 );

CREATE TABLE IF NOT EXISTS media_versions (
  id serial PRIMARY KEY,
  media_id SERIAL REFERENCES medias(id) ON DELETE CASCADE NOT NULL,
  version_size SMALLINT,
  path VARCHAR(100)
);

CREATE TABLE IF NOT EXISTS route_medias (
  route_id SERIAL REFERENCES routes(id) ON DELETE CASCADE NOT NULL,
  media_id SERIAL REFERENCES medias(id) ON DELETE CASCADE NOT NULL,
  linear_position FLOAT
);

CREATE RULE delete_route_medias AS ON DELETE TO route_medias do (
  DELETE FROM medias where medias.id=old.media_id
);
