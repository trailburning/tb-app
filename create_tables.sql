CREATE EXTENSION IF NOT EXISTS POSTGIS; 
CREATE EXTENSION IF NOT EXISTS HSTORE;

--DROP TABLE routepoints;
--DROP TABLE routes;
--DROP TABLE gpxfiles;

CREATE TABLE IF NOT EXISTS gpxfiles (
  id SERIAL PRIMARY KEY,
  path varchar(50)
);

CREATE TABLE IF NOT EXISTS routes(
  id SERIAL PRIMARY KEY,
  gpxfileid SERIAL REFERENCES gpxfiles(id),
  name varchar(20),
  center GEOMETRY
);

CREATE TABLE IF NOT EXISTS routepoints (
  id  SERIAL PRIMARY KEY,
  routeid SERIAL REFERENCES routes(id) NOT NULL,
  pointnumber INTEGER NOT NULL,
  coords GEOMETRY, -- POINT(X, Y)
  tags HSTORE      -- elevation, speed, date/time...
);


