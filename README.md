# Deployer Recipes

This repository contains Isotops recipes to integrate with deployer.

## Installation

```
composer require --dev isotopsweden/deployer-recipes
```

## Recipes

Load all:

```php
require 'vendor/isotopsweden/deployer-recipes/recipes.php';
```

Load separately:

| Recipe     | Usage
| ------     | -----
| apache     | `require 'vendor/isotopsweden/deployer-recipes/recipes/apache.php';`
| deploy     | `require 'vendor/isotopsweden/deployer-recipes/recipes/deploy.php';`
| redis      | `require 'vendor/isotopsweden/deployer-recipes/recipes/redis.php';`
| sentry     | `require 'vendor/isotopsweden/deployer-recipes/recipes/sentry.php';`
| wp         | `require 'vendor/isotopsweden/deployer-recipes/recipes/wp.php';`

## License

MIT © Isotop