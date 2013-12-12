#pg_dump -h ec2-54-217-240-136.eu-west-1.compute.amazonaws.com -U uffcp636dbunjn -p 5752 --data-only -N 'tz_workd_mp' d640i4r4smeoia > batch/dump.sql

pg_dump -h ec2-54-217-240-136.eu-west-1.compute.amazonaws.com -U uffcp636dbunjn -p 5752 d640i4r4smeoia > tools/dump.sql