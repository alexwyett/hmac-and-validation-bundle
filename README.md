Symfony HMAC and Validation Bundle
==========================

Symfony Bundle to handle HMAC authentication and Parameter validation

## Configuration
There are several optional parameters which can be added into the config.yml, these are:

```

aw_hmac:
    # Set to either depending on if you want hmac authentication turned on or not
    hmac: true|false
    
    # Add in additional roles as required
    hmac_roles: ["USER", "ADMIN"]
    
    # Add a path to a directory which stores your json validation schemas
    json_schema_route: /path/to/your/schema/dir 

```

## Running unit tests

1. Make sure the bundle is configured in your AppKernel.php
2. Ensure that the app/console doctrine:schema:create has been run and is up to date.
3. Add the following into your app/config.yml

```
services:
    kernel.listener.ExceptionListener:
        class: AW\HmacBundle\Listeners\ExceptionListener
        arguments: [@kernel]
        tags:
            - { name: kernel.event_listener, event: kernel.exception, method: onKernelException }
```
4. Ensure the route /hmac is pointing at the Bundle in your routing config, i.e:
```
aw_hmac_bundle:
    resource: "@AWHmacBundle/Controller/"
    type:     annotation
    prefix:   /hmac
```


### If installed with composer
From within the symfony route:
`phpunit -c app/ vendor/AW/hmac-and-validation-bundle/AW/HmacBundle/Tests`

### If cloned directly into a symfony install
`phpunit -c app/ AW/HmacBundle/`
