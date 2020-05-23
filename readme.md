## Laravel GIT pre-commit hook

### Installation
To install through Composer, by run the following command:

```
composer require hungthai1401/laravel-pre-commit --dev
```

The package will automatically register a service provider and alias.

Optionally, publish the package's configuration file by running:

```
php artisan vendor:publish --provider="HT\PreCommit\Providers\PreCommitServiceProvider" --tag=config
```

### Usage

- Install git pre-commit hook:

```
php artisan git:pre-commit-hook:install
```

- Publish default PSR config (It will be create phpcs.xml in your root project.).

```
php artisan git:publish-phpcs
```

- Run test manually (made sure that you've added all changed files to git stage)

```
php artisan git:pre-commit
```
