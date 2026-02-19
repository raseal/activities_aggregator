# 🤖 AI Agent Instructions

> ⚠️ **IMPORTANT**: All AI assistants (GitHub Copilot, JetBrains AI, etc.) MUST read and follow these instructions for every interaction.

## 📋 Project Rules

### 1. Documentation
- ❌ **DO NOT** create or edit any README files
- ✅ Only update technical documentation when explicitly requested

### 2. Docker Environment
- 🐳 This project runs inside a **Docker container**
- 📍 Console location: `/app/apps/SymfonyClient/bin/console`
- 🔧 All commands must be run inside the container using:
  - `docker compose exec php_container <command>`
  - Or `make shell` to enter the container

### 3. Code Generation
- ❌ **DO NOT** generate code unless **explicitly requested**
- ✅ Explain solutions and approaches first
- ✅ Only implement when user confirms

### 4. Project Context
- 🏗️ **Architecture**: Symfony 8.0 with Hexagonal Architecture
- 📦 **Stack**: PHP, Symfony, API Platform, Docker
- 🎯 **Purpose**: Activities aggregator from external providers

### 5. Test Generation
- ❌ **DO NOT** add comments inside test methods or test classes.
- ❌ **DO NOT** fix deprecations unless explicitly asked.
- ❌ **DO NOT** use `test` prefix in method names.
- ✅ Use AAA (Arrange, Act, Assert) pattern. Separate each section with a blank line.
- ✅ Use `#[Test]` attribute for test methods.
- ✅ Use camelCase for test method names (e.g. `createUser()`, `updateUserEmail()`).
- ✅ Use `PHPUnit\Framework\TestCase` as base class for unit tests.
- ✅ Use data providers to reduce test method duplication.
- ✅ Run only the test you are working on using `--filter`:

```bash
docker compose exec php_container vendor/bin/phpunit --filter MyTestClassName
```

**Unit test example (with data provider):**
```php
<?php
declare(strict_types=1);

namespace Test\Unit\Ingestor\Application;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class IngestEventFromExternalProviderTest extends TestCase
{
    #[Test]
    #[DataProvider('provideValidEvents')]
    public function ingestEvent(string $type, string $expectedResult): void
    {
        $handler = new IngestEventFromExternalProvider();

        $result = $handler->handle(new IngestEventCommand($type));

        self::assertSame($expectedResult, $result);
    }

    public static function provideValidEvents(): array
    {
        return [
            'run event'  => ['run',  'ingested:run'],
            'ride event' => ['ride', 'ingested:ride'],
        ];
    }
}
```

## 📌 Quick Reference Commands

```bash
# Enter container
make shell

# Run console command inside container
docker compose exec php_container /app/apps/SymfonyClient/bin/console <command>

# Run tests(all suites)
make test

# Run only integration tests
make test-integration

# Run only unit tests
make test-unit

# Check container status
make status
```

---
**Last Updated**: February 2026
