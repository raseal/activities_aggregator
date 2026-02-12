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

## 📌 Quick Reference Commands

```bash
# Enter container
make shell

# Run console command inside container
docker compose exec php_container /app/apps/SymfonyClient/bin/console <command>

# Run tests
make test

# Check container status
make status
```

---
**Last Updated**: February 2026
