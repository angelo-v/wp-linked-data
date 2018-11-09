test:
	docker run -it --rm -v $(shell pwd):/workdir -u "$(shell id -u):$(shell id -g)" -w /workdir --network=host phpunit/phpunit test

.PHONY: test