# INSTALLATION

## Summary 
My Flagship CMS app so depends on this package that it has the composer and service provider stuff built in already. 


## composer.json:

```
{
    "require": {
        "lasallecms/lasallecmsapi": "0.1.*",
    }
}
```


## Service Provider

In config/app.php:
```
'Lasallecms\Lasallecmsapi\LasallecmsapiServiceProvider',
```


## Facade Alias

* none


## Dependencies
* none


## Publish the Package's Config

With Artisan:
```
php artisan vendor:publish
```

## Migration

With Artisan:
```
php artisan migrate
```

## Notes

* view files will be published to the main app's view folder
* first: install all your packages 
* second: run "vendor:publish" (once for all packages) 
* third:  run "migrate" (once for all packages)


## Serious Caveat 

This package is designed to run specifically with my Flagship blog app.