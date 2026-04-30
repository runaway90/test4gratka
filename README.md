# Photo Gallery - Galeria Zdjęć

Kompletny projekt galerii zdjęć składający się z dwóch niezależnych aplikacji:
- **API** - Phoenix Framework (Elixir)
- **Web** - Symfony Framework (PHP)

## Architektura

Ten projekt składa się z dwóch oddzielnych aplikacji z własnymi bazami danych:

- **Photo Gallery Web** (port 8000): Główna aplikacja internetowa
  - Baza danych: `photogallery_web` (PostgreSQL, port 5432)
  - Framework: Symfony 6.4 (PHP 8.1)

- **Photo Gallery API** (port 4000): Mikroserwis REST API
  - Baza danych: `photogallery_api` (PostgreSQL, port 5433)
  - Framework: Phoenix 1.7 (Elixir 1.15)

## Szybki start

### Wymagania
- Docker
- Docker Compose

### Uruchomienie wszystkich projektów

```bash
# Nadaj uprawnienia wykonywania (tylko za pierwszym razem)
chmod +x run.sh

# Uruchom wszystkie projekty
./run.sh start

# Zatrzymaj wszystkie projekty
./run.sh stop

# Konfiguracja API
cd photogallery-api
docker compose exec api mix ecto.migrate
docker compose exec api mix run priv/repo/seeds.exs
cd ..

# Konfiguracja Web
cd photogallery-web
docker compose exec web composer install
docker compose exec web php bin/console doctrine:migrations:migrate --no-interaction
docker compose exec web php bin/console app:seed
cd ..
```