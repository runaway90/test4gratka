#!/bin/bash

find_projects() {
    find . -maxdepth 2 -name "docker-compose.yml" -not -path "./docker-compose.yml" | sed 's|/docker-compose.yml||' | sed 's|^\./||'
}

wait_for_db() {
    local project=$1
    echo "   → Oczekiwanie na bazę danych..."
    until (cd "$project" && docker compose exec -T db pg_isready -U postgres) > /dev/null 2>&1; do
        sleep 1
    done
    echo "   → Baza danych gotowa!"
}

seed_if_empty() {
    local project=$1

    if [ "$project" = "photogallery-api" ]; then
        echo "   → Sprawdzanie danych w bazie API..."
        user_count=$(cd "$project" && docker compose exec -T api mix run -e "IO.puts(PhoenixApi.Repo.aggregate(PhoenixApi.Accounts.User, :count))" 2>/dev/null | tail -1 | tr -d '[:space:]')
        if [ "$user_count" = "0" ] || [ -z "$user_count" ]; then
            echo "   → Baza pusta – seedowanie..."
            (cd "$project" && docker compose exec -T api mix run priv/repo/seeds.exs)
        else
            echo "   → Dane już istnieją (${user_count} użytkowników) – pomijam seedy."
        fi

    elif [ "$project" = "photogallery-web" ]; then
        echo "   → Seedowanie bazy Web (idempotentne)..."
        (cd "$project" && docker compose exec -T web php bin/console app:seed)
    fi
}

start_project() {
    local project=$1
    echo ""
    echo "▶  Start $project..."
    (cd "$project" && docker compose up -d)
    wait_for_db "$project"
    seed_if_empty "$project"
    echo "   ✅ $project gotowy!"
}

case "$1" in
    install)
        echo "🚀 Pełna instalacja wszystkich projektów od zera"
        echo ""

        for project in $(find_projects); do
            echo ""
            echo "📦 Instalacja $project..."

            (cd "$project" && docker compose up -d)
            wait_for_db "$project"

            if [ "$project" = "photogallery-web" ]; then
                echo "   → Migracja bazy danych..."
                (cd "$project" && docker compose exec -T web php bin/console doctrine:migrations:migrate --no-interaction)
            fi

            seed_if_empty "$project"
            echo "   ✅ $project gotowy!"
        done

        echo ""
        echo "✅ Wszystkie projekty zainstalowane!"
        echo ""
        echo "Dostęp do aplikacji:"
        echo "  - Web: http://localhost:8000"
        echo "  - API: http://localhost:4000"
        ;;

    start)
        echo "🚀 Start wszystkich projektów"

        for project in $(find_projects); do
            start_project "$project"
        done

        echo ""
        echo "Done!"
        ;;

    stop)
        echo "Stop all projects in repo"

        for project in $(find_projects); do
            echo "   → Stop $project..."
            (cd "$project" && docker compose down)
        done

        echo ""
        echo "Done"
        ;;

    clean)
        echo "Czyść wszystkie projekty (usuń kontenery i bazy danych)"

        for project in $(find_projects); do
            echo "   → Czyszczenie $project..."
            (cd "$project" && docker compose down -v)
        done

        echo ""
        echo "Done"
        ;;

    *)
        echo "Usage:"
        echo "  ./run.sh install - Pełna instalacja wszystkich projektów (kontenery + bazy danych)"
        echo "  ./run.sh start   - Uruchomienie wszystkich projektów (auto-seed jeśli baza pusta)"
        echo "  ./run.sh stop    - Zatrzymanie wszystkich projektów"
        echo "  ./run.sh clean   - Czyszczenie wszystkich projektów (usuwa bazy danych!)"
        ;;
esac
