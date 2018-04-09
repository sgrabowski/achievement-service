# Achievement Service

This PoC project aims to serve a full backend for any service which requires
achievement handling, such as social media pages or game servers.
The goal is to provide REST API and rabbitmq consumers as means of receiving achievement progress data.

## Getting Started

As of now, this is a standard Symfony 4 project without any 3rd party dependencies.

### Prerequisites

```
PHP >7.1.3
```

## Running the tests

Main suite:
```
bin/phpunit
```

AchievementBundle:
```
bin/phpunit src/AchievementBundle/Tests
```