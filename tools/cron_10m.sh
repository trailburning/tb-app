#!/bin/bash

# Get the directory the script is in
dir="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"     

# define the console command to call
console=$dir/../app/console

# Start 3 RabbitMQ Consumer
nohup $console rabbitmq:consumer main &
nohup $console rabbitmq:consumer main &
nohup $console rabbitmq:consumer main &