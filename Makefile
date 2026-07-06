# SPDX-FileCopyrightText: 2018 Nextcloud GmbH and Nextcloud contributors
# SPDX-License-Identifier: AGPL-3.0-or-later

app_name=notifications

project_dir=$(CURDIR)/../$(app_name)
build_dir=$(CURDIR)/build
source_dir=$(build_dir)/$(app_name)
sign_dir=$(build_dir)/sign
composer=$(shell which composer 2> /dev/null)

all: dev-setup build-js-production

dev-setup: clean clean-dev composer-dev npm-init

composer-dev:
	composer i

composer-production:
	composer i --no-dev

npm-init:
	npm ci

npm-update:
	npm update

dependabot: dev-setup npm-update build-js-production

build-js:
	npm run dev

build-js-production:
	npm run build

lint:
	npm run lint

lint-fix:
	npm run lint:fix

watch-js:
	npm run watch

clean:
	rm -f js/notifications.js
	rm -f js/notifications.js.map
	rm -rf $(build_dir)

clean-dev:
	rm -rf node_modules

package: dev-setup build-js-production composer-production
	mkdir -p $(source_dir)
	rsync -a \
	--exclude=/build \
	--exclude=/docs \
	--exclude=/js-src \
	--exclude=/.tx \
	--exclude=/tests \
	--exclude=/.git \
	--exclude=/.github \
	--exclude=/CONTRIBUTING.md \
	--exclude=/issue_template.md \
	--exclude=/README.md \
	--exclude=/.gitignore \
	--exclude=/.scrutinizer.yml \
	--exclude=/.travis.yml \
	--exclude=/.drone.yml \
	--exclude=.l10nignore \
	--exclude=/node_modules \
	--exclude=/npm-debug.log \
	--exclude=/package.json \
	--exclude=/package-lock.json \
	--exclude=/Makefile \
	$(project_dir)/ $(source_dir)
