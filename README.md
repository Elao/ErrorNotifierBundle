# Elao Error Notifier Bundle

## Installation

###Add the followings lines to your `deps` file

    // deps

    [ElaoErrorNotifierBundle]
        git=git://github.com/Elao/ErrorNotifierBundle.git
        target=bundles/Elao/ErrorNotifierBundle

### Register autoloading

    // app/autoload.php

    $loader->registerNamespaces(array(
        ...
        'Elao' => __DIR__.'/../vendor/bundles',
    ));

### Register the bundle

    // app/AppKernel.php

    public function registerBundles()
    {
        return array(
            // ...
            new Elao\ErrorNotifierBundle\ElaoErrorNotifierBundle(),
            // ...
        );
    }

### Run the vendors script:

```bash
$ php bin/vendors install
```

#Configuration

Add in your `config_prod.yml` file, you don't need this lines when you are in dev environment.

```yml
elao_error_notifier:
    from: from@example.com
    to: to@example.com
    handle404: true
```
