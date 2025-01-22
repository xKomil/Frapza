# Analiza aplikacji restauracji FRAPZA
## Programowanie serwera baz danych
### Rok akademicki 2024/2025

#### Autorzy:
- Kamil Stępień 406359
- Kamil Płocki 406296

## Spis treści
1. [Wstęp](#wstęp)
2. [Analiza funkcjonalności](#analiza-funkcjonalności)
    1. [Użytkownik niezalogowany](#użytkownik-niezalogowany)
    2. [Użytkownik zalogowany](#użytkownik-zalogowany)
    3. [Pracownik](#pracownik)
    4. [Administrator](#administrator)
3. [Baza danych](#baza-danych)
4. [Wykorzystane technologie i narzędzia](#wykorzystane-technologie-i-narzędzia)
5. [Instalacja](#instalacja)

---

## Wstęp
Aplikacja restauracji FRAPZA to nowoczesna platforma stworzona z myślą o wygodzie gości i sprawnym zarządzaniu restauracją. Dzięki niej zarówno odwiedzający stronę, jak i zarejestrowani użytkownicy mają dostęp do szerokiego wachlarza funkcji, które umożliwiają komfortowe korzystanie z usług lokalu. Dodatkowo aplikacja jest narzędziem wspierającym pracowników oraz administratorów w codziennych obowiązkach, takich jak zarządzanie rezerwacjami, menu czy komunikacją z gośćmi.

Zastosowanie technologii umożliwia użytkownikom łatwą rezerwację stolików, przeglądanie pełnego menu, a także korzystanie z innych usług, jak np. kontakt z obsługą. System został zaprojektowany w sposób elastyczny, aby odpowiedzieć na różne potrzeby różnych grup użytkowników – od gości restauracji po osoby zarządzające lokalu.

Dzięki czterem poziomom użytkowników – niezalogowanym, zalogowanym, pracownikom oraz administratorom – FRAPZA stwarza szeroką gamę możliwości. W zależności od posiadanego statusu, użytkownicy mogą korzystać z funkcji dostosowanych do ich roli, co przekłada się na sprawną obsługę i optymalizację procesów zarządzania.

Celem aplikacji jest nie tylko ułatwienie codziennego zarządzania restauracją, ale także poprawienie komfortu gości, zapewniając im szybki dostęp do istotnych informacji oraz możliwość łatwego rezerwowania stolików, sali na imprezy, a także bezpośredniego kontaktu z pracownikami.

Poniższa analiza przedstawia pełny obraz funkcji i ról dostępnych w aplikacji FRAPZA, umożliwiając zrozumienie jak poszczególne grupy użytkowników mogą korzystać z platformy oraz jakie mają dostępne opcje w ramach swoich uprawnień.

---

## Analiza funkcjonalności

### Użytkownik niezalogowany
Osoba, która odwiedza stronę bez logowania, może:  
- **Przeglądać stronę** – pełny dostęp do wszystkich ogólnodostępnych treści:
    - Godziny otwarcia  
    - Menu  
    - Ostatnie opinie  
    - Formularz rezerwacyjny (z możliwością rezerwacji tylko stolika)
- **Przeglądać pełne menu** – oglądać aktualne pozycje w menu, w tym ich ceny i opisy.  
- **Zarezerwować stolik** – korzystając z formularza rezerwacji, niezalogowany użytkownik może wybrać datę, godzinę i liczbę osób (1-6).  
- **Czytać ostatnie opinie użytkowników.**  
- **Wysyłać wiadomości do pracowników** – formularz kontaktowy zadać pytania dotyczące menu, dostępności, wydarzeń itp.

### Użytkownik zalogowany
Osoba, która założyła konto i zalogowała się w aplikacji, ma dostęp do wszystkich funkcji użytkownika niezalogowanego, a dodatkowo może:  
- **Zarezerwować całą salę** po wybraniu daty, godziny i menu.  
Użytkownik zalogowany otrzymuje dostęp do zakładki Profil w której ma możliwość:
- **Przeglądać historię rezerwacji** – dostęp do listy wcześniejszych i nadchodzących rezerwacji, dodatkowo w przypadku rezerwacji sali zobaczyć szacunkową cenę.
- **Anulować rezerwację.**
- **Zarządzać swoim profilem** – możliwość zmiany swoich danych takich jak: e-mail, nr telefonu, zdjęcia profilowego.

### Pracownik
Osoby zatrudnione w firmie, które mają uprawnienia do obsługi systemu od strony zarządzania treściami i komunikacji.  
Funkcje:
- **Zarządzanie rezerwacjami sal/stolików**:
    - Przeglądanie rezerwacji
    - Potwierdzenie rezerwacji
    - Anulowanie rezerwacji (Informacja o anulowaniu rezerwacji wysyła również maila do użytkownika)
    - Przeglądanie menu rezerwacji (w przypadku sali)
- **Obsługa wiadomości użytkowników**:
    - Przeglądanie wiadomości
    - Odpisywanie na wiadomości użytkownika
    - Zmiana statusu wiadomości na „Przeczytana”

### Administrator
Osoba odpowiedzialna za zarządzanie użytkownikami, pobieraniem statystyk, zarządzaniem menu, najlepiej gdyby tę rolę pełniła wysoka postawiona osoba w hierarchii firmy, np. menadżer restauracji.  
Funkcje:
- **Zarządzanie kontami użytkowników i pracowników**:
    - Dodawanie konta
    - Usuwanie konta
    - Zmiana roli na user/pracownik/admin
    - Zmiana danych użytkownika
- **Aktualizacja treści**:
    - Edytowanie menu
    - Dodawanie nowych pozycji
    - Zmienianie ich opisów i cen
    - Wyszukiwanie pozycji w menu
- **Generowanie raportu uwzględniając ramy czasowe**:
    - Liczba użytkowników
    - Liczba wiadomości
    - Liczba opinii
    - Liczba rezerwacji stolików
    - Liczba rezerwacji sal
    - Zarobki stolików
    - Zarobki sal
    - Łączna liczba rezerwacji
    - Łączne zarobki

---

## Baza danych

W celu sprawnego działania aplikacji, niezbędne jest odpowiednie zaprojektowanie bazy danych. Baza danych w aplikacji restauracji FRAPZA służy do przechowywania danych o użytkownikach, rezerwacjach, wiadomościach oraz menu. Poniżej przedstawiona jest struktura bazy danych oraz kluczowe tabele.

### Struktura bazy danych
1. **Tabela `users`**
   - `user_id` (INT, AUTO_INCREMENT, PRIMARY KEY) – Unikalny identyfikator użytkownika.
   - `name` (VARCHAR) – Imię użytkownika.
   - `email` (VARCHAR) – Adres e-mail użytkownika.
   - `password` (VARCHAR) – Hasło użytkownika (zaszyfrowane).
   - `role` (ENUM: 'guest', 'user', 'employee', 'admin') – Rola użytkownika.
   - `phone_number` (VARCHAR) – Numer telefonu użytkownika (opcjonalnie).
   - `profile_picture` (VARCHAR) – Ścieżka do zdjęcia profilowego.

2. **Tabela `reservations`**
   - `reservation_id` (INT, AUTO_INCREMENT, PRIMARY KEY) – Unikalny identyfikator rezerwacji.
   - `user_id` (INT, FOREIGN KEY) – Identyfikator użytkownika (klucz obcy z tabeli `users`).
   - `table_id` (INT, FOREIGN KEY) – Identyfikator stolika.
   - `reservation_date` (DATETIME) – Data i godzina rezerwacji.
   - `number_of_people` (INT) – Liczba osób.
   - `status` (ENUM: 'pending', 'confirmed', 'cancelled') – Status rezerwacji.

3. **Tabela `messages`**
   - `message_id` (INT, AUTO_INCREMENT, PRIMARY KEY) – Unikalny identyfikator wiadomości.
   - `user_id` (INT, FOREIGN KEY) – Identyfikator użytkownika (klucz obcy z tabeli `users`).
   - `message_content` (TEXT) – Treść wiadomości.
   - `status` (ENUM: 'unread', 'read') – Status wiadomości (przeczytana/nieprzeczytana).
   - `created_at` (DATETIME) – Data i godzina wysłania wiadomości.

4. **Tabela `menu`**
   - `menu_item_id` (INT, AUTO_INCREMENT, PRIMARY KEY) – Unikalny identyfikator pozycji w menu.
   - `name` (VARCHAR) – Nazwa pozycji.
   - `description` (TEXT) – Opis pozycji.
   - `price` (DECIMAL) – Cena pozycji.
   - `category` (VARCHAR) – Kategoria (np. napój, danie główne).
   - `available` (BOOLEAN) – Dostępność pozycji w menu.

---

## Wykorzystane technologie i narzędzia

### Backend
- **PHP**: Odpowiada za komunikację z bazą danych MySQL oraz logikę serwera.

### Frontend
- **HTML, CSS**: Budowa i stylowanie interfejsu użytkownika.
- **JavaScript**: Obsługa komponentów oraz dynamiczne elementy strony.

### Baza danych
- **MySQL**: Przechowywanie danych aplikacji, takich jak użytkownicy, rezerwacje, wiadomości.

---

## Instalacja

### Wymagania wstępne
1. **Serwer WWW**: Zainstaluj i skonfiguruj środowisko serwera, np. XAMPP lub WAMP.
2. **PHP**: Wersja minimum 7.4 lub nowsza.
3. **MySQL**: Zainstalowany serwer MySQL (często jest w pakiecie z XAMPP/WAMP).
4. **Przeglądarka internetowa**: Do testowania aplikacji.
5. **Edytor kodu**: Zalecane Visual Studio Code.

### Kroki instalacji
#### 1. Pobranie plików aplikacji
- Pobierz plik `public_html` (z repozytorium GitHub: https://github.com/xKomil/Frapza).
- Skopiuj folder `public_html` i wstaw go do katalogu `htdocs` (dla XAMPP) lub `www` (dla WAMP).

#### 2. Konfiguracja bazy danych
- Uruchom serwer MySQL za pomocą panelu sterowania XAMPP/WAMP.
- Otwórz narzędzie do zarządzania bazami danych, np. phpMyAdmin.
- Utwórz nową bazę danych:
    - Nazwa bazy: `2025_kamil321`
- Zaimportuj plik SQL do bazy danych:
    - Otwórz zakładkę `Import` w phpMyAdmin.
    - Wybierz i zaimportuj plik `2025_kamil321.sql` znajdujący się w folderze projektu pobranego z GitHub.
    - Kliknij `Importuj`.

#### 3. Konfiguracja połączenia z bazą danych
- Otwórz plik `db.php` w folderze projektu.
- Upewnij się, że dane połączenia są poprawne:

```php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "2025_kamil321";
```
#### 4. Uruchomienie aplikacji
Otwórz przeglądarkę internetową i wejdź pod adres localhost/Frapza lub localhost/Frapza/public_html.