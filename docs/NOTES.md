# Notatki

## Przygotowanie do zadania

### Analiza i podział projektu
- Przeanalizowano strukturę projektu
- Podjęto decyzję o podziale projektu na dwie niezależne aplikacje (API i Web)
- Cel: wyeliminować zależności między projektami, aby móc niezależnie zarządzać wersjami i wprowadzać zmiany w jednym projekcie bez wpływu na drugi

### Mechanizmy uruchamiania
- Przygotowano oddzielne mechanizmy uruchamiania dla każdego projektu
- Stworzono skrypt `run.sh` do wspólnego uruchamiania wielu projektów
- Każdy projekt ma własny `docker-compose.yml` dla niezależności

### Dokumentacja
- Dodano osobne README dla każdego projektu z opisem technologii
- Centralne README z opisem całej architektury
- Celem jest ułatwienie wdrażania się w konkretny projekt bez zamieszania z drugim

## Gotowe do pracy nad zadaniem
  
*Dodałem gotowe rozwiązanie w run.sh dla pierwszej installacji oraz czyszczenia kontenerów i bazy danych*

TASK 1 
Naprawa blędów:
 ~~- SQL-injection w AuthController.php~~
- Inkapsulacja Auth i Profile, tworzenie servisów.
- Dodanie DI