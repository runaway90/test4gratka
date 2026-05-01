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
- Dodanie DI w kontrolerach
- Przeniosłem routes w plik yaml. wzystko w 1 mejscu
- Stworzyłem ExceptionListener
- Przepisanie authorizacji za pomocą JWT tokena(nie może on być zmienną w URL). Automatyczna instalacja.
- Zmiana ID na UUID jako unikalny identyfikator obiektów. Przepisanie seed-ów
- 
- 

### Stary System (`AuthToken` + Sesja)
*   **Sposób logowania:** Użytkownik musiał przejść pod specjalny link z tokenem w adresie URL.
*   **Przechowywanie stanu:** Po weryfikacji tokenu, serwer tworzył plik sesji na dysku. Każdy zalogowany użytkownik to osobny plik na serwerze.
*   **Weryfikacja:** Przy każdym kolejnym zapytaniu serwer musiał odczytać ID sesji z ciasteczka i znaleźć odpowiedni plik sesji na dysku, aby potwierdzić tożsamość użytkownika.
*   **Bezpieczeństwo:** Przekazywanie tokenu w adresie URL jest bardzo niebezpieczne, ponieważ zostaje on w historii przeglądarki i logach serwera.
*   **Skalowalność:** System jest zależny od stanu serwera (stateful). Przy wielu serwerach wymaga to dodatkowej konfiguracji ("lepkie sesje") lub wspólnej bazy dla sesji.

### Nowy System (JWT w Ciasteczku `HttpOnly`)
*   **Sposób logowania:** Użytkownik loguje się za pomocą standardowego formularza z loginem i hasłem.
*   **Przechowywanie stanu:** Serwer jest bezstanowy (stateless). Wszystkie potrzebne informacje (kto jest zalogowany, jakie ma role, kiedy wygasa sesja) są zapisane w samym tokenie JWT, który znajduje się w bezpiecznym ciasteczku `HttpOnly` po stronie klienta.
*   **Weryfikacja:** Przy każdym zapytaniu serwer jedynie sprawdza kryptograficzny podpis tokenu. Nie musi niczego szukać w bazie danych ani na dysku, aby potwierdzić tożsamość.
*   **Bezpieczeństwo:** Token jest przesyłany w ciasteczku `HttpOnly`, co oznacza, że nie można go odczytać za pomocą JavaScriptu (ochrona przed atakami XSS).
*   **Skalowalność:**  Każdy serwer może obsłużyć każde zapytanie, ponieważ nie przechowuje żadnego stanu. To znacznie ułatwia rozbudowę aplikacji.
*   **Gotowość na przyszłość:** Architektura jest gotowa do obsługi API lub aplikacji mobilnych bez żadnych zmian w logice uwierzytelniania.


### Dlaczego Używam UUID Zamiast Zwykłego ID?
*   **Bezpieczeństwo i Ukrywanie Informacji**
    Twoje adresy URL nie zdradzają już wewnętrznej struktury ani liczby danych w systemie. Zamiast `/zdjecia/123`, masz `/zdjecia/a1b2c3d4-e5f6...`. Nikt nie jest w stanie odgadnąć ID następnego zdjęcia ani policzyć, ilu masz użytkowników, po prostu zmieniając cyfrę w adresie.
*   **Niezależność od Bazy Danych**
    Możesz wygenerować unikalne ID dla nowego obiektu w kodzie PHP, jeszcze *przed* zapisaniem go do bazy. Daje to ogromną elastyczność. Obiekt ma swoje ID od samego początku istnienia, co ułatwia pracę z bardziej złożonymi systemami, np. gdy trzeba przekazać go do innego serwisu przed ostatecznym zapisem.
*   **Brak Konfliktów i Skalowalność**
    Prawdopodobieństwo, że dwa różne serwery wygenerują ten sam UUID, jest praktycznie zerowe. To kluczowe, jeśli aplikacja będzie rozwijana i w przyszłości może działać na wielu serwerach jednocześnie. Możesz bezpiecznie łączyć dane z różnych środowisk (np. deweloperskiego i produkcyjnego) bez obawy o konflikt ID.