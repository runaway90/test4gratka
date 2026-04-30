# Photo Gallery Web

Główna aplikacja webowa do zarządzania galerią zdjęć zbudowana na Symfony Framework (PHP).

## Architektura

- **Framework**: Symfony 6.4
- **Język**: PHP 8.1
- **Baza danych**: PostgreSQL 15 (port 5432)
- **Web Port**: 8000 http://localhost:8000

## Szybki start

### Wymagania
- Docker
- Docker Compose

### Uruchomienie projektu

```bash
# Uruchom kontenery
docker compose up -d

# Instalacja zależności
docker compose exec web composer install

# Migracja bazy danych
docker compose exec web php bin/console doctrine:migrations:migrate --no-interaction

# Seedowanie bazy danych
docker compose exec web php bin/console app:seed

# Zatrzymaj kontenery
docker compose down

# Zatrzymaj i usuń dane (baza danych zostanie usunięta!)
docker compose down -v

# Wyczyść i przeinstaluj
docker compose exec web composer install
docker compose restart web
```