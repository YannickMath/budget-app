# Budget App

A personal budget management application to track income, expenses, and financial goals.

## What I'm Building

A full-stack budget management application with:
- **Backend API**: Symfony 7.4 with API Platform for RESTful endpoints
- **Frontend**: Modern JavaScript framework (to be decided)
- **Database**: PostgreSQL for data persistence
- **Email Testing**: Mailhog for development

## What I'm Doing

Setting up the project architecture and development environment:
- Configured Docker containers for all services
- Set up Symfony API with API Platform
- Added code quality tools (PHPStan, PHP CS Fixer)
- Configured database with migrations support
- Added Gedmo extensions for automatic timestamps

## Tech Stack

### Backend
- PHP 8.2+
- Symfony 7.4
- API Platform 4.2
- Doctrine ORM
- PostgreSQL 15

### Frontend
- To be determined

### Development Tools
- Docker & Docker Compose
- PHPStan (static analysis)
- PHP CS Fixer (code style)
- Symfony Maker Bundle
- Symfony Profiler

## Getting Started

### Prerequisites
- Docker
- Docker Compose

### Installation

1. Clone the repository
2. Copy environment files:
   ```bash
   cp .env.local .env
   ```
3. Start the services:
   ```bash
   docker-compose up -d
   ```

### Services

- API: http://localhost:8000
- Database: localhost:5432
- Mailhog UI: http://localhost:8025

## Project Structure

```
budget-app/
├── budget-app-api/     # Symfony API
├── budget-app-front/   # Frontend application (in progress)
├── docs/               # Documentation
└── docker-compose.yaml # Docker services configuration
```
