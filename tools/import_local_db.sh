psql -d trailburning -c "drop schema public cascade"
psql -d trailburning -c "create schema public"
psql -q -t -h localhost -d trailburning -f tools/dump.sql