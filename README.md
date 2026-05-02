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
  - Baza danych: `photogallery_api` (PostgreSQL, port 5432)
  - Framework: Phoenix 1.7 (Elixir 1.15)

## Szybki start

### Wymagania
- Docker
- Docker Compose

### Uruchomienie wszystkich projektów

```bash

# Pełna instalacja od zera (kontenerery + zależności + migracje + seedy)
./run.sh install
# Szybki start (tylko uruchomienie kontenerów)
./run.sh start
# Zatrzymanie projektów
./run.sh stop
# Czyszczenie (usuwa kontenery i bazy danych)
./run.sh clean

# Konfiguracja API
cd photogallery-api
docker compose exec api mix ecto.migrate
docker compose exec api mix run priv/repo/seeds.exs
cd ..
# Logi API
docker compose exec api cat var/log/dev.log
#Testy API
docker compose exec -e MIX_ENV=test api mix test

# Konfiguracja Web
cd photogallery-web
docker compose exec web composer install
docker compose exec web php bin/console doctrine:migrations:migrate --no-interaction
cd ..
# Logi web
docker compose exec web cat var/log/dev.log
# Testy web
docker compose exec web php vendor/bin/phpunit

```