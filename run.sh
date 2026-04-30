#!/bin/bash

find_projects() {
    find . -maxdepth 2 -name "docker-compose.yml" -not -path "./docker-compose.yml" | sed 's|/docker-compose.yml||' | sed 's|^\./||'
}

install_project() {
    local project=$1
    echo ""
    echo "📦 Instalacja $project..."

    if [ "$project" = "photogallery-api" ]; then
        echo "   → Uruchamianie kontenerów..."
        (cd "$project" && docker compose up -d)
        sleep 3
        echo "   → Instalacja zależności..."
        (cd "$project" && docker compose exec -T api mix deps.get)
        echo "   → Migracja bazy danych..."
        (cd "$project" && docker compose exec -T api mix ecto.migrate)
        echo "   → Seedowanie bazy danych..."
        (cd "$project" && docker compose exec -T api mix run priv/repo/seeds.exs)
        echo "   ✅ $project gotowy!"

    elif [ "$project" = "photogallery-web" ]; then
        echo "   → Uruchamianie kontenerów..."
        (cd "$project" && docker compose up -d)
        sleep 3
        echo "   → Instalacja zależności..."
        (cd "$project" && docker compose exec -T web composer install)
        echo "   → Migracja bazy danych..."
        (cd "$project" && docker compose exec -T web php bin/console doctrine:migrations:migrate --no-interaction)
        echo "   → Seedowanie bazy danych..."
        (cd "$project" && docker compose exec -T web php bin/console app:seed)
        echo "   ✅ $project gotowy!"
    fi
}

case "$1" in
    install)
        echo "🚀 Pełna instalacja wszystkich projektów od zera"
        echo ""

        for project in $(find_projects); do
            install_project "$project"
        done

        echo ""
        echo "✅ Wszystkie projekty zainstalowane!"
        echo ""
        echo "Dostęp do aplikacji:"
        echo "  - Web: http://localhost:8000"
        echo "  - API: http://localhost:4000"
        ;;

    start)
        echo "Start all projects in repo"

        for project in $(find_projects); do
            echo "   → Start $project..."
            (cd "$project" && docker compose up -d)
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
        echo "  ./run.sh start   - Uruchomienie wszystkich projektów"
        echo "  ./run.sh stop    - Zatrzymanie wszystkich projektów"
        echo "  ./run.sh clean   - Czyszczenie wszystkich projektów (usuwa bazy danych!)"
        ;;
esac