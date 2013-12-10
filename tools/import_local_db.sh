psql -U odeiko -d trailburning -c "drop schema public cascade"
psql -U odeiko -d trailburning -c "create schema public"
psql -q -t -h localhost -d trailburning -U odeiko -f batch/dump.sql