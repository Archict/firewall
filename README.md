# Firewall

[![Tests](https://github.com/Archict/firewall/actions/workflows/tests.yml/badge.svg?branch=master)](https://github.com/Archict/firewall/actions/workflows/tests.yml)

Control access to your resources

## How to use

The majority of the work is inside the config file `firewall.yml`:

```yaml
providers:
  my_provider: \Class\Name\Of\My\User\Provider
access_control:
  - path: ^/admin # Path to match (a regex)
    provider: my_provider
    roles: [ "ADMIN" ] # Roles user need to have to see resources
    error: 404 # If user not authorized, then return this error code
  - path: ^/profile
    provider: my_provider
    roles: [ "USER" ]
    redirect_to: /login # If user not authorized, then return to this uri
```

Let's go in details!

### User provider

To help firewall to get current user, you need to give it a User provider.

This Brick provides you the interface `\Archict\Firewall\UserProvider`:

```php
<?php

namespace Archict\Firewall;

interface UserProvider 
{
    public function getCurrentUser(ServerRequestInterface $request): User;
}
```

The class you pass in the config must implement this interface.

`User` is an interface also provided by this Brick:

```php
<?php

namespace Archict\Firewall;

interface User
{
    /**
     * @return string[]
     */
    public function getRoles(): array;
}
```

### Access control

This config tag must contain an array of rules.

Each rule must have at least the `path` tag. This tag define the path to match, it can be a pattern with the same rules
as in [`Archict\router`](https://github.com/Archict/router).

Then you have the choice between let the firewall check if user can access the resource (the check is based on user
roles), or implement your own checker.

#### Firewall checker

If you choose to use firewall checker, then you must provide these 2 tags:

- `provider` ➡ One of the previously defined provider
- `roles` ➡ An array of string. User must have one these roles to access resource

Then you can define the behavior with one these rules (only one):

- `error` ➡ a HTTP error code to return
- `redirect_to` ➡ return a 301 response with the specified uri

#### Your own checker

To use your own checker, your class must implement this interface:

```php
<?php

namespace Archict\Firewall;

interface UserAccessChecker
{
    public function canUserAccessResource(ServerRequestInterface $request): bool;
}
```

This method returns `true` if user is authorized to see resource. It can throw an exception the same way as defined
in [`Archict\router`](https://github.com/Archict/router).

Then you can provide the class name to your rule with the tag `checker`:

```yaml
access_control:
  - path: some/path
    checker: \My\Own\Checker
```

You can also provide one of the behavior tag (see [Firewall checker](#firewall-checker)) in case your method
returns `false`.
