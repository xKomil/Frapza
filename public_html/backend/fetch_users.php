<?php
require_once('../database/db.php');

$query = "SELECT * FROM Uzytkownicy";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>
            <td>{$row['UzytkownikId']}</td>
            <td>{$row['Imie']}</td>
            <td>{$row['Nazwisko']}</td>
            <td>{$row['Email']}</td>
            <td>{$row['NumerTelefonu']}</td>
            <td>{$row['Rola']}</td>
            <td><button class='edit button-86 margin-bottom-smf' data-id='{$row['UzytkownikId']}'>Zmień</button> <button class='delete button-86' data-id='{$row['UzytkownikId']}'>Usuń</button></td>
        </tr>";
    }
} else {
    echo "<tr><td colspan='7'>Brak użytkowników w bazie</td></tr>";
}
?>
