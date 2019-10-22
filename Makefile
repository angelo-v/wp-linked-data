start:
	docker-compose --project-name wp-linked-data-dev --file=./docker/development.yml up --build -d

test:
	docker run -it --rm -v $(shell pwd):/workdir -u "$(shell id -u):$(shell id -g)" -w /workdir --network=host phpunit/phpunit test

install:
	docker run --rm --tty --interactive \
		--user $(shell id -u):$(shell id -g) \
		--volume $(shell pwd):/app \
		--volume $${HOME}/.composer:/tmp \
		composer install

.PHONY: test
