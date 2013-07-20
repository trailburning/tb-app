CREATE EXTENSION IF NOT EXISTS POSTGIS; 
CREATE EXTENSION IF NOT EXISTS HSTORE;

--DROP TABLE routepoints;
--DROP TABLE routes;
CREATE TABLE  IF NOT EXISTS routes(
  id SERIAL PRIMARY KEY,
  name varchar(15),
  center GEOMETRY
);

CREATE TABLE IF NOT EXISTS routepoints (
  id  SERIAL PRIMARY KEY,
  routeid SERIAL REFERENCES routes(id) NOT NULL,
  pointnumber INTEGER NOT NULL,
  coords GEOMETRY, -- POINT(X, Y)
  tags HSTORE      -- elevation, speed, date/time...
);


INSERT INTO routes (name) VALUES ('Ultraks');




UPDATE routes
    SET center = (
        SELECT  ST_AsBinary(ST_Centroid(ST_MakeLine(rp.coords ORDER BY rp.pointnumber ASC)))
        FROM routepoints rp
        WHERE routes.id = rp.routeid )
    WHERE id=52; 



    SELECT route.id AS routeid, route.name AS name, ST_AsGeoJson(ST_MakeLine(routepoint.coords ORDER BY routepoint.pointnumber ASC)) AS route FROM routes as route, routepoints as routepoint WHERE route.id=?