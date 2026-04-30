#!/bin/bash

find_projects() {
    find . -maxdepth 2 -name "docker-compose.yml" -not -path "./docker-compose.yml" | sed 's|/docker-compose.yml||' | sed 's|^\./||'
}

case "$1" in
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

    *)
        echo "Usage:"
        echo "  ./run.sh start   - Start all projects"
        echo "  ./run.sh stop    - Stop all projects"
        ;;
esac