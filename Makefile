##################
# Variables
##################

DOCKER_COMPOSE = docker-compose -f ./_docker/docker-compose.yml --env-file ./_docker/.env
DOCKER_COMPOSE_TEST = ${DOCKER_COMPOSE} ${DOCKER_COMPOSE_EXEC} php bin/console
DOCKER_COMPOSE_EXEC = exec -u www-data php-fpm

PRINT_SEPARATOR = "\n"
PRINT_WELCOME = Welcome: http://localhost:5000
PRINT_WEBMAIL = webMail: http://localhost:1080/
PRINT_PGADMIN = pgAdmin: http://localhost:5050/browser/

##################
# Docker compose
##################

build:
	${DOCKER_COMPOSE} build
start:
	${DOCKER_COMPOSE} start
stop:
	${DOCKER_COMPOSE} stop
up:
	${DOCKER_COMPOSE} up -d --remove-orphans
ps:
	${DOCKER_COMPOSE} ps
logs:
	${DOCKER_COMPOSE} logs -f
down:
	${DOCKER_COMPOSE} down -v --rmi=all --remove-orphans
restart:
	make stop start

##################
# App
##################

php:
	${DOCKER_COMPOSE} ${DOCKER_COMPOSE_EXEC} bash
composer:
	${DOCKER_COMPOSE} ${DOCKER_COMPOSE_EXEC} composer install
cache:
	${DOCKER_COMPOSE} ${DOCKER_COMPOSE_EXEC} php bin/console cache:clear
	${DOCKER_COMPOSE} ${DOCKER_COMPOSE_EXEC} php bin/console cache:clear --env=test

##################
# Test
##################

t_creat:
	${DOCKER_COMPOSE_TEST} --env=test doctrine:database:create
t_del_db:
	${DOCKER_COMPOSE_TEST} --env=test doctrine:database:drop --force
t_migrate:
	${DOCKER_COMPOSE_TEST} --env=test doctrine:migrations:migrate --no-interaction
t_diff:
	${DOCKER_COMPOSE_TEST} --env=test doctrine:migrations:diff --no-interaction
t_drop:
	${DOCKER_COMPOSE_TEST} --env=test doctrine:schema:drop --full-database --force
t_load:
	${DOCKER_COMPOSE_TEST} --env=test doctrine:fixtures:load --append
t_clear_add:
	make clear_migrate t_creat t_drop t_diff t_migrate t_load

test:
	${DOCKER_COMPOSE} exec -u www-data php-fpm php bin/phpunit

##################
# Database
##################

migrate:
	${DOCKER_COMPOSE_TEST} doctrine:migrations:migrate --no-interaction
diff:
	${DOCKER_COMPOSE_TEST} doctrine:migrations:diff --no-interaction
drop:
	${DOCKER_COMPOSE_TEST} doctrine:schema:drop --full-database --force
load:
	${DOCKER_COMPOSE_TEST} doctrine:fixtures:load --append
clear_add:
	make clear_migrate drop diff migrate load

clear_migrate:
	rm -rf ./migrations/*

#################
#  Deployment
#################
dep:
	make clear_migrate build up composer pause10 clear_add t_clear_add print

#################
# pause
# for weak computers.
#################

pause10:
	sleep 10

#################
# Hi
#################
print:
	@echo ${PRINT_SEPARATOR}
	@echo ${PRINT_WELCOME}
	@echo ${PRINT_WEBMAIL}
	@echo ${PRINT_PGADMIN}
	@echo ${PRINT_SEPARATOR}
