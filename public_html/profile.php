<?php
require_once 'database/db.php'; // Plik z połączeniem do bazy danych
session_start();

// Sprawdzenie, czy użytkownik jest zalogowany
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: logowanie.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$message = '';

// Domyślne zdjęcie profilowe
$defaultPhotoPath = 'assets/uploads/default_profile.jpg';

// Pobranie danych użytkownika
$userQuery = $conn->prepare("SELECT * FROM Uzytkownicy WHERE UzytkownikId = ?");
$userQuery->bind_param('i', $user_id);
$userQuery->execute();
$result = $userQuery->get_result();
$userData = $result->fetch_assoc();

// Jeśli użytkownik nie ma zdjęcia, ustaw domyślne
if (empty($userData['Zdjecie'])) {
    $userData['Zdjecie'] = $defaultPhotoPath;
}

// Jeśli zdjęcie jest lokalnym plikiem (rozpoczyna się od 'assets/uploads/'), to przypisujemy odpowiednią ścieżkę
if (strpos($userData['Zdjecie'], 'assets/uploads/') === 0) {
    $userData['Zdjecie'] = htmlspecialchars($userData['Zdjecie']);
} else {
    // Jeśli to jest zewnętrzny link, używamy go bez zmian
    $userData['Zdjecie'] = htmlspecialchars($userData['Zdjecie']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? $userData['Email'];
    $phone = $_POST['phone'] ?? $userData['NumerTelefonu'];

    // Walidacja e-maila
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Podaj poprawny adres e-mail.';
    }
    // Walidacja numeru telefonu (tylko cyfry, długość 9)
    elseif (!preg_match('/^\d{9}$/', $phone)) {
        $message = 'Numer telefonu musi składać się z 9 cyfr.';
    } else {
        // Obsługa przesłania zdjęcia
        $photoPath = $userData['Zdjecie']; // Domyślnie zachowujemy istniejące zdjęcie
        if (isset($_FILES['profile-pic']) && $_FILES['profile-pic']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = 'assets/uploads/'; // Ścieżka do folderu z plikami
            $fileName = uniqid() . '_' . basename($_FILES['profile-pic']['name']); // Unikalna nazwa pliku
            $targetFile = $uploadDir . $fileName;

            // Upewnij się, że folder istnieje, w razie potrzeby go twórz
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            // Przenieś przesłany plik do docelowego folderu
            if (move_uploaded_file($_FILES['profile-pic']['tmp_name'], $targetFile)) {
                $photoPath = $targetFile; // Zapisujemy ścieżkę pliku
            } else {
                $message = 'Nie udało się zapisać zdjęcia profilowego.';
            }
        }

        // Aktualizacja danych w bazie
        $updateQuery = $conn->prepare("
            UPDATE Uzytkownicy 
            SET Email = ?, NumerTelefonu = ?, Zdjecie = ? 
            WHERE UzytkownikId = ?
        ");
        $updateQuery->bind_param('sssi', $email, $phone, $photoPath, $user_id);
        if ($updateQuery->execute()) {
            $message = 'Dane zostały zaktualizowane!';
            header('Location: profile.php');
            exit;
        } else {
            $message = 'Błąd podczas aktualizacji danych: ' . $conn->error;
        }
    }
}

// Pobranie rezerwacji użytkownika
$reservationsQuery = $conn->prepare("SELECT * FROM RezerwacjeSale WHERE UzytkownikId = ?");
$reservationsQuery->bind_param('i', $user_id);
$reservationsQuery->execute();
$reservationsResult = $reservationsQuery->get_result();
$reservations = [];
while ($row = $reservationsResult->fetch_assoc()) {
    $reservations[] = $row;
}

// Obliczanie ceny

function calculateEstimatedCost($reservation, $conn) {
    $menuItems = [$reservation['Przystawka'], $reservation['DanieGlowne'], $reservation['Ciasto']];
    $guestCount = (int)$reservation['LiczbaOsob'];

    // Sprawdzanie, czy elementy są ID (liczby), czy nazwami
    $menuIds = [];
    foreach ($menuItems as $item) {
        if (is_numeric($item)) {
            $menuIds[] = $item;
        }
    }

    $totalDishCost = 0;
    $query = "";

    // Obsługa ID potraw
    if (!empty($menuIds)) {
        $placeholders = implode(',', array_fill(0, count($menuIds), '?'));
        $query = "SELECT Cena FROM Potrawy WHERE PotrawaId IN ($placeholders)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param(str_repeat('i', count($menuIds)), ...$menuIds);
    } else {
        // Obsługa nazw potraw
        $placeholders = implode(',', array_fill(0, count($menuItems), '?'));
        $query = "SELECT Cena FROM Potrawy WHERE Nazwa IN ($placeholders)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param(str_repeat('s', count($menuItems)), ...$menuItems);
    }

    if (!$stmt) {
        return "Błąd zapytania: " . $conn->error;
    }

    $stmt->execute();
    $result = $stmt->get_result();

    // Sumowanie cen potraw
    while ($row = $result->fetch_assoc()) {
        $totalDishCost += (float)$row['Cena'];
    }

    $costPerGuest = $totalDishCost;
    $missingGuests = max(42 - $guestCount, 0);
    $additionalCost = $missingGuests * 50;

    $estimatedCost = ($costPerGuest * $guestCount) + $additionalCost;

    return number_format($estimatedCost, 2, '.', '') . " zł";
}

// Przykład użycia
foreach ($reservations as $index => $reservation) {
    $reservations[$index]['CenaSzacunkowa'] = calculateEstimatedCost($reservation, $conn);
}

// Zamiana id na nazwe

function getDishNameById($id, $conn) {
    $stmt = $conn->prepare("SELECT Nazwa FROM Potrawy WHERE PotrawaId = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        return $row['Nazwa'];
    }
    return null;
}

// Wiadomości

// Funkcja do pobierania wiadomości użytkownika i odpowiedzi
function fetchUserMessages($conn, $userId) {
    $sql = "SELECT WiadomoscId, Tresc, DataCzas, Przeczytana, Odpowiedz
            FROM Wiadomosci
            WHERE UserId = ?
            ORDER BY DataCzas DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    $messages = [];
    while ($row = $result->fetch_assoc()) {
        $messages[] = $row;
    }
    return $messages;
}

// Pobieranie wiadomości użytkownika
$userId = $_SESSION['user_id']; // Zakładamy, że user_id jest w sesji
$userMessages = fetchUserMessages($conn, $userId);

?>


<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
    <meta name="description" content="Frapza jest stroną restauracji" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap"
      rel="stylesheet"
    />
    <link rel="stylesheet" href="css/style.css?v=<?php echo filemtime('css/style.css'); ?>" />
    <link rel="stylesheet" href="css/general.css?v=<?php echo filemtime('css/general.css'); ?>" />
    <link rel="stylesheet" href="css/queries.css?v=<?php echo filemtime('css/queries.css'); ?>" />
    <link rel="stylesheet" href="css/profile.css?v=<?php echo filemtime('css/profile.css'); ?>" />
    



    <!-- import ikon i skryptu -->
    <script
      type="module"
      src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"
    ></script>  
    <script
      nomodule
      src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"
    ></script>
    <script defer src="js/script.js?v=<?php echo filemtime('js/script.js'); ?>"></script>
    
    <script src="https://cdn.jsdelivr.net/npm/animejs@3.2.1/lib/anime.min.js"></script>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

    <title>Twój profil - Frapza</title>
    <link rel="icon" href="assets/frapza_logo.png" type="image/png" />
</head>
<body>
    <main>
    <h1 class="heading-profile">Witaj w swoim profilu!</h1>
    <section class="section-profile">
        <div class="profile-section container">
            <!-- Pasek nawigacyjny -->
            <div class="swiper-nav">
                <a href="index.php" class="button-86">Powrót</a>
                <button class="btn button-86 active" data-tab="user-data">Edytuj dane</button>
                <button class="btn button-86" data-tab="reservations">Rezerwacje</button>
                <button class="btn button-86" data-tab="wiadomosci">Wiadomości</button>
            </div>
            <!-- Sekcja: Edytuj dane -->
            <div id="user-data" class="tab-content active">
                <div class="user-profile-info">
                <img src="<?php echo htmlspecialchars($userData['Zdjecie']); ?>" 
                    alt="Zdjęcie profilowe" class="profile-picture">
                    <p class="name"><?php echo htmlspecialchars($userData['Imie'] . ' ' . $userData['Nazwisko']); ?></p>
                    <p class="email"><?php echo htmlspecialchars($userData['Email']); ?></p>
                    <p class="phone"><?php echo htmlspecialchars($userData['NumerTelefonu']); ?></p>
                </div>
                <h2>Edytuj dane</h2>
                <?php if ($message) echo "<p class='message'>$message</p>"; ?>
                <form method="post" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="email">E-mail:</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($userData['Email']); ?>">
                    </div>
                    <div class="form-group">
                        <label for="phone">Numer telefonu:</label>
                        <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($userData['NumerTelefonu']); ?>">
                    </div>
                    <div class="form-group">
                        <label for="profile-pic">Zdjęcie profilowe:</label>
                        <input type="file" id="profile-pic" name="profile-pic">
                    </div>
                    <button type="submit" class="save-btn">Zapisz zmiany</button>
                </form>     
            </div>
            <!-- Sekcja: Rezerwacje -->     
            <div id="reservations" class="tab-content">
            <h2 class ="messages-heading">Tutaj znajdziesz swoje rezerwacje sali</h2>
                <?php foreach ($reservations as $index => $reservation): ?>
                    <div class="reservation">
                        <p>Rezerwacja #<?php echo $reservation['RezerwacjaId']; ?></p>
                        <div class="buttons">
                            <button class="toggle-details button-86" data-id="<?php echo $index; ?>">Szczegóły</button> 
                            <button class="cancel-btn button-86" data-id="<?php echo $reservation['RezerwacjaId']; ?>">Anuluj</button>
                        </div>  
                        <div class="details" id="details-<?php echo $index; ?>" style="display: none;">
                            <p>Status rezerwacji: <?php echo htmlspecialchars($reservation['Status'] == 1) ? 'Potwierdzone' : 'Czeka na potwierdzenie'; ?></p>
                            <p>Menu: 
                                <?php 
                                // Pobierz nazwy potraw na podstawie ID
                                $przystawkaNazwa = isset($reservation['Przystawka']) ? getDishNameById($reservation['Przystawka'], $conn) : null;
                                $danieGlowneNazwa = isset($reservation['DanieGlowne']) ? getDishNameById($reservation['DanieGlowne'], $conn) : null;
                                $ciastoNazwa = isset($reservation['Ciasto']) ? getDishNameById($reservation['Ciasto'], $conn) : null;

                                // Wyświetl nazwy potraw lub ID (jeśli brak nazwy)
                                echo htmlspecialchars($przystawkaNazwa ?? $reservation['Przystawka'] ?? 'Brak') . ', ';
                                echo htmlspecialchars($danieGlowneNazwa ?? $reservation['DanieGlowne'] ?? 'Brak') . ', ';
                                echo htmlspecialchars($ciastoNazwa ?? $reservation['Ciasto'] ?? 'Brak');
                                ?>
                            </p>
                            <p>Ilość gości: <?php echo htmlspecialchars($reservation['LiczbaOsob']); ?></p>
                            <p>Data: <?php echo htmlspecialchars($reservation['DataRezerwacji']); ?></p>
                            <p>Godzina: <?php echo htmlspecialchars($reservation['GodzinaRozpoczecia']); ?></p>
                            <p>Cena końcowa szacunkowo: <span class="cena"><?php echo htmlspecialchars($reservation['CenaSzacunkowa']); ?></span></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div id="wiadomosci" class="tab-content">
                <h2 class ="messages-heading">Twoje wiadomości</h2>
                <div class="table-wrapper">
                <table class="fl-table">
                    <thead>
                        <tr>
                            <th>Treść wiadomości</th>
                            <th>Data wysłania</th>
                            <th>Przeczytana</th>
                            <th>Odpowiedź</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (!empty($userMessages)): ?>
                        <?php foreach ($userMessages as $message): ?>
                            <tr>
                                <td><?= htmlspecialchars($message['Tresc']); ?></td>
                                <td><?= htmlspecialchars($message['DataCzas']); ?></td>
                                <td><?= $message['Przeczytana'] ? 'Tak' : 'Nie'; ?></td>
                                <td><?= $message['Odpowiedz'] ? htmlspecialchars($message['Odpowiedz']) : 'Brak odpowiedzi'; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5">Nie masz jeszcze żadnych wiadomości.</td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
                </div>
            </div>

        </div>
    </section>
    </main>

<script>    
    document.addEventListener("DOMContentLoaded", () => {
    const tabButtons = document.querySelectorAll(".btn");
    const tabContents = document.querySelectorAll(".tab-content");

    tabButtons.forEach((button) => {
        button.addEventListener("click", () => {
            // Dezaktywuj wszystkie zakładki i przyciski
            tabButtons.forEach((btn) => btn.classList.remove("active"));
            tabContents.forEach((content) => content.classList.remove("active"));

            // Aktywuj bieżący element
            const tabId = button.getAttribute("data-tab");
            button.classList.add("active");
            document.getElementById(tabId).classList.add("active");
        });
    });

    // Obsługa przycisków anulowania rezerwacji
    const cancelButtons = document.querySelectorAll(".cancel-btn");
    cancelButtons.forEach((btn) => {
        btn.addEventListener("click", () => {
            const reservationId = btn.getAttribute("data-id");
            if (confirm("Czy na pewno chcesz anulować tę rezerwację?")) {
                fetch(`backend/cancel_reservation.php?id=${reservationId}`)
                    .then(response => response.text())
                    .then(data => {
                        if (data.trim() === 'success') {
                            alert("Rezerwacja anulowana!");
                            location.reload(); // Odświeżenie strony
                        } else if (data.trim() === 'invalid') {
                            alert("Nieprawidłowy identyfikator rezerwacji.");
                        } else {
                            alert("Błąd podczas anulowania rezerwacji.");
                        }
                    })
                    .catch(() => alert("Błąd połączenia z serwerem."));
            }
        });
    });


    const detailsButtons = document.querySelectorAll(".details-btn");
    detailsButtons.forEach((btn) => {
        btn.addEventListener("click", () => {
            alert("Szczegóły rezerwacji: " + btn.parentElement.innerHTML);
        });
    }); 
});
document.addEventListener("DOMContentLoaded", () => {
    const tabButtons = document.querySelectorAll(".btn");
    const tabContents = document.querySelectorAll(".tab-content");

    tabButtons.forEach((button) => {
        button.addEventListener("click", () => {
            tabButtons.forEach((btn) => btn.classList.remove("active"));
            tabContents.forEach((content) => content.classList.remove("active"));

            const tabId = button.getAttribute("data-tab");
            button.classList.add("active");
            document.getElementById(tabId).classList.add("active");
        });
    });
});
function toggleDetails(id) {
    const details = document.getElementById(`details-${id}`);
    if (details.style.display === "none") {
        details.style.display = "block";
    } else {
        details.style.display = "none";
    }
}
document.addEventListener('DOMContentLoaded', () => {
    const buttons = document.querySelectorAll('.toggle-details');

    buttons.forEach(button => {
        button.addEventListener('click', () => {
            const id = button.getAttribute('data-id');
            const details = document.getElementById(`details-${id}`);

            // Zamień widoczność szczegółów
            if (details.style.display === 'none' || details.style.display === '') {
                details.style.display = 'block';
            } else {
                details.style.display = 'none';
            }
        });
    });
});



</script>
<footer>
    
</footer>
</body>

</html>