# Laravel Nova tool for running Artisan commands.

This [Nova](https://nova.laravel.com) tool lets you:
- run artisan commands
- specify options for commands
- get command result
- view commands history
> This is an extended version of the original package [Nova Command Runner](https://github.com/guratr/nova-command-runner) by [guratr](https://github.com/guratr)

![screenshot of the command runner tool](https://user-images.githubusercontent.com/1502853/50797697-16c4f100-12ef-11e9-99b0-2bf9736236f1.png)

## Installation

You can install the nova tool in to a Laravel app that uses [Nova](https://nova.laravel.com) via composer:

```bash
composer require binarybuilds/nova-advanced-command-runner
```

Next up, you must register the tool with Nova. This is typically done in the `tools` method of the `NovaServiceProvider`.

```php
// in app/Providers/NovaServiceProvder.php

// ...

public function tools()
{
    return [
        // ...
        new \BinaryBuilds\NovaAdvancedCommandRunner\CommandRunner,
    ];
}
```

Publish the config file:

``` bash
php artisan vendor:publish --provider="BinaryBuilds\NovaAdvancedCommandRunner\ToolServiceProvider"
```

Add your commands to config/nova-advanced-command-runner.php

Available options:
- run : command to run (E.g. route:cache)
- options : arrary of options for command (e.g. ['--allow' => ['127.0.0.1']])  
- type : button class (primary, secondary, success, danger, warning, info, light, dark, link) 
- group: Group name (optional)

## Usage

Click on the "Command Runner" menu item in your Nova app to see the tool.

## Credits
- [guratr](https://github.com/guratr)

## Contributing

Thank you for considering contributing to Laravel mail manager! Please create a pull request with your contributions with detailed explanation of the changes you are proposing.

## Security Vulnerabilities

If you found a security vulnerability with in this package, Please do not use the issue tracker. Instead send an email to `srinathreddydudi@gmail.com`. All security vulnerabilities are addressed promptly.

## License

This package is open-sourced software licensed under the [MIT license](LICENSE.md).
