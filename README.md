Running:
```sh
php composer.phar install --ignore-platform-reqs
php init --env=Development
docker-compose up -d
docker exec -it test-sql-frontend /bin/bash -c 'php yii migrate/up --interactive=0'
```

Test page:\
[http://127.0.0.1](http://127.0.0.1)
