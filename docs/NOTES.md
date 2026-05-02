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

# TASK 1. Naprawa blędów:

 ~~- SQL-injection w AuthController.php~~
- Inkapsulacja Auth i Profile, tworzenie servisów.
- Dodanie DI w kontrolerach
- Przeniosłem routes w plik yaml. wzystko w 1 mejscu
- Stworzyłem ExceptionListener
- Przepisanie authorizacji za pomocą JWT tokena(nie może on być zmienną w URL). Automatyczna instalacja.
- Zmiana ID na UUID jako unikalny identyfikator obiektów. Przepisanie seed-ów
- poprawa działania frontu
- fix like i unlike zdjęc

# Dlaczego przepisałem system authoryzacji?
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


# Dlaczego Używam UUID Zamiast Zwykłego ID?
*   **Bezpieczeństwo i Ukrywanie Informacji**
    Twoje adresy URL nie zdradzają już wewnętrznej struktury ani liczby danych w systemie. Zamiast `/zdjecia/123`, masz `/zdjecia/a1b2c3d4-e5f6...`. Nikt nie jest w stanie odgadnąć ID następnego zdjęcia ani policzyć, ilu masz użytkowników, po prostu zmieniając cyfrę w adresie.
*   **Niezależność od Bazy Danych**
    Możesz wygenerować unikalne ID dla nowego obiektu w kodzie PHP, jeszcze *przed* zapisaniem go do bazy. Daje to ogromną elastyczność. Obiekt ma swoje ID od samego początku istnienia, co ułatwia pracę z bardziej złożonymi systemami, np. gdy trzeba przekazać go do innego serwisu przed ostatecznym zapisem.
*   **Brak Konfliktów i Skalowalność**
    Prawdopodobieństwo, że dwa różne serwery wygenerują ten sam UUID, jest praktycznie zerowe. To kluczowe, jeśli aplikacja będzie rozwijana i w przyszłości może działać na wielu serwerach jednocześnie. Możesz bezpiecznie łączyć dane z różnych środowisk (np. deweloperskiego i produkcyjnego) bez obawy o konflikt ID.

.
.
.

# Dlaczego nie używam interfejsów w tym zadaniu testowym

## Szczegółowe wyjaśnienie

### 1. Zasada YAGNI (You Ain't Gonna Need It)

Najważniejszą zasadą, którą się tutaj kieruje, jest YAGNI ("Nie będziesz tego potrzebować"). Interfejsy wprowadzam wtedy, gdy potrzebuje zdefiniować kontrakt, który może mieć wiele różnych implementacji. W naszym projekcie przykladowo:
- `LikeService` ma jedną, konkretną logikę.
- `LikeRepository` jest ściśle powiązany z Doctrine.
Nie ma obecnie potrzeby tworzenia alternatywnych wersji tych klas, więc interfejsy nie są konieczne.
### 2. Prostota i czytelność
Kod bez dodatkowych warstw abstrakcji jest prostszy do czytania i nawigacji. Kiedy klikasz na nazwę serwisu, od razu przechodzisz do jego implementacji, a nie do pliku interfejsu.
### 3. Testowalność
Nowoczesne narzędzia do testowania (np. PHPUnit z Mockery) pozwalają na łatwe tworzenie "zaślepek" (mocków) dla konkretnych klas. Oznacza to, że **nie potrzebujemy interfejsów, aby móc efektywnie testować nasz kod**.

## Kiedy interfejsy byłyby uzasadnione?
Warto byłoby je dodać, gdybyśmy w przyszłości stanęli przed jedną z poniższych sytuacji:
-   **Wiele implementacji:** Gdybyśmy chcieli, aby lajki mogły być przechowywane w różny sposób (np. w bazie danych PostgreSQL, w Redis, a dla testów w pamięci). Interfejs `LikeRepositoryInterface` pozwoliłby na łatwą zamianę tych implementacji.
-   **Moduł jako biblioteka:** Gdyby system lajków miał stać się oddzielną biblioteką, używaną w wielu różnych projektach (potencjalnie nie tylko opartych o Symfony).
-   **Złożona logika biznesowa:** Przy bardzo skomplikowanych domenach, gdzie chcemy całkowicie oddzielić logikę biznesową od warstwy dostępu do danych (frameworka).
## Wniosek
Brak interfejsów(w summie i trait-ów) w tym projekcie to **świadoma decyzja**, która stawia na prostotę i pragmatyzm. Kod jest łatwiejszy w utrzymaniu i spełnia obecne wymagania. Jeśli w przyszłości projekt będzie się rozwijał w kierunku, który uzasadni wprowadzenie interfejsów, będzie można je dodać w ramach refaktoryzacji.

---

# TASK 2. Import zdjęć z PhoenixAPI

### Nagłówek uwierzytelniający
PhoenixAPI oczekuje nagłówka `access-token`, a nie standardowego `Authorization: Bearer`. To ważna różnica — błędny nagłówek skutkuje odpowiedzią 401 bez żadnej wskazówki w treści błędu. Zdecydowałem się trzymać się kontraktu zdefiniowanego po stronie API i nie zmieniać PhoenixAPI, ponieważ mogłoby to złamać innych klientów korzystających z tego samego endpointu.

### Deduplikacja zdjęć
Przy imporcie sprawdzam czy zdjęcie o danym `imageUrl` już istnieje w bazie (`findOneBy(['imageUrl' => ...])`). Dzięki temu wielokrotne naciśnięcie "Importuj" nie tworzy duplikatów. Alternatywą byłoby dodanie unikalnego indeksu na kolumnie `image_url` w bazie danych — to rozwiązanie byłoby bardziej niezawodne (ochrona na poziomie bazy), ale wymagałoby dodatkowej migracji.

### Konfiguracja URL PhoenixAPI
URL do PhoenixAPI jest przechowywany jako parametr Symfony oparty na zmiennej środowiskowej `PHOENIX_API_URL` z wartością domyślną. Pozwala to na łatwą zmianę adresu bez modyfikacji kodu (np. dla środowiska produkcyjnego). W `docker-compose.yml` dodałem `extra_hosts: host-gateway`, ponieważ dwa projekty działają w oddzielnych sieciach Docker i kontener web musi dotrzeć do API przez hosta.

### Komunikacja błędów
W przypadku błędnego tokenu lub niedostępności API użytkownik otrzymuje czytelny komunikat flash zamiast surowego wyjątku. Kod HTTP z PhoenixAPI jest przekazywany w treści komunikatu, co ułatwia diagnozę problemu.

---

# TASK 3. Filtrowanie zdjęć na stronie głównej

### QueryBuilder z dynamicznymi warunkami
Zamiast wielu osobnych metod (`findByLocation`, `findByCamera` itd.) używam jednej metody z tablicą filtrów i dynamicznie dodawanymi klauzulami `andWhere`. Puste pola są pomijane przez `array_filter()` w kontrolerze — filtrowanie jest addytywne (AND). Takie podejście jest łatwe do rozszerzenia o nowe pola bez zmiany interfejsu metody.

### Wyszukiwanie tekstowe przez LIKE
Dla pól tekstowych (`location`, `camera`, `description`, `username`) używam `LIKE %value%` — częściowe dopasowanie jest bardziej użyteczne niż dokładne. Użytkownik szukający `"Canon"` znajdzie zarówno `"Canon EOS R5"` jak i `"Canon 5D"`.

### Filtrowanie po dacie
Pole `taken_at` jest typem `datetime_immutable`, więc filtrowanie po dacie wymaga zakresu: `>= 2024-01-01 00:00:00 AND < 2024-01-02 00:00:00`. Użytkownik podaje tylko datę (input `type="date"`), a zakres jest obliczany automatycznie w repozytorium.

### Unikanie N+1
Przy braku filtrów używam istniejącego `findAllWithUsers()` (JOIN + SELECT user w jednym zapytaniu). `findByFilters()` robi to samo przez `leftJoin` z `addSelect('u')` — niezależnie od filtrów dane użytkownika są ładowane jednym zapytaniem.

---