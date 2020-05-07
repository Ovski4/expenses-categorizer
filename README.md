Expense categorizer
===================

Run the app:

```bash
docker-compose up -d
docker-compose run php php bin/console doctrine:migrations:migrate --no-interaction
```

Run the tests:

```bash
docker-compose run php php bin/phpunit
```
