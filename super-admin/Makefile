.PHONY: bootstrap clean pint test

bootstrap:
	docker compose exec app php artisan app:bootstrap

clean:
	docker compose exec app php artisan app:clean

pint:
	docker compose exec app ./vendor/bin/pint

test:
	docker compose exec app php artisan test
