CREATE EXTENSION IF NOT EXISTS POSTGIS; 
CREATE EXTENSION IF NOT EXISTS HSTORE;

DROP TABLE trackpoints;
DROP TABLE tracks;
CREATE TABLE tracks (
  id SERIAL PRIMARY KEY,
  name varchar(15) UNIQUE
);

CREATE TABLE trackpoints (
  id  SERIAL PRIMARY KEY,
  trackid SERIAL REFERENCES tracks(id) NOT NULL,
  coords GEOMETRY, -- POINT(X, Y)
  tags HSTORE      -- elevation, speed, date/time...
);


INSERT INTO tracks (name) VALUES ('Ultraks');

INSERT INTO trackpoints (trackid, coords, tags) VALUES (
    (select id from tracks where name = 'Ultraks'), 
    'POINT(46.019698202 7.746125325)',
    '"elevation" => "1624", "time"=>"2013-07-02T18:37:07Z"'
),(
    (select id from tracks where name = 'Ultraks'), 
    'POINT(46.019588282 7.7468478)',
    '"elevation" => "1623", "time"=>"2013-07-02T18:37:07Z"'
),(
    (select id from tracks where name = 'Ultraks'), 
    'POINT(46.019389325 7.747234049)',
    '"elevation" => "1618", "time"=>"2013-07-02T18:37:07Z"'
);
