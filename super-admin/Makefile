.PHONY: bootstrap clean pint test test-filter

bootstrap:
	docker compose exec app php artisan app:bootstrap

clean:
	docker compose exec app php artisan app:clean

pint:
	docker compose exec app ./vendor/bin/pint

test:
	docker compose exec app php artisan test

test-filter:
	docker compose exec app php artisan test --filter=$(filter)
