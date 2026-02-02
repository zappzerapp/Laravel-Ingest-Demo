# Laravel Ingest Demo

Demonstration of the `zappzerapp/laravel-ingest` package for efficient CSV imports with performance benchmarking.

## Prerequisites

- Docker
- Docker Compose

## Setup

1. Start Docker containers:
   ```bash
   docker compose up -d --build
   ```

2. Install Laravel:
   ```bash
   docker compose exec app composer create-project laravel/laravel . "11.*"
   ```

3. Install package:
   ```bash
   docker compose exec app composer require zappzerapp/laravel-ingest
   ```

4. Run migrations:
   ```bash
   docker compose exec app php artisan migrate
   ```

5. Generate test CSV files:
   ```bash
   docker compose exec app php artisan csv:generate
   ```

## Architecture

- **Docker**: PHP 8.3-FPM, Nginx, MariaDB 10.11
- **Laravel**: Version 11.x
- **Package**: zappzerapp/laravel-ingest
- **Model**: Product (SKU, Name, Description, Price, Category, Stock)
- **Importer**: ProductImporter with UPDATE strategy

## CLI Commands

| Command | Description |
|---------|-------------|
| `csv:generate` | Generate test CSV files (100, 1K, 10K, 100K rows) |
| `ingest:run product-importer --file=X` | Import CSV file |
| `benchmark:ingest --clear` | Benchmark with all CSV sizes |
| `ingest:list` | Show available importers |

## Benchmark Results

| CSV Size | Rows Imported | Duration (s) | Memory (MB) | Rows/Sec. |
|----------|---------------|--------------|-------------|-----------|
| 100      | 100           | 0.13         | 6.00        | 743       |
| 1,000    | 1,000         | 0.50         | 4.00        | 1,996     |
| 10,000   | 10,000        | 5.38         | 2.00        | 1,860     |
| 100,000  | 100,000       | 53.34        | 0.00        | 1,875     |


## Troubleshooting

**Problem**: Containers won't start  
**Solution**: `docker compose down -v && docker compose up -d --build`

**Problem**: Permission denied with Artisan  
**Solution**: `docker compose exec app chmod -R 777 storage bootstrap/cache`

## License

MIT License
