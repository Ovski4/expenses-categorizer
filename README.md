Expense categorizer
===================

```bash
docker network create expenses
docker-compose up -d
docker-compose run php php bin/console doctrine:migrations:migrate
docker-compose run php php bin/console doctrine:fixtures:load
docker-compose run php php bin/phpunit
```
