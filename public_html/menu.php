<?php
require_once 'database/db.php';
session_start();

// Obsługa wylogowania
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['logout']) && $_POST['logout'] == true) {
    session_destroy();
    header("Location: logowanie.php");
    exit();
}

// Pobieranie kategorii potraw
$queryCategories = "SELECT DISTINCT Kategoria FROM Potrawy ORDER BY Kategoria";
$resultCategories = mysqli_query($conn, $queryCategories);
$categories = mysqli_fetch_all($resultCategories, MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons" />
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.8.2/css/all.css" />
    <link rel="stylesheet" href="css/menu.css" />
    <title>Frapza- Pełne Menu</title>
    <link rel="icon" href="assets/frapza_logo.png" type="image/png" />
</head>

<body>
    <main class="container">
        <?php foreach ($categories as $category): ?>
            <div class="menu">
                <h2 class="menu-group-heading"><?php echo htmlspecialchars($category['Kategoria']); ?></h2>
                <div class="menu-group">
                    <?php
                    // Zapytanie do pobrania tylko aktywnych potraw
                    $queryItems = "SELECT PotrawaId, Nazwa, Cena, Opis, Zdjecie FROM Potrawy WHERE Kategoria = ? AND Aktywny = 1";
                    $stmt = mysqli_prepare($conn, $queryItems);
                    mysqli_stmt_bind_param($stmt, "s", $category['Kategoria']);
                    mysqli_stmt_execute($stmt);
                    $resultItems = mysqli_stmt_get_result($stmt);
                    $items = mysqli_fetch_all($resultItems, MYSQLI_ASSOC);

                    // Sprawdzanie, czy są dostępne aktywne potrawy w danej kategorii
                    if (count($items) > 0):
                        foreach ($items as $item):
                    ?>
                        <div class="menu-item">
                            <img
                                src="<?php echo htmlspecialchars($item['Zdjecie']); ?>"
                                alt="Image of <?php echo htmlspecialchars($item['Nazwa']); ?>"
                                class="menu-item-img"
                            />
                            <div class="menu-item-text">
                                <h3 class="menu-item-heading">
                                    <span class="menu-item-name"><?php echo htmlspecialchars($item['Nazwa']); ?></span>
                                    <span class="menu-item-price"><?php echo number_format($item['Cena'], 2); ?> zł</span>
                                </h3>
                                <p class="menu-item-desc"><?php echo htmlspecialchars($item['Opis']); ?></p>
                            </div>
                        </div>
                    <?php
                        endforeach;
                    else:
                        echo "<p>Brak aktywnych potraw w tej kategorii.</p>";
                    endif;
                    ?>
                </div>
            </div>
        <?php endforeach; ?>
    </main>
    <script src="src/app.js"></script>
</body>
</html>
