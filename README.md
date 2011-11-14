# Elao Error Notifier Bundle

## Installation

Add the followings lines to your `deps` file

    [ElaoErrorNotifierBundle]
    git=git://github.com/Elao/ErrorNotifierBundle.git
    target=bundles/Elao/ErrorNotifierBundle

#Configuration

Add in your `config_prog.yml` file, you don't need this lines when you are in dev environment.

```yml
elao_error_notifier:
    from: from@example.com
    to: to@example.com
    error404: false
```