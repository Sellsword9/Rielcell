reset:
	docker compose down
	docker compose rm -f
	docker compose build --no-cache
s:
	docker compose up
sd: 
	docker compose up -d
off:
	docker compose down
build: 
	docker compose build --no-cache
migrate: build
	docker exec -it rielcell-web-1 php bin/console doctrine:migrations:migrate --no-interaction
up: migrate
	docker compose up -d
quick-migrate:
	docker exec -it rielcell-web-1 php bin/console doctrine:migrations:migrate --no-interaction