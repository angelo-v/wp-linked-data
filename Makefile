install:
	docker run --rm --tty --interactive \
		--user $(shell id -u):$(shell id -g) \
		--volume $(shell pwd):/app \
		--volume $${HOME}/.composer:/tmp \
		composer install

update:
	docker run --rm --tty --interactive \
		--user $(shell id -u):$(shell id -g) \
		--volume $(shell pwd):/app \
		--volume $${HOME}/.composer:/tmp \
		composer update

test: install
	docker run -it --rm -v $(shell pwd):/workdir -u "$(shell id -u):$(shell id -g)" -w /workdir --network=host phpunit/phpunit test

start: install
	docker-compose --project-name wp-linked-data-dev --file=./docker/development.yml up --build -d

plugin-repo:
	svn co https://plugins.svn.wordpress.org/wp-linked-data plugin-repo

copy-to-plugin-repo: plugin-repo install
	rsync -avh ./src/ ./plugin-repo/trunk/ --delete

add-all-to-plugin-repo: copy-to-plugin-repo
	cd ./plugin-repo/trunk ; svn add --force * --auto-props --parents --depth infinity -q

diff-plugin-repo: add-all-to-plugin-repo
	cd ./plugin-repo/trunk ; svn diff ; svn stat;

publish-to-plugin-repo: diff-plugin-repo
	cd ./plugin-repo/trunk ; svn ci -m "publish to wordpress plugin repo"

.PHONY: install start test copy-to-plugin-repo diff-plugin-repo
