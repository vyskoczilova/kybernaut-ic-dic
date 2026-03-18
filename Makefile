.PHONY: build styles scripts translate version-bump install test

# Build all assets
build:
	gulp build

# Individual build tasks
styles:
	gulp styles

scripts:
	gulp scripts

translate:
	gulp translate

# Install dependencies
install:
	npm install
	composer install

# Run tests
test:
	composer test:unit

# Bump version number
version-bump:
	@bash bin/version-bump.sh
