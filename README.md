Expense categorizer
===================

```bash
docker network create expenses
docker-compose up -d
docker-compose run php php bin/console doctrine:migrations:migrate
docker-compose run php php bin/console doctrine:fixtures:load --append
docker-compose run php php bin/phpunit

# import transactions, then:

docker-compose run php php bin/console app:categorize-transactions
docker-compose run php php bin/console app:export-transactions
```
