<?php
require_once 'database/db.php';
session_start();

//Wylogowywanie
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['logout']) && $_POST['logout'] === "true") {
    // Zakończenie sesji
    session_start();
    session_destroy();
    
    // Przekierowanie na stronę logowania
    header("Location: logowanie.php");
    exit();
}

// Sprawdzenie, czy użytkownik jest zalogowany i ma rolę admina
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    // Jeśli użytkownik nie jest administratorem, przekierowanie na stronę główną
    header("Location: index.php");
    exit();
}

//------------------- Statystyki ---------------------
// Zapytanie do pobrania unikalnych kategorii potraw
$query = "SELECT DISTINCT Kategoria FROM Potrawy";
$stmt = $conn->prepare($query);
$stmt->execute();
$result = $stmt->get_result();

$categories = [];
while ($row = $result->fetch_assoc()) {
    $categories[] = $row['Kategoria'];
}
$stmt->close();


$userCountQuery = "SELECT COUNT(*) as count FROM Uzytkownicy";
$userCountResult = mysqli_query($conn, $userCountQuery);
$userCount = mysqli_fetch_assoc($userCountResult)['count'];

// Rezerwacje stolików
$reservationStolikCountQuery = "SELECT COUNT(*) as count FROM RezerwacjeStoliki";
$reservationStolikCountResult = mysqli_query($conn, $reservationStolikCountQuery);
$reservationStolikCount = mysqli_fetch_assoc($reservationStolikCountResult)['count'];

// Rezerwacje sal
$reservationSalaCountQuery = "SELECT COUNT(*) as count FROM RezerwacjeSale";
$reservationSalaCountResult = mysqli_query($conn, $reservationSalaCountQuery);
$reservationSalaCount = mysqli_fetch_assoc($reservationSalaCountResult)['count'];

// Łączna liczba rezerwacji (stoliki + sale)
$reservationCount = $reservationStolikCount + $reservationSalaCount;

$messageCountQuery = "SELECT COUNT(*) as count FROM Wiadomosci";
$messageCountResult = mysqli_query($conn, $messageCountQuery);
$messageCount = mysqli_fetch_assoc($messageCountResult)['count'];

$dishCountQuery = "SELECT COUNT(*) as count FROM Potrawy";
$dishCountResult = mysqli_query($conn, $dishCountQuery);
$dishCount = mysqli_fetch_assoc($dishCountResult)['count'];

// Pobranie aktualnego roku i miesiąca
$currentYear = date('Y'); // Bieżący rok
$currentMonth = date('m'); // Bieżący miesiąc
// Zapytanie SQL, uwzględniające obecny miesiąc i rok
$query = "
    SELECT 
        -- Miesięczny przychód z rezerwacji sali
        (SELECT 
            SUM(
                (p1.Cena + p2.Cena + p3.Cena) * r.LiczbaOsob + 
                IF(r.LiczbaOsob < 42, (42 - r.LiczbaOsob) * 50, 0)
            ) 
         FROM 
            RezerwacjeSale r
         INNER JOIN 
            Potrawy p1 ON r.Przystawka = p1.PotrawaId  -- Łączenie po PotrawaId
         INNER JOIN 
            Potrawy p2 ON r.DanieGlowne = p2.PotrawaId  -- Łączenie po PotrawaId
         INNER JOIN 
            Potrawy p3 ON r.Ciasto = p3.PotrawaId  -- Łączenie po PotrawaId
         WHERE 
            DATE_FORMAT(r.DataRezerwacji, '%Y-%m') = '$currentYear-$currentMonth'
        ) AS monthlyEarningsSala,

        -- Miesięczny przychód z rezerwacji stolików
        (SELECT 
            SUM(IloscOsob * 50) 
         FROM 
            RezerwacjeStoliki
         WHERE 
            DATE_FORMAT(DataRezerwacji, '%Y-%m') = '$currentYear-$currentMonth'
        ) AS monthlyEarningsStolik,

        -- Całkowity przychód z rezerwacji sali
        (SELECT 
            SUM(
                (p1.Cena + p2.Cena + p3.Cena) * r.LiczbaOsob + 
                IF(r.LiczbaOsob < 42, (42 - r.LiczbaOsob) * 50, 0)
            ) 
         FROM 
            RezerwacjeSale r
         INNER JOIN 
            Potrawy p1 ON r.Przystawka = p1.PotrawaId  -- Łączenie po PotrawaId
         INNER JOIN 
            Potrawy p2 ON r.DanieGlowne = p2.PotrawaId  -- Łączenie po PotrawaId
         INNER JOIN 
            Potrawy p3 ON r.Ciasto = p3.PotrawaId  -- Łączenie po PotrawaId
        ) AS totalEarningsSala,

        -- Całkowity przychód z rezerwacji stolików
        (SELECT 
            SUM(IloscOsob * 50) 
         FROM 
            RezerwacjeStoliki
        ) AS totalEarningsStolik,

        -- Łączny miesięczny przychód z sali i stolików
        (
            (SELECT 
                SUM(
                    (p1.Cena + p2.Cena + p3.Cena) * r.LiczbaOsob + 
                    IF(r.LiczbaOsob < 42, (42 - r.LiczbaOsob) * 50, 0)
                ) 
             FROM 
                RezerwacjeSale r
             INNER JOIN 
                Potrawy p1 ON r.Przystawka = p1.PotrawaId  -- Łączenie po PotrawaId
             INNER JOIN 
                Potrawy p2 ON r.DanieGlowne = p2.PotrawaId  -- Łączenie po PotrawaId
             INNER JOIN 
                Potrawy p3 ON r.Ciasto = p3.PotrawaId  -- Łączenie po PotrawaId
             WHERE 
                DATE_FORMAT(r.DataRezerwacji, '%Y-%m') = '$currentYear-$currentMonth'
            )
            +
            (SELECT 
                SUM(IloscOsob * 50) 
             FROM 
                RezerwacjeStoliki
             WHERE 
                DATE_FORMAT(DataRezerwacji, '%Y-%m') = '$currentYear-$currentMonth'
            )
        ) AS monthlyTotalEarnings,

        -- Łączny całkowity przychód z sali i stolików
        (
            (SELECT 
                SUM(
                    (p1.Cena + p2.Cena + p3.Cena) * r.LiczbaOsob + 
                    IF(r.LiczbaOsob < 42, (42 - r.LiczbaOsob) * 50, 0)
                ) 
             FROM 
                RezerwacjeSale r
             INNER JOIN 
                Potrawy p1 ON r.Przystawka = p1.PotrawaId  -- Łączenie po PotrawaId
             INNER JOIN 
                Potrawy p2 ON r.DanieGlowne = p2.PotrawaId  -- Łączenie po PotrawaId
             INNER JOIN 
                Potrawy p3 ON r.Ciasto = p3.PotrawaId  -- Łączenie po PotrawaId
            )
            +
            (SELECT 
                SUM(IloscOsob * 50) 
             FROM 
                RezerwacjeStoliki
            )
        ) AS totalTotalEarnings;
";

// Wykonanie zapytania
$result = mysqli_query($conn, $query);

// Sprawdzenie, czy zapytanie zostało wykonane poprawnie
if ($result) {
    // Pobranie wyników zapytania
    $row = mysqli_fetch_assoc($result);

    // Przypisanie wyników do zmiennych
    $monthlyEarningsSala = $row['monthlyEarningsSala'] ?? 0;
    $monthlyEarningsStolik = $row['monthlyEarningsStolik'] ?? 0;
    $totalEarningsSala = $row['totalEarningsSala'] ?? 0;
    $totalEarningsStolik = $row['totalEarningsStolik'] ?? 0;
    $monthlyTotalEarnings = $row['monthlyTotalEarnings'] ?? 0;
    $totalTotalEarnings = $row['totalTotalEarnings'] ?? 0;

    // Obliczanie łącznego przychodu (sumowanie sal i stolików)
    $totalEarnings = $totalEarningsSala + $totalEarningsStolik;
    $monthlyEarnings = $monthlyEarningsSala + $monthlyEarningsStolik;
} else {
    echo "Błąd w zapytaniu: " . mysqli_error($conn);
}

//------------------- Wiadomosci ---------------------

function fetchMessages($conn) {
    $sql = "SELECT W.WiadomoscId, W.Imie, W.Email, W.NumerTelefonu, W.Tresc, W.DataCzas, W.Przeczytana, U.Imie AS UserImie, U.Nazwisko AS UserNazwisko 
            FROM Wiadomosci W 
            LEFT JOIN Uzytkownicy U ON W.UserId = U.UzytkownikId";
    $result = $conn->query($sql);
    $messages = [];
    while ($row = $result->fetch_assoc()) {
        $messages[] = $row;
    }
    return $messages;
}

// Obsługa odpowiedzi administratora na wiadomość
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_response'])) {
    $wiadomosc_id = $_POST['wiadomosc_id'];  // ID wiadomości, na którą odpowiedź
    $odpowiedz = mysqli_real_escape_string($conn, $_POST['odpowiedz']);
    
    // Sprawdzenie, czy odpowiedź nie jest pusta
    if (!empty($odpowiedz)) {
        // Wstawienie odpowiedzi do kolumny Odpowiedz
        $sql = "UPDATE Wiadomosci SET Odpowiedz = ?, Przeczytana = 1 WHERE WiadomoscId = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $odpowiedz, $wiadomosc_id);
        
        if ($stmt->execute()) {
            $message2 = "Odpowiedź została wysłana!";
        } else {
            $message2 = "Błąd podczas wysyłania odpowiedzi: " . $stmt->error;
        }
    } else {
        $message2 = "Odpowiedź nie może być pusta!";
    }
}

// Obsługa oznaczania wiadomości jako przeczytanej
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['mark_as_read'])) {
    $wiadomosc_id = $_POST['wiadomosc_id'];
    $sql = "UPDATE Wiadomosci SET Przeczytana = 1 WHERE WiadomoscId = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $wiadomosc_id);
    
    if ($stmt->execute()) {
        $message2 = "Wiadomość została oznaczona jako przeczytana!";
    } else {
        $message2 = "Błąd podczas oznaczania wiadomości jako przeczytanej: " . $stmt->error;
    }
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Frapza- Panel admina</title>
    <link rel="icon" href="assets/frapza_logo.png" type="image/png" />
    <link rel="stylesheet" href="css/panelAdmina.css?v=<?php echo filemtime('css/panelAdmina.css'); ?>" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
</head>
<body>

<div class="sidebar">
<img src="assets/frapza_logo.png" alt="swiftdrop logo" class="logo" />
    <button class="button-86" onclick="showSection('user-management')">Zarządzanie użytkownikami</button>
    <button class="button-86" onclick="showSection('statistics')">Statystyki</button>
    <button class="button-86" onclick="showSection('menu')">Menu</button>
    <form action="" method="POST">
        <button type="submit" name="logout" value="true" class="button-86">Wyloguj się</button>
    </form>
</div>


<div id="user-management" class="content active">
    <h1>Zarządzanie użytkownikami</h1>

    <div class="grid">
        <div class="right">
  
    <!-- Przycisk do wyświetlania formularza -->

    <!-- Formularz dodawania użytkownika w kontenerze -->
    <div id="add-user-form-container" class="form-container">
    
    <form id="add-user-form" action="backend/add_user.php" method="POST">
    <!-- Komunikaty o błędach lub sukcesie -->
    <div id="error-messages">
    </div>
    <div class="panel-add">
    <label for="imie">Imię:</label>
    <input type="text" id="imie" name="imie" value="<?php echo isset($_POST['imie']) ? $_POST['imie'] : ''; ?>" required>

    <label for="nazwisko">Nazwisko:</label>
    <input type="text" id="nazwisko" name="nazwisko" value="<?php echo isset($_POST['nazwisko']) ? $_POST['nazwisko'] : ''; ?>" required>

    <label for="email">Email:</label>
    <input type="email" id="email" name="email" value="<?php echo isset($_POST['email']) ? $_POST['email'] : ''; ?>" required>

    <label for="numer">Numer telefonu:</label>
    <input type="text" id="numer" name="numer" value="<?php echo isset($_POST['numer']) ? $_POST['numer'] : ''; ?>">

    <label for="haslo">Hasło:</label>
    <input type="password" id="haslo" name="haslo" required>

    <label for="powtorz_haslo">Powtórz hasło:</label>
    <input type="password" id="powtorz_haslo" name="powtorz_haslo" required>

    <label for="rola">Rola:</label>
    <select name="rola" id="rola">
        <option value="user" <?php echo (isset($_POST['rola']) && $_POST['rola'] == 'user') ? 'selected' : ''; ?>>Użytkownik</option>
        <option value="pracownik" <?php echo (isset($_POST['rola']) && $_POST['rola'] == 'pracownik') ? 'selected' : ''; ?>>Pracownik</option>
        <option value="admin" <?php echo (isset($_POST['rola']) && $_POST['rola'] == 'admin') ? 'selected' : ''; ?>>Admin</option>
    </select>
    
    </div>

    <div class="box">
    <button type="submit" class="btn button-86">Dodaj</button>
    </div>
    </form>  
    </div>
    </div><!-- RIGHT -->
    
  
    <div class="left">
    <!-- Formularz edycji użytkownika -->
    <div id="edit-user-form" class="flex-container"style="display: none;">
        <form id="edit-form">
            <input type="hidden" id="edit-user-id">

            <label for="edit-imie">Imię:</label>
            <input type="text" id="edit-imie" name="imie" required>

            <label for="edit-nazwisko">Nazwisko:</label>
            <input type="text" id="edit-nazwisko" name="nazwisko" required>

            <label for="edit-email">Email:</label>
            <input type="email" id="edit-email" name="email" required>

            <label for="edit-numer">Numer telefonu:</label>
            <input type="text" id="edit-numer" name="numer">

            <label for="edit-rola">Rola:</label>
            <select id="edit-rola" name="rola">
                <option value="user">Użytkownik</option>
                <option value="pracownik">Pracownik</option>
                <option value="admin">Admin</option>
            </select>
            <div class="edit-buttons">
    <button type="submit" class="btn button-86" >Zapisz zmiany</button>
            <button type="button" class="btn button-86" onclick="hideEditForm()">Anuluj</button>
            </div>
        </form>
    </div>


    <!-- Formularz wyszukiwania użytkowników -->
    <div id="search-user-form">
        <h2 class="heading-search">Wyszukaj użytkowników</h2>
        <div class="box-search">
        <form id="search-form" action="" method="GET">
            <label for="search-criteria">Wybierz kryterium wyszukiwania:</label>
            <select name="search-criteria" id="search-criteria">
                <option value="imie">Imię</option>
                <option value="nazwisko">Nazwisko</option>
                <option value="email">Email</option>
                <option value="rola">Rola</option>
            </select>

            <label for="search-term">Wprowadź wartość:</label>
            <input type="text" id="search-term" name="search-term">
        </form>
        </div>
    </div>


    <!-- Tabela z użytkownikami -->
    <div id="user-list">
        <h2>Lista użytkowników</h2>
        <table class = "fl-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Imię</th>
                    <th>Nazwisko</th>
                    <th>Email</th>
                    <th>Numer telefonu</th>
                    <th>Rola</th>
                    <th>Akcje</th>
                </tr>
            </thead>
            <tbody id="user-table-body">
                <!-- Dane użytkowników będą ładowane dynamicznie -->
            </tbody>
        </table>
    </div>
    </div> <!-- left -->
    
    </div> <!-- GRID -->
</div>

<script>
//------------------------------- Dodanie Użytkownika ----------------------


document.addEventListener('DOMContentLoaded', function () {
    // Nasłuchiwacz dla formularza
    document.getElementById('add-user-form').addEventListener('submit', function (e) {
        e.preventDefault(); // Zapobiegaj domyślnemu działaniu formularza

        var formData = new FormData(this); // Pobierz dane formularza

        // Utwórz obiekt XMLHttpRequest (AJAX)
        var xhr = new XMLHttpRequest();
        xhr.open('POST', this.action, true); // Ustaw metodę i adres, do którego wysyłamy formularz
        xhr.onload = function () {
            if (xhr.status === 200) {
                // Jeśli odpowiedź jest OK (200), aktualizujemy część strony
                var response = JSON.parse(xhr.responseText); // Oczekujemy JSON-a z serwera

                // Wyświetlamy odpowiedni komunikat o błędzie lub sukcesie
                if (response.success) {
                    document.getElementById('error-messages').innerHTML = '<p style="color: green;">Użytkownik został pomyślnie dodany.</p>';
                    
                    // Jeśli formularz powinien zostać zresetowany
                    if (response.resetForm) {
                        document.getElementById('add-user-form').reset(); // Resetuje formularz
                    }
                } else if (response.errors && response.errors.length > 0) {
                    var errorHTML = '<div class="error-message"><ul>';
                    response.errors.forEach(function (error) {
                        errorHTML += '<li>' + error + '</li>';
                    });
                    errorHTML += '</ul></div>';
                    document.getElementById('error-messages').innerHTML = errorHTML;
                }
            } else {
                document.getElementById('error-messages').innerHTML = '<p style="color: red;">Wystąpił błąd po stronie serwera. Spróbuj ponownie.</p>';
            }
        };
        xhr.send(formData); // Wysyłamy dane formularza
    });
});

// Funkcja przewijania strony do formularza
function scrollToEditForm() {
    const formContainer = document.getElementById('edit-user-form');
    window.scrollTo({
        top: formContainer.offsetTop - 100, // Przewijamy do formularza z lekkim odstępem
        behavior: 'smooth' // Płynne przewijanie
    });
}

//----------------------------------- Ładowanie użytkowników do tabeli -------------

document.addEventListener("DOMContentLoaded", function () {
    loadUsers();

    // Obsługa formularza edycji
    document.getElementById("edit-form").addEventListener("submit", function (event) {
        event.preventDefault();
        updateUser();
    });
});

// Funkcja do ładowania danych użytkowników
function loadUsers() {
    const xhr = new XMLHttpRequest();
    xhr.open("GET", "backend/fetch_users.php", true);
    xhr.onload = function () {
        if (xhr.status === 200) {
            document.getElementById("user-table-body").innerHTML = xhr.responseText;
            addActionListeners();
        }
    };
    xhr.send();
}

//------------------------ Szukanie w tabeli -----------------

document.addEventListener("DOMContentLoaded", function () {
    // Nasłuchiwacz na zmianę kryterium wyszukiwania lub tekstu w polu wyszukiwania
    document.getElementById("search-term").addEventListener("input", searchUsers);
    document.getElementById("search-criteria").addEventListener("change", searchUsers);

    // Funkcja do dynamicznego wyszukiwania
    function searchUsers() {
        const searchTerm = document.getElementById("search-term").value.trim();
        const searchCriteria = document.getElementById("search-criteria").value;

        if (searchTerm === "") {
            // Jeśli pole wyszukiwania jest puste, wyświetlamy wszystkie użytkowników
            loadUsers();
            return;
        }

        const xhr = new XMLHttpRequest();
        xhr.open("GET", `backend/search_users.php?search-term=${encodeURIComponent(searchTerm)}&search-criteria=${encodeURIComponent(searchCriteria)}`, true);
        xhr.onload = function () {
            if (xhr.status === 200) {
                document.getElementById("user-table-body").innerHTML = xhr.responseText;
                addActionListeners();
            }
        };
        xhr.send();
    }
});

//--------------------------------- ----------------------------
// Funkcja dodająca obsługę przycisków edycji i usuwania
function addActionListeners() {
    document.querySelectorAll(".edit").forEach(button => {
        button.addEventListener("click", function () {
            const userId = this.dataset.id;
            loadUserForEdit(userId);
            scrollToEditForm()
        });
    });

    document.querySelectorAll(".delete").forEach(button => {
        button.addEventListener("click", function () {
            const userId = this.dataset.id;
            deleteUser(userId);
        });
    });
}

// Funkcja do chowania formularza edycji po klikniecia guzika anuluj
function hideEditForm() {
    document.getElementById("edit-user-form").style.display = "none";
}

// Funkcja do ładowania danych użytkownika do formularza edycji
function loadUserForEdit(userId) {
    const xhr = new XMLHttpRequest();
    xhr.open("GET", `backend/get_user.php?id=${userId}`, true);
    xhr.onload = function () {
        if (xhr.status === 200) {
            const user = JSON.parse(xhr.responseText);
            document.getElementById("edit-user-id").value = user.UzytkownikId;
            document.getElementById("edit-imie").value = user.Imie;
            document.getElementById("edit-nazwisko").value = user.Nazwisko;
            document.getElementById("edit-email").value = user.Email;
            document.getElementById("edit-numer").value = user.NumerTelefonu;
            document.getElementById("edit-rola").value = user.Rola;
            document.getElementById("edit-user-form").style.display = "flex";
        }
    };
    xhr.send();
}

// Funkcja do aktualizacji danych użytkownika z walidacją na froncie
function updateUser() {
    const userId = document.getElementById("edit-user-id").value;
    const imie = document.getElementById("edit-imie").value.trim();
    const nazwisko = document.getElementById("edit-nazwisko").value.trim();
    const email = document.getElementById("edit-email").value.trim();
    const numer = document.getElementById("edit-numer").value.trim();
    const rola = document.getElementById("edit-rola").value;

    // Walidacja danych na froncie
    const errors = [];

    // Walidacja imienia
    if (!imie) {
        errors.push("Proszę podać imię.");
    }

    // Walidacja nazwiska
    if (!nazwisko) {
        errors.push("Proszę podać nazwisko.");
    }

    // Walidacja adresu e-mail
    if (!email) {
        errors.push("Proszę podać adres email.");
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        errors.push("Podano nieprawidłowy adres email.");
    }

    // Walidacja numeru telefonu
    if (!numer) {
        errors.push("Proszę podać numer telefonu.");
    } else if (!/^[0-9]{9}$/.test(numer)) {
        errors.push("Numer telefonu powinien składać się z dokładnie 9 cyfr.");
    }

    // Jeśli są błędy, wyświetl je i przerwij wysyłanie danych
    if (errors.length > 0) {
        alert(errors.join("\n"));
        return;
    }

    // Wysłanie danych do backendu
    const xhr = new XMLHttpRequest();
    xhr.open("POST", "backend/edit_user.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.onload = function () {
        if (xhr.status === 200) {
            const response = JSON.parse(xhr.responseText);
            if (response.success) {
                alert("Dane użytkownika zostały zaktualizowane.");
                loadUsers(); // Funkcja do odświeżenia listy użytkowników
                hideEditForm(); // Funkcja do ukrycia formularza edycji
            } else {
                alert(response.errors.join("\n") || "Wystąpił błąd podczas aktualizacji.");
            }
        }
    };
    xhr.send(`id=${userId}&imie=${imie}&nazwisko=${nazwisko}&email=${email}&numer=${numer}&rola=${rola}`);
}


// Funkcja do usuwania użytkownika
function deleteUser(userId) {
    if (confirm("Czy na pewno chcesz usunąć tego użytkownika?")) {
        const xhr = new XMLHttpRequest();
        xhr.open("POST", "backend/delete_user.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.onload = function () {
            if (xhr.status === 200) {
                const response = JSON.parse(xhr.responseText);
                if (response.success) {
                    alert("Użytkownik został usunięty.");
                    loadUsers();
                } else {
                    alert(response.error); // Wyświetlamy błąd, jeśli próbowano usunąć administratora
                }
            } else {
                alert("Wystąpił błąd podczas usuwania użytkownika.");
            }
        };
        xhr.send(`id=${userId}`);
    }
}

// Ładowanie użytkowników po załadowaniu strony
window.onload = loadUsers;
</script>


<div id="statistics" class="content">
    <h1>Statystyki</h1>
    
    <!-- Formularz do wybierania dat -->
    <form id="dateForm">
        <label for="startDate">Data początkowa:</label>
        <input type="date" id="startDate" name="startDate" required>
    
        <label for="endDate">Data końcowa:</label>
        <input type="date" id="endDate" name="endDate" required>
        
        <button type="submit" class="button-24 edytuj margin-left-smf">Pokaż statystyki</button>
        <button type="button" class="button-24" id="clearButton">Wyczyść</button>
    </form>>
    
    <!-- Statystyki, które będą się zmieniać po kliknięciu -->
    <div class="container boxes" id="initialStatistics">
        <!-- Te dane wyświetlane są początkowo -->
        <div class="box-stats">
            <p class="box-text">Użytkownicy🧔</p>
            <p class="box-statistic" id="userCount"></p>
        </div>
        <div class="box-stats">
            <p class="box-text">Rezerwacje 🎫</p>
            <p class="box-statistic" id="reservationCount"></p>
        </div>
        <div class="box-stats">
            <p class="box-text">Wiadomości 📩</p>
            <p class="box-statistic" id="messageCount"></p>
        </div>
        <div class="box-stats">
            <p class="box-text">Ilość dań 🥗</p>
            <p class="box-statistic" id="dishCount"></p>
        </div>
        <div class="box-stats">
            <p class="box-text">Zarobki w miesiącu💸</p>
            <p class="box-statistic" id="monthlyEarnings"></p>
        </div>
        <div class="box-stats">
            <p class="box-text">Zarobki ogólnie💸</p>
            <p class="box-statistic" id="totalEarnings"></p>
        </div>
    </div>

    <!-- Wyniki po kliknięciu przycisku "Pokaż statystyki" -->
    <div class="container box" id="manualStatsResults" style="display: none;">
        <div class="box-stats-manual" >
            <div class="stats-results">
            <div>Użytkownicy: <span id="userCountDate"></span></div>
            <div>Wiadomości: <span id="messageCountDate"></span></div>
            <div>Opinie: <span id="opinionCountDate"></span></div>
            <div>Rezerwacje stolików: <span id="reservationCountDate"></span></div>
            <div>Rezerwacje sal: <span id="roomReservationCountDate"></span></div>
            <div>Łączna liczba rezerwacji: <span id="totalReservationsDate"></span></div>
            <div>Zarobki stolików: <span id="tableEarningsDate"></span></div>
            <div>Zarobki sal: <span id="roomEarningsDate"></span></div>
            <div>Łączne zarobki: <span id="totalEarningsDate"></span></div>
            <div class="info">
            <div id="dishFrequencyDate"></div>
            <div id="dishRevenueDate"></div>
            </div>
            </div>
        </div>
    </div>
</div>



<script>

document.addEventListener('DOMContentLoaded', function () {
    const initialStatistics = document.getElementById('initialStatistics');
    const manualStatsResults = document.getElementById('manualStatsResults');
    const dateForm = document.getElementById('dateForm');
    const clearButton = document.getElementById('clearButton');

    // Dodanie przycisku do pobrania danych
    const downloadButtonContainer = document.createElement('div');
    downloadButtonContainer.id = 'downloadButtonContainer'; 
    downloadButtonContainer.style.display = 'none';
    downloadButtonContainer.innerHTML = '<button class="edit-menu button-24 edytuj button-servis" id="downloadExcelButton">Pobierz dane do Excel</button>';
    manualStatsResults.parentNode.insertBefore(downloadButtonContainer, manualStatsResults.nextSibling);

    // Ustaw maksymalną datę na dzisiejszy dzień
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('startDate').max = today;
    document.getElementById('endDate').max = today;

    // Funkcja fetchStatistics
    function fetchStatistics(startDate, endDate) {
        $.ajax({
            url: 'backend/fetch_statistics.php',
            method: 'POST',
            data: { startDate, endDate },
            dataType: 'json',
            success: function (response) {
                // Aktualizacja wyników
                $('#userCountDate').text(response.users.newUsers);
                $('#messageCountDate').text(response.messages.newMessages);
                $('#opinionCountDate').text(response.opinions.newOpinions);
                $('#reservationCountDate').text(response.reservations.tables);
                $('#roomReservationCountDate').text(response.reservations.rooms);
                $('#tableEarningsDate').text(response.earnings.tables + " zł");
                $('#roomEarningsDate').text(response.earnings.rooms + " zł");
                $('#totalReservationsDate').text(response.reservations.total);
                $('#totalEarningsDate').text(response.earnings.total + " zł");

                // Najczęściej wybierane potrawy
                const dishFrequency = response.dishes.mostFrequent;
                const dishRevenue = response.dishes.highestEarnings;

                let frequencyHtml = '<h3>Najczęściej wybierane potrawy:</h3><ul>';
                dishFrequency.forEach(dish => {
                    frequencyHtml += `<li>${dish.Kategoria} - ${dish.Nazwa}: ${dish.dishFrequency} razy</li>`;
                });
                frequencyHtml += '</ul>';
                $('#dishFrequencyDate').html(frequencyHtml);

                let revenueHtml = '<h3>Potrawy z największym zarobkiem:</h3><ul>';
                dishRevenue.forEach(dish => {
                    revenueHtml += `<li>${dish.Kategoria} - ${dish.Nazwa}: ${dish.dishRevenue} zł</li>`;
                });
                revenueHtml += '</ul>';
                $('#dishRevenueDate').html(revenueHtml);

                // Pokaż nowe dane, ukryj dane początkowe
                manualStatsResults.style.display = 'block';
                initialStatistics.style.display = 'none';

                // Pokaż przycisk pobierania
                downloadButtonContainer.style.display = 'block';
            },
            error: function (xhr, status, error) {
                console.error('Błąd AJAX:', error);
            }
        });
    }

    // Obsługa formularza
    dateForm.addEventListener('submit', function (event) {
        event.preventDefault();

        const startDate = document.getElementById('startDate').value;
        const endDate = document.getElementById('endDate').value;

        if (startDate && endDate) {
            fetchStatistics(startDate, endDate);
        } else {
            alert('Proszę wybrać obie daty!');
        }
    });

    // Obsługa przycisku czyszczenia
clearButton.addEventListener('click', function () {
    document.getElementById('startDate').value = '';
    document.getElementById('endDate').value = '';

    // Ukryj szczegółowe dane, pokaż dane początkowe
    manualStatsResults.style.display = 'none';
    initialStatistics.style.display = '';
    downloadButtonContainer.style.display = 'none';
});

// Obsługa pobierania danych do Excela
document.getElementById('downloadExcelButton').addEventListener('click', function () {
    // Pobieranie danych z elementów HTML
    const statisticsData = [
        ['Kategoria', 'Wartość'],
        ['Użytkownicy', $('#userCountDate').text()],
        ['Wiadomości', $('#messageCountDate').text()],
        ['Opinie', $('#opinionCountDate').text()],
        ['Rezerwacje stolików', $('#reservationCountDate').text()],
        ['Rezerwacje sal', $('#roomReservationCountDate').text()],
        ['Zarobki stolików', $('#tableEarningsDate').text()],
        ['Zarobki sal', $('#roomEarningsDate').text()],
        ['Łączna liczba rezerwacji', $('#totalReservationsDate').text()],
        ['Łączne zarobki', $('#totalEarningsDate').text()],
    ];

    // Najczęściej wybierane potrawy
    const dishFrequencyData = [['Kategoria', 'Nazwa', 'Ilość']];
    $('#dishFrequencyDate ul li').each(function () {
        const text = $(this).text();
        const [category, details] = text.split(' - ');
        const [name, frequency] = details.split(': ');
        dishFrequencyData.push([category.trim(), name.trim(), frequency.replace(' razy', '').trim()]);
    });

    // Potrawy z największym zarobkiem
    const dishRevenueData = [['Kategoria', 'Nazwa', 'Zarobek']];
    $('#dishRevenueDate ul li').each(function () {
        const text = $(this).text();
        const [category, details] = text.split(' - ');
        const [name, revenue] = details.split(': ');
        dishRevenueData.push([category.trim(), name.trim(), revenue.replace(' zł', '').trim()]);
    });

    // Tworzenie nowego pliku Excel
    const wb = XLSX.utils.book_new();

    // Dodanie arkusza z głównymi statystykami
    const wsStatistics = XLSX.utils.aoa_to_sheet(statisticsData);
    XLSX.utils.book_append_sheet(wb, wsStatistics, 'Statystyki');

    // Dodanie arkusza z najczęściej wybieranymi potrawami
    if (dishFrequencyData.length > 1) {
        const wsDishFrequency = XLSX.utils.aoa_to_sheet(dishFrequencyData);
        XLSX.utils.book_append_sheet(wb, wsDishFrequency, 'Najczęściej wybierane potrawy');
    }

    // Dodanie arkusza z potrawami o największym zarobku
    if (dishRevenueData.length > 1) {
        const wsDishRevenue = XLSX.utils.aoa_to_sheet(dishRevenueData);
        XLSX.utils.book_append_sheet(wb, wsDishRevenue, 'Potrawy z największym zarobkiem');
    }

    // Zapisanie pliku Excel
    const startDate = document.getElementById('startDate').value;
    const endDate = document.getElementById('endDate').value;
    const fileName = `Statystyki_${startDate}_do_${endDate}.xlsx`;
    XLSX.writeFile(wb, fileName);
});
});

document.addEventListener('DOMContentLoaded', function () {
    // Funkcja do animowania liczb
    function animateCounter(elementId, targetValue, duration, isCurrency = false) {
        let startValue = 0;
        let increment = targetValue / (duration / 50); // Inkrement na każdą klatkę (50ms)

        const element = document.getElementById(elementId);

        function updateCounter() {
            if (startValue < targetValue) {
                startValue += increment;
                element.innerText = isCurrency
                    ? formatNumber(Math.floor(startValue)) + ' zł'
                    : Math.floor(startValue); // Formatowanie tylko dla zarobków
                requestAnimationFrame(updateCounter); // Wywołuje funkcję na następnej klatce
            } else {
                element.innerText = isCurrency
                    ? formatNumber(targetValue) + ' zł'
                    : targetValue; // Gdy liczba osiągnie docelową wartość
            }
        }

        updateCounter();
    }

    // Funkcja formatująca liczby na format "1,234.00" (dla zarobków)
    function formatNumber(number) {
        return number.toLocaleString('pl-PL', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    // Nasłuchiwanie na kliknięcie przycisku "Statystyki"
    const statsButton = document.querySelector('button[onclick="showSection(\'statistics\')"]');
    
    if (statsButton) {
        statsButton.addEventListener('click', function() {
            // Pokaż sekcję 'statistics'
            const statisticsSection = document.getElementById('statistics');
            if (statisticsSection) {

                // Uruchom animacje liczników po kliknięciu przycisku
                animateCounter('userCount', <?php echo $userCount; ?>, 3000);
                animateCounter('reservationCount', <?php echo $reservationCount; ?>, 3000);
                animateCounter('messageCount', <?php echo $messageCount; ?>, 3000);
                animateCounter('dishCount', <?php echo $dishCount; ?>, 4000);
                animateCounter('monthlyEarnings', <?php echo $monthlyEarnings; ?>, 5000, true); // Formatowanie dla zarobków
                animateCounter('totalEarnings', <?php echo $totalEarnings; ?>, 5000, true); // Formatowanie dla zarobków
            }
        });
    }

    // Obsługa formularza wyboru dat
    const dateForm = document.getElementById('dateForm');
    if (dateForm) {
        dateForm.addEventListener('submit', function (event) {
            event.preventDefault(); // Zapobiega przeładowaniu strony
            $('initialStatistics').hide()

            const startDate = document.getElementById('startDate').value;
            const endDate = document.getElementById('endDate').value;

            if (startDate && endDate) {
                // Pobierz statystyki dla wybranego zakresu dat
                fetchStatistics(startDate, endDate);
            } else {
                alert('Proszę wybrać obie daty!');
            }
        });
    }
});


</script>


<div id="menu" class="content">
    <h1>Menu</h1>
    <!-- Przycisk do wyświetlenia formularza dodawania potrawy -->
    <button id="open-add-form" class="add-menu button-86" style="margin-bottom:4rem">Dodaj nową potrawę</button>

    <!-- Formularz dodawania nowej potrawy -->
    <div id="add-dish-form-container" class="form-container" style="display: none;">
        <h2>Dodaj nową potrawę</h2> 
        <form id="form-dodaj-menu"> 
            <div class="form-temp">
            <label for="nazwa">Nazwa:</label>
            <input type="text" id="nazwa" name="nazwa" required><br><br>
            </div>
            <div class="form-temp">
            <label for="cena">Cena:</label>
            <input type="text" id="cena" name="cena" required><br><br>  
            </div>

            <div class="form-temp">
            <label for="opis">Opis:</label>
            <textarea id="opis" name="opis"></textarea><br><br>
            </div>
            <div class="form-temp">
            <label for="link-zdjecie">Zdjęcie (link):</label>
            <input type="url" id="link-zdjecie" name="link_zdjecie" placeholder="Wklej link do zdjęcia"><br><br>
            </div>
            <div class="form-temp">
            <label for="kategoria">Kategoria:</label>
            <!-- Lista rozwijana z kategoriami z bazy danych -->
            <select id="kategoria" name="kategoria">
                <option value="">Wybierz kategorię</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?php echo htmlspecialchars($category); ?>"><?php echo htmlspecialchars($category); ?></option>
                <?php endforeach; ?>
            </select><br><br>
            <label for="new-kategoria">Lub dodaj nową kategorię:</label>
            <input type="text" id="new-kategoria" name="new_kategoria" placeholder="Nowa kategoria (opcjonalnie)">
            </div>
            <div class="buttons-temp">
            <button type="submit" name="dodaj" class="add-menu button-24">Dodaj potrawę</button>
            <button type="button" id="close-add-form" class="cancel-button button-24">Anuluj</button>
            </div>
        </form>
    </div>

    <!-- Formularz edycji potrawy -->
    <div id="edit-dish-form-container" class="form-container" style="display: none;">
        <h2>Edytuj potrawę</h2>
        <form id="form-edytuj">
            <input type="hidden" id="edit-id" name="id">
            <label for="edit-nazwa">Nazwa:</label>
            <input type="text" id="edit-nazwa" name="nazwa" required><br><br>

            <label for="edit-cena">Cena:</label>
            <input type="text" id="edit-cena" name="cena" required><br><br>

            <label for="edit-opis">Opis:</label>
            <textarea id="edit-opis" name="opis"></textarea><br><br>

            <label for="edit-zdjecie">Zdjęcie (plik):</label>
            <input type="file" id="edit-zdjecie" name="zdjecie"><br><br>

            <label for="edit-link-zdjecie">Zdjęcie (link):</label>
            <input type="url" id="edit-link-zdjecie" name="link_zdjecie" placeholder="Wklej link do zdjęcia"><br><br>

            <label for="edit-kategoria">Kategoria:</label>
            <input type="text" id="edit-kategoria" name="kategoria"><br><br>

            <div class="buttons-temp">
            <button type="submit" name="edytuj" class="edit-menu button-24">Zaktualizuj</button>
            <button type="button" id="close-edit-form" class="cancel-button button-24">Anuluj</button>
            </div>
        </form>
    </div>

    <!-- Filtry dla wszystkich kolumn -->
    <div id="search-form-menu">
        <h2>Wyszukaj potrawy</h2>
        <form id="search-dish-form" method="GET" action="">
            <label for="search-criteria">Wybierz kryterium wyszukiwania:</label>
            <select name="search-criteria" id="search-criteria-menu" class="search-menu">
                <option value="nazwa">Nazwa</option>
                <option value="cena">Cena</option>
                <option value="opis">Opis</option>
                <option value="kategoria">Kategoria</option>
            </select>

            <label for="search-term">Wprowadź wartość:</label>
            <input type="text" id="search-term-menu" name="search-term" class="search-menu" placeholder="Wpisz wartość do wyszukania">
        </form>
    </div>


<h2>Potrawy w menu</h2>
<table class= fl-table> 
    <thead>
        <tr>
            <th>ID</th>
            <th>Nazwa</th>
            <th>Cena</th>
            <th>Opis</th>
            <th>Zdjęcie</th>
            <th>Kategoria</th>
            <th>Status</th>
            <th>Opcje</th>
        </tr>
    </thead>
    <tbody id="potrawy-lista">
        <!-- Potrawy będą wczytywane dynamicznie -->
    </tbody>
</table>
</div>

<script>

//-------------------------- wyszukiwanie dania w menu ----------------------

document.addEventListener("DOMContentLoaded", function () {
    // Nasłuchiwacze na zmiany w polach wyszukiwania
    document.getElementById("search-term-menu").addEventListener("input", searchDishes);
    document.getElementById("search-criteria-menu").addEventListener("change", searchDishes);

    // Funkcja do dynamicznego wyszukiwania potraw
    function searchDishes() {
        const searchTerm = document.getElementById("search-term-menu").value.trim();
        const searchCriteria = document.getElementById("search-criteria-menu").value;

        if (searchTerm === "") {
            // Jeśli pole wyszukiwania jest puste, załaduj wszystkie potrawy
            loadMenu();
            return;
        }

        const xhr = new XMLHttpRequest();
        xhr.open("GET", `backend/search_dishes.php?search-term=${encodeURIComponent(searchTerm)}&search-criteria=${encodeURIComponent(searchCriteria)}`, true);
        xhr.onload = function () {
            if (xhr.status === 200) {
                document.getElementById("potrawy-lista").innerHTML = xhr.responseText;
            }
        };
        xhr.send();
    }
});

//-------------------------------- Ladowanie menu ------------------------------------
// Funkcja do pobierania potraw z bazy danych i wstawiania ich do tabeli
function loadMenu() {
    fetch('backend/manage_menu.php')
        .then(response => response.json()) // Oczekujemy odpowiedzi w formacie JSON
        .then(data => {
            const tableBody = document.getElementById('potrawy-lista'); // Tabela potraw
            
            // Najpierw czyścimy zawartość tabeli
            tableBody.innerHTML = '';
            
            if (data.length > 0) {
                // Sortujemy potrawy według kategorii (jeśli potrzebne)
                data.sort((a, b) => a.kategoria.localeCompare(b.kategoria));

                // Iterujemy po danych i tworzymy wiersze tabeli
                data.forEach(dish => {
                    // Tworzymy wiersz dla każdej potrawy
                    const row = document.createElement('tr');
                    
                    // Tworzymy komórki dla każdej kolumny
                    row.innerHTML = `
                        <td>${dish.id}</td>
                        <td>${dish.nazwa}</td>
                        <td>${dish.cena}</td>
                        <td>${dish.opis}</td>
                        <td><img src="${dish.zdjecie || 'default_image.jpg'}" alt="Zdjęcie" width="50" height="50"></td>
                        <td>${dish.kategoria}</td>
                        <td>${dish.status}</td> <!-- Wyświetlamy status -->
                        <td>
                            <button class="edit-menu button-24 edytuj" data-id="${dish.id}">Edytuj</button>
                            <button class="change-status button-24" data-id="${dish.id}" data-status="${dish.status}">Zmień status</button> <!-- Przycisk zmiany statusu -->
                        </td>
                    `;
                    
                    // Dodajemy wiersz do tabeli
                    tableBody.appendChild(row);
                });
            } else {
                // Jeśli nie ma potraw, wyświetlamy komunikat
                tableBody.innerHTML = '<tr><td colspan="8">Brak potraw w menu</td></tr>';
            }

            // Dodajemy nasłuchiwacze na przyciski zmiany statusu
            document.querySelectorAll('.change-status').forEach(button => {
                button.addEventListener('click', function() {
                    const dishId = this.getAttribute('data-id');
                    const currentStatus = this.getAttribute('data-status');
                    const newStatus = currentStatus === 'aktywna' ? 'nieaktywna' : 'aktywna'; // Zmieniamy status na przeciwny
                    
                    changeStatus(dishId, newStatus);
                });
            });
        })
        .catch(error => {
            console.error('Błąd podczas ładowania potraw:', error);
        });
}

// Funkcja zmieniająca status potrawy
function changeStatus(dishId, newStatus) {
    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'backend/change_status.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onload = function() {
        if (xhr.status === 200) {
            alert('Status został zmieniony');
            loadMenu(); // Przeładuj menu po zmianie statusu
        } else {
            alert('Błąd podczas zmiany statusu');
        }
    };
    xhr.send(`id=${dishId}&status=${newStatus}`);
}

// Uruchomienie funkcji podczas ładowania strony
document.addEventListener('DOMContentLoaded', function() {
    loadMenu(); // Załaduj menu przy wczytaniu strony
});



//---------------------------------------- Dodawanie dania ---------------------------
// Zdarzenie dla formularza dodawania potrawy

document.getElementById('form-dodaj-menu').addEventListener('submit', function(event) {
    event.preventDefault(); // Zapobiega przeładowaniu strony

    // Pobieramy wartości z formularza
    var nazwa = document.getElementById('nazwa').value.trim();
    var cena = document.getElementById('cena').value.trim();
    var opis = document.getElementById('opis').value.trim();
    var link_zdjecie = document.getElementById('link-zdjecie').value.trim();
    var nowaKategoria = document.getElementById('new-kategoria').value.trim(); // Pobieramy nową kategorię
    var kategoria = document.getElementById('kategoria').value;

    // Walidacja ceny (czy zawiera tylko liczby i opcjonalnie jedno miejsce dziesiętne)
    var cenaRegex = /^[0-9]+(\.[0-9]+)?$/; // Regex dla liczby (może mieć część dziesiętną)
    if (!cenaRegex.test(cena)) {
        alert('Proszę wpisać poprawną cenę (tylko liczby, np. 19.99)');
        return; // Przerywamy wykonanie, jeśli cena jest niepoprawna
    }

    // Walidacja dla pola "Nazwa" (nie może być puste)
    if (nazwa === "") {
        alert('Proszę wpisać nazwę potrawy.');
        return; // Przerywamy wykonanie, jeśli nazwa jest pusta
    }

    // Walidacja dla pola "Kategoria" (jeśli kategoria jest pusta, to pokazujemy komunikat)
    if (kategoria === "" && nowaKategoria === "") {
        alert('Proszę wybrać kategorię lub dodać nową.');
        return; // Przerywamy wykonanie, jeśli nie wybrano kategorii
    }

    var formData = new FormData(this); // Pobieramy wszystkie dane z formularza

    // Jeśli użytkownik wpisał nową kategorię, zamień wartość "kategoria" na nową
    if (nowaKategoria) {
        formData.set('kategoria', nowaKategoria); // Zmieniamy kategorię na nową, jeśli użytkownik ją wpisał
    }

    // Wysyłamy dane formularza za pomocą AJAX
    fetch('backend/add_menu.php', {
        method: 'POST',
        body: formData // Przesyłamy FormData, która zawiera dane formularza
    })
    .then(response => response.json()) // Oczekujemy odpowiedzi w formacie JSON
    .then(data => {
        // Sprawdzamy odpowiedź
        if (data.status === 'success') {
            alert(data.message); // Wyświetlamy komunikat
            loadMenu(); // Ponowne załadowanie listy potraw
            document.getElementById('add-dish-form-container').style.display = 'none'; // Ukrycie formularza
            
            // Wyczyść pola formularza
            document.getElementById('form-dodaj-menu').reset(); // Resetuje wszystkie pola formularza
            
            // Dodatkowo: Zresetuj kategorie, jeśli została dodana nowa kategoria
            document.getElementById('new-kategoria').value = ''; // Resetowanie nowej kategorii
            document.getElementById('kategoria').value = ''; // Resetowanie pola kategorii (jeśli była wybrana)
            loadMenu()
        } else {
            alert('Błąd: ' + data.message); // Wyświetlamy błąd
        }
    })
    .catch(error => {
        console.error('Błąd:', error);
        alert('Wystąpił błąd podczas dodawania potrawy.');
    });
});

// Pokazuje formularz dodawania
document.getElementById('open-add-form').addEventListener('click', function() {
    document.getElementById('add-dish-form-container').style.display = 'flex';
});

// Zamknięcie formularza dodawania
document.getElementById('close-add-form').addEventListener('click', function() {
    document.getElementById('add-dish-form-container').style.display = 'none';
});

//---------------------------------------- Edytowanie potrawy -------------------------
// Logowanie kliknięcia w przycisk edycji
document.getElementById('potrawy-lista').addEventListener('click', function (event) {
    if (event.target.classList.contains('edit-menu')) {
        const dishId = event.target.getAttribute('data-id');

        fetch(`backend/get_dish.php?id=${dishId}`)
            .then(response => response.json())
            .then(dish => {
                if (dish && dish.id) {
                    openEditForm(dish);
                } else {
                    alert('Nie znaleziono danych potrawy!');
                }
            })
            .catch(error => {
                console.error('Błąd podczas pobierania danych:', error);
                alert('Wystąpił błąd podczas pobierania danych.');
            });
    }
});

function openEditForm(dish) {
    document.getElementById('edit-id').value = dish.id;
    document.getElementById('edit-nazwa').value = dish.nazwa || '';
    document.getElementById('edit-cena').value = dish.cena || '';
    document.getElementById('edit-opis').value = dish.opis || '';
    document.getElementById('edit-link-zdjecie').value = dish.zdjecie || '';
    document.getElementById('edit-kategoria').value = dish.kategoria || '';

    document.getElementById('edit-dish-form-container').style.display = 'flex';
    scrollToEditForm();
}

document.getElementById('close-edit-form').addEventListener('click', function () {
    document.getElementById('edit-dish-form-container').style.display = 'none';
});

// Funkcja przewijania strony do formularza
function scrollToEditForm() {
    const formContainer = document.getElementById('edit-dish-form-container');
    window.scrollTo({
        top: formContainer.offsetTop - 100, // Przewijamy do formularza z lekkim odstępem
        behavior: 'smooth' // Płynne przewijanie
    });
}

document.getElementById('form-edytuj').addEventListener('submit', function (event) {
    event.preventDefault(); // Zapobiegamy przeładowaniu strony

    // Pobieramy wartości z formularza
    var nazwa = document.getElementById('edit-nazwa').value.trim();
    var cena = document.getElementById('edit-cena').value.trim();
    var opis = document.getElementById('edit-opis').value.trim();
    var link_zdjecie = document.getElementById('edit-link-zdjecie').value.trim();
    var kategoria = document.getElementById('edit-kategoria').value.trim();

    // Walidacja ceny (czy zawiera tylko liczby i opcjonalnie jedno miejsce dziesiętne)
    var cenaRegex = /^[0-9]+(\.[0-9]+)?$/; // Regex dla liczby (może mieć część dziesiętną)
    if (!cenaRegex.test(cena)) {
        alert('Proszę wpisać poprawną cenę (tylko liczby, np. 19.99)');
        return; // Przerywamy wykonanie, jeśli cena jest niepoprawna
    }

    // Walidacja dla pola "Nazwa" (nie może być puste)
    if (nazwa === "") {
        alert('Proszę wpisać nazwę potrawy.');
        return; // Przerywamy wykonanie, jeśli nazwa jest pusta
    }

    // Walidacja dla pola "Kategoria" (nie może być puste)
    if (kategoria === "") {
        alert('Proszę wpisać kategorię potrawy.');
        return; // Przerywamy wykonanie, jeśli kategoria jest pusta
    }

    const formData = new FormData(this); // Pobieramy wszystkie dane z formularza

    // Wysyłamy dane formularza za pomocą AJAX
    fetch('backend/update_dish.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                alert('Potrawa została zaktualizowana.');
                updateDishRow(data.dish);
                document.getElementById('edit-dish-form-container').style.display = 'none';
                loadMenu()
            } else {
                alert(`Błąd aktualizacji: ${data.message}`);
            }
        })
        .catch(error => {
            console.error('Błąd podczas wysyłania danych:', error);
            alert('Wystąpił błąd podczas aktualizacji potrawy.');
        });
});

// Funkcja do aktualizacji wiersza w tabeli
function updateDishRow(updatedDish) {
    const row = document.querySelector(`button[data-id="${updatedDish.id}"]`).closest('tr');
    if (!row) {
        console.error('Nie znaleziono wiersza do aktualizacji!');
        return;
    }

    row.querySelector('td:nth-child(2)').textContent = updatedDish.nazwa;
    row.querySelector('td:nth-child(3)').textContent = updatedDish.cena.toFixed(2);
    row.querySelector('td:nth-child(4)').textContent = updatedDish.opis;
    row.querySelector('td:nth-child(5) img').src = updatedDish.zdjecie || '';
    row.querySelector('td:nth-child(6)').textContent = updatedDish.kategoria;
}




</script>




<script>
    function showSection(sectionId) {
        // Hide all sections
        const sections = document.querySelectorAll('.content');
        sections.forEach(section => {
            section.classList.remove('active');
        });

        // Show the selected section
        const targetSection = document.getElementById(sectionId);
        if (targetSection) {
            targetSection.classList.add('active');
        }
    }
    // Możesz dodać funkcje dla guzików, np. zmiana statusu rezerwacji

document.querySelectorAll('.cancel').forEach(button => {
    button.addEventListener('click', function() {
        const row = this.closest('tr');
        const statusCell = row.querySelector('td:nth-child(10)');
        statusCell.textContent = 'Anulowano';
        statusCell.style.color = '#ff6666';
    });
});

document.querySelectorAll('.confirm').forEach(button => {
    button.addEventListener('click', function() {
        const row = this.closest('tr');
        const statusCell = row.querySelector('td:nth-child(10)');
        statusCell.textContent = 'Zatwierdzono';
        statusCell.style.color = '#4CAF50';
    });
});

</script>

</body>
</html>
