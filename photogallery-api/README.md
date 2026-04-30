# Photo Gallery API

REST API do zarządzania zdjęciami użytkowników zbudowane na Phoenix Framework (Elixir).

## Architektura

- **Framework**: Phoenix 1.7
- **Język**: Elixir 1.15
- **Baza danych**: PostgreSQL 15 (port 5432)
- **API Port**: 4000 http://localhost:4000

## Szybki start

### Wymagania
- Docker
- Docker Compose

### Uruchomienie projektu

```bash
# Uruchom kontenery
docker compose up -d

# Seedowanie bazy danych
docker compose exec api mix run priv/repo/seeds.exs

# zależności
docker compose exec api mix deps.get

# Migracja bazy danych
docker compose exec api mix ecto.migrate

# Zatrzymaj kontenery
docker compose down

# Zatrzymaj i usuń dane (baza danych zostanie usunięta!)
docker compose down -v

# Wyczyść i przeinstaluj
docker compose exec api mix deps.clean --all
docker compose exec api mix deps.get
docker compose restart api

# Testy
docker compose exec api mix test

```
