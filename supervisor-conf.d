[program:horizon]
process_name=%(program_name)s
command=php /var/www/backend/artisan horizon
autostart=true
autorestart=true
user=ubuntu
redirect_stderr=true
;stdout_logfile=/home/forge/example.com/horizon.log
stopwaitsecs=3600
