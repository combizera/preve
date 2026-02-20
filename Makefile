COMPOSE_FILE=docker-compose.local.yml
DOCKER_COMPOSE=docker compose -f $(COMPOSE_FILE)

# -------------------------------------------------------------------
# Up / Down / Restart
# -------------------------------------------------------------------
up:
	$(DOCKER_COMPOSE) up -d

up-build:
	$(DOCKER_COMPOSE) up -d --build

down:
	$(DOCKER_COMPOSE) down

restart: down up

# -------------------------------------------------------------------
# Logs
# -------------------------------------------------------------------
logs:
	$(DOCKER_COMPOSE) logs -f

logs-app:
	$(DOCKER_COMPOSE) logs -f app

logs-node:
	$(DOCKER_COMPOSE) logs -f node

# -------------------------------------------------------------------
# Exec / Shell
# -------------------------------------------------------------------
bash-app:
	$(DOCKER_COMPOSE) exec app sh

bash-node:
	$(DOCKER_COMPOSE) exec node sh

# -------------------------------------------------------------------
# Artisan / Laravel
# -------------------------------------------------------------------
migrate:
	$(DOCKER_COMPOSE) exec app php artisan migrate

migrate-fresh:
	$(DOCKER_COMPOSE) exec app php artisan migrate:fresh

seed:
	$(DOCKER_COMPOSE) exec app php artisan migrate:fresh --seed

wayfinder:
	$(DOCKER_COMPOSE) exec app php artisan wayfinder:generate --with-form

# -------------------------------------------------------------------
# Node / Vite / NPM
# -------------------------------------------------------------------
npm-install:
	$(DOCKER_COMPOSE) exec node npm install

npm-dev:
	$(DOCKER_COMPOSE) exec node npm run dev

npm-build:
	$(DOCKER_COMPOSE) exec node npm run build
