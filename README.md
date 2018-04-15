# Achievement Service

This PoC project aims to serve a full backend for any service which requires
achievement handling, such as social media services or game servers.
The goal is to provide REST API and rabbitmq consumers as means of exchanging achievement progress data.

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

## Project Structure

Main app includes one controller and a single endpoint:

```
POST /events
```

Best way to see how it works is to check the EventControllerTest file.

### AchievementBundle

This is where all the core functionality is kept.
When the bundle reaches maturity and most use-cases are covered, it will be moved out to a separate repository.

Achievement themselves are not stored by any part of this bundle.
It's up to the implementation to handle that.

##### Implementing Achievement Handlers

Achievement handlers are the most important aspect of the bundle, as
they process the achievement logic.

Every handler needs to implement `HandlerInterface`, but there
are 2 predefined handlers which should cover most of the needs:

`InstantHandler` handles achievements which have very easy conditions, such as
"login for the first time", "create a group", etc.
Generally speaking, it will handle every scenario where one event is  sufficent to
decide whether the achievement's conditions have been met or not.

`PersistingHandler` on the other hand persists it's data over time, so more complex
achievements can be processed. For example "make your 100th post" (needs to store overall post count),
or "kill 5 enemies within one minute".

##### Handler Processor

Every handler is automatically registered and processed by the `Processor` service.

`Processor` operates solely on events. It doesn't return any data and doesn't throw any exceptions.

##### Events

There are 3 possible events generated in the process:
`AchievementCompletedEvent`,
`AchievementProgressedEvent` (both self-explanatory), and a `ProgressUpdateUnhandledEvent` which
means the event was not processed for some reason.

##### Persistance

Both `Processor` and `PersistingHandler` rely on persistance objects, and
by default both use `MetadataCacheStorage` to persist any data.
`MetadataCacheStorage` uses symfony `CacheAdapters` for this purpose, however
every effort was made for those implementations to be swapped out easily.