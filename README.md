# Laravel Ingest Demo

Demonstration of the [`zappzerapp/laravel-ingest`](https://github.com/zappzerapp/laravel-ingest) package for efficient CSV imports with performance benchmarking.

## Prerequisites

- Docker
- Docker Compose

## Quick Start

1. Start Docker container:
   ```bash
   docker compose up -d
   ```

2. Wait for the container to finish setup (PHP extensions and Composer install):
   ```bash
   # Check if ready
   docker compose exec app php -m | grep pdo_sqlite
   ```

3. Run migrations:
   ```bash
   docker compose exec app php artisan migrate:fresh --force
   ```

4. Generate test CSV files:
   ```bash
   docker compose exec app php artisan csv:generate
   ```

5. Run benchmark:
   ```bash
   docker compose exec app php artisan benchmark:ingest --clear
   ```

## Architecture

- **Docker**: PHP 8.3-CLI with SQLite
- **Database**: SQLite (file-based, no separate container needed)
- **Queue**: Sync driver (no queue worker needed)
- **Laravel**: Version 11.x
- **Package**: zappzerapp/laravel-ingest
- **Model**: Product (SKU, Name, Description, Price, Category, Stock)
- **Importer**: ProductImporter with UPDATE strategy

## CLI Commands

| Command | Description |
|---------|-------------|
| `csv:generate` | Generate test CSV files (100, 1K, 10K rows) |
| `ingest:run productimporter --file=csv/products_1000.csv` | Import CSV file |
| `benchmark:ingest --clear` | Benchmark with all CSV sizes |
| `ingest:list` | Show available importers |

## Benchmark Results (SQLite)

| CSV Size | Rows Imported | Duration (s) | Memory (MB) | Rows/Sec. |
|----------|---------------|--------------|-------------|-----------|
| 100      | 100           | 0.19         | 6.00        | 520       |
| 1,000    | 1,000         | 1.36         | 2.00        | 735       |
| 10,000   | 10,000        | 12.45        | 0.00        | 803       |
| 100,000  | 100,000       | 124.31       | 2.00        | 804       |

## Troubleshooting

**Problem**: Container won't start  
**Solution**: `docker compose down -v && docker compose up -d`

**Problem**: Permission denied with Artisan  
**Solution**: `docker compose exec app chmod -R 777 storage bootstrap/cache database`

**Problem**: SQLite database not found  
**Solution**: `docker compose exec app touch database/database.sqlite`

## GitHub Actions

This repository includes a GitHub Actions workflow (`.github/workflows/benchmark-docker.yml`) that automatically runs the benchmark on every pull request and push to main.

## License

MIT License
