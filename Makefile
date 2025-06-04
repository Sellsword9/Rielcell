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
