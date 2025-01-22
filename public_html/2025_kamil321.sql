-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Czas generowania: 22 Sty 2025, 09:42
-- Wersja serwera: 10.3.39-MariaDB-0ubuntu0.20.04.2
-- Wersja PHP: 7.4.3-4ubuntu2.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Baza danych: `2025_kamil321`
--

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `GodzinyOtwarcia`
--

CREATE TABLE `GodzinyOtwarcia` (
  `DzienTygodnia` enum('Poniedziałek','Wtorek','Środa','Czwartek','Piątek','Sobota','Niedziela') NOT NULL,
  `GodzinaOtwarcia` time DEFAULT NULL,
  `GodzinaZamkniecia` time DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Zrzut danych tabeli `GodzinyOtwarcia`
--

INSERT INTO `GodzinyOtwarcia` (`DzienTygodnia`, `GodzinaOtwarcia`, `GodzinaZamkniecia`) VALUES
('Poniedziałek', '09:00:00', '00:00:00'),
('Wtorek', '09:00:00', '00:00:00'),
('Środa', '09:00:00', '00:00:00'),
('Czwartek', '09:00:00', '00:00:00'),
('Piątek', '09:00:00', '00:00:00'),
('Sobota', '09:00:00', '00:00:00'),
('Niedziela', '00:00:00', '00:00:00');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `Opinie`
--

CREATE TABLE `Opinie` (
  `OpiniaId` int(11) NOT NULL,
  `UzytkownikId` int(11) DEFAULT NULL,
  `Ocena` int(11) DEFAULT NULL CHECK (`Ocena` between 1 and 5),
  `Opis` text DEFAULT NULL,
  `DataDodania` date DEFAULT curdate()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Zrzut danych tabeli `Opinie`
--

INSERT INTO `Opinie` (`OpiniaId`, `UzytkownikId`, `Ocena`, `Opis`, `DataDodania`) VALUES
(2, 2, 4, 'Bardzo miło, ale było trochę tłoczno.', '2023-11-09'),
(3, 5, 5, 'Polecam każdemu! Pyszne jedzenie i wspaniały klimat.', '2023-11-10'),
(4, 6, 3, 'Jedzenie było smaczne, ale obsługa trochę wolna.', '2023-11-10'),
(5, 5, 5, 'Opis', '2024-11-12');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `Potrawy`
--

CREATE TABLE `Potrawy` (
  `PotrawaId` int(11) NOT NULL,
  `Nazwa` varchar(100) NOT NULL,
  `Cena` decimal(10,2) NOT NULL,
  `Opis` text DEFAULT NULL,
  `Zdjecie` varchar(255) DEFAULT NULL,
  `DataDodania` datetime DEFAULT current_timestamp(),
  `Kategoria` varchar(50) DEFAULT NULL,
  `Aktywny` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Zrzut danych tabeli `Potrawy`
--

INSERT INTO `Potrawy` (`PotrawaId`, `Nazwa`, `Cena`, `Opis`, `Zdjecie`, `DataDodania`, `Kategoria`, `Aktywny`) VALUES
(4, 'Ciasto czekoladowe', '16.00', 'Puszyste ciasto czekoladowe z kremem czekoladowym', 'https://cdn.pixabay.com/photo/2016/11/22/18/52/cake-1850011_1280.jpg', '2024-11-09 14:26:46', 'Ciasta', 1),
(5, 'Serniczek', '18.00', 'Sernik na zimno z musem owocowym', 'https://ilovebake.pl/wp-content/uploads/2023/05/sernik-na-zimno-z-truskawkami-3.jpg', '2024-11-09 14:26:46', 'Ciasta', 1),
(6, 'Tarta owocowa', '16.00', 'Tarta z kremem waniliowym i świeżymi owocami', 'https://www.sajkofankasmaku.pl/wp-content/uploads/2024/08/tarta-2.jpg', '2024-11-09 14:26:46', 'Ciasta', 1),
(7, 'Brownie', '14.00', 'Ciasto czekoladowe z orzechami', 'https://www.przyslijprzepis.pl/media/cache/big/uploads/media/recipe/0008/39/ciasto-czekoladowe-z-orzechami-wloskimi.jpeg', '2024-11-09 14:26:46', 'Ciasta', 1),
(8, 'Pavlova', '20.00', 'Beza z owocami sezonowymi', 'https://wszystkiegoslodkiego.pl/storage/images/202211/tort-bezowy-low.jpg', '2024-11-09 14:26:46', 'Ciasta', 1),
(9, 'Margherita', '25.00', 'Klasyczna pizza z sosem pomidorowym, mozzarellą i bazylią', 'https://cookmagazine.pl/wp-content/uploads/2023/11/fotolia_81597702_subscription_monthly_m-3.jpg', '2024-11-09 14:27:06', 'Pizza', 1),
(10, 'Pepperoni', '28.00', 'Pizza z salami pepperoni i mozzarellą', 'https://cdn.aniagotuje.com/pictures/articles/2022/08/31553217-v-1500x1500.jpg', '2024-11-09 14:27:06', 'Pizza', 1),
(11, 'Hawajska', '30.00', 'Pizza z szynką, ananasem i serem', 'https://cms.pogotujmy.pl/wp-content/uploads/2016/11/DSC_0442.jpg', '2024-11-09 14:27:06', 'Pizza', 1),
(12, 'Wegetariańska', '27.00', 'Pizza z warzywami, pomidorami i oliwkami', 'https://blogger.googleusercontent.com/img/b/R29vZ2xl/AVvXsEiBmQs9nH3FlrdcMY7HGaXQT6hQoRlFmhi3v-dZDZpta2sPkJTl6vuT9bFq59gFFPxzaKsHHrhECE3GI_Hk2_zNe2oBdskiSe0wElNOjqqJfZL5P7hFV1DhOD4FNCgSw28tBiXUnNYuSxY/s1600/Pizza+z+%C5%81ososiem+18.jpg', '2024-11-09 14:27:06', 'Pizza', 1),
(13, 'Włoska', '32.00', 'Pizza z szynką parmeńską, rukolą i parmezanem', 'https://kuchnia.it/Img/Pizza/Szynka_parma/pizza_parma1_mw.jpg', '2024-11-09 14:27:06', 'Pizza', 1),
(14, 'Kotlety schabowe', '35.00', 'Soczyste kotlety schabowe z ziemniakami i surówką', 'https://bi.im-g.pl/im/6a/6b/1b/z28750698Q,Jak-zrobic-kotlet-schabowy--Ten-prosty-trik-sprawi.jpg', '2024-11-09 14:27:16', 'Dania Główne', 1),
(15, 'Stek wołowy', '60.00', 'Stek wołowy podany z frytkami i sosem pieprzowym', 'https://niespiebopieke.pl/wp-content/uploads/2020/12/1365-102411-3.jpg', '2024-11-09 14:27:16', 'Dania Główne', 1),
(16, 'Spaghetti Bolognese', '30.00', 'Makaron spaghetti w sosie bolognese', 'https://aniastarmach.pl/content/uploads/2016/02/makaron-spaghetti-bolognese-1.jpg', '2024-11-09 14:27:16', 'Dania Główne', 1),
(17, 'Pierogi ruskie', '28.00', 'Domowe pierogi z twarogiem i ziemniakami', 'https://akademiasmaku.pl/storage/7202/conversions/tradycyjne-pierogi-ruskie-4370-single.webp', '2024-11-09 14:27:16', 'Dania Główne', 1),
(18, 'Filet z łososia', '45.00', 'Grillowany filet z łososia z warzywami', 'https://saproduwielbiaplmmedia.blob.core.windows.net/media/recipes/images/1699973472780.jpeg', '2024-11-09 14:27:16', 'Dania Główne', 1),
(19, 'Jajecznica na maśle', '12.00', 'Jajecznica z dwóch jaj na maśle', 'https://polki.pl/foto/4_3_LARGE/ile-kalorii-ma-jajecznica-z-2-jajek-duzo-zalezy-od-sposobu-przygotowania-2478476.jpg', '2024-11-09 14:27:27', 'Śniadania', 1),
(20, 'Omlet z warzywami', '14.00', 'Omlet z papryką, cebulą i pomidorami', 'https://www.jajabrychcy.pl/wp-content/uploads/2014/03/omlet-z-papryka-pomidorami-ziolami.jpg', '2024-11-09 14:27:27', 'Śniadania', 1),
(21, 'Tosty francuskie', '18.00', 'Tosty francuskie z cukrem pudrem i syropem klonowym', 'https://pysznadieta.pl/wp-content/uploads/2024/09/hana.life_Sweet_French_toast_f72854e1-e1a9-4a66-ad78-68f162cf548e_Easy-Resize.com_-800x840.jpg', '2024-11-09 14:27:27', 'Śniadania', 1),
(22, 'Granola z jogurtem', '16.00', 'Granola z jogurtem naturalnym i owocami', 'https://naszprzepis.pl/wp-content/uploads/2020/06/jogurt_z_granloa_i_owocami_land.jpg', '2024-11-09 14:27:27', 'Śniadania', 1),
(23, 'Pancakes', '20.00', 'Placuszki z owocami i syropem', 'https://www.przyslijprzepis.pl/media/cache/big/uploads/media/recipe/0007/23/pancakes-z-syropem-klonowym-i-owocami-latapancakes-z-syropem-klonowym-i-owocami-lata.jpeg', '2024-11-09 14:27:27', 'Śniadania', 1),
(24, 'Kawa latte', '10.00', 'Kawa z mlekiem, podana z pianką', 'https://cafessima.pl/wp-content/uploads/2021/01/latte-macchiato.jpeg', '2024-11-09 14:27:39', 'Napoje', 1),
(25, 'Herbata zielona', '8.00', 'Świeża herbata zielona', 'https://media.istockphoto.com/id/628986454/pl/zdj%C4%99cie/szklana-fili%C5%BCanka-ze-%C5%9Bwie%C5%BC%C4%85-zielon%C4%85-herbat%C4%85.jpg?s=612x612&w=0&k=20&c=OUuJgnpDzhLN7ObjQAcy8AF4j9SDh0SFd5DT3zpRJ-4=', '2024-11-09 14:27:39', 'Napoje', 1),
(26, 'Sok pomarańczowy', '12.00', 'Świeżo wyciskany sok pomarańczowy', 'https://vianto.pl/system/attachments/files/000/000/371/original/zdrowe_soki_owocowe_wyciskane_w_domu_-_vianto.jpg?1485443702', '2024-11-09 14:27:39', 'Napoje', 1),
(27, 'Woda mineralna', '6.00', 'Woda gazowana lub niegazowana', 'https://swiecickizdroj.pl/wp-content/uploads/2018/07/produkt_niegazowana_premium.jpg', '2024-11-09 14:27:39', 'Napoje', 1),
(28, 'Koktajl owocowy', '15.00', 'Koktajl z mango, banana i pomarańczy', 'https://static.fajnegotowanie.pl/media/uploads/media_image/original/przepis/3368/koktajl-z-mango.jpg', '2024-11-09 14:27:39', 'Napoje', 1),
(29, 'Bruschetta', '12.00', 'Grzanki z pomidorami, bazylią i czosnkiem', 'https://kuchnialidla.pl/img/PL/1250x700/e87b99ca1e5e-fc0b8c9ad26c-kw40-2023-kuchnia-lidla-bruschetta-z-pomidorami_1250x700.webp', '2024-11-09 14:27:51', 'Przystawki', 1),
(30, 'Carpaccio z wołowiny', '22.00', 'Cienko krojony stek wołowy z rukolą i parmezanem', 'https://cdn.aniagotuje.com/pictures/articles/2021/05/14747060-v-1500x1500.jpg', '2024-11-09 14:27:51', 'Przystawki', 1),
(31, 'Sałatka grecka', '18.00', 'Sałatka z fetą, oliwkami i pomidorami', 'https://cdn.aniagotuje.com/pictures/articles/2021/04/14728791-v-1500x1500.jpg', '2024-11-09 14:27:51', 'Przystawki', 1),
(32, 'Krewetki w czosnku', '25.00', 'Krewetki podsmażane na czosnku i oliwie z oliwek', 'https://az.przepisy.pl/www-przepisy-pl/www.przepisy.pl/przepisy3ii/img/variants/800x0/krewetki-w-masle-czosnkowym.jpg', '2024-11-09 14:27:51', 'Przystawki', 1),
(33, 'Tatar z łososia', '28.00', 'Tatar z świeżego łososia z cebulą i koprem', 'https://praktykulinarni.com/wp-content/uploads/2022/12/tatar-z-lososia.jpg', '2024-11-09 14:27:51', 'Przystawki', 1),
(34, 'Tarta Cytrynowa z Bezą', '23.00', 'Kruche ciasto z kremem cytrynowym i delikatną, wypiekaną bezą.', 'https://aniastarmach.pl/content/uploads/2022/08/tarta-cytrynowa-z-beza-wloska-13.jpg', '2024-11-09 18:25:49', 'Ciasta', 1),
(35, 'Ciasto Opera', '28.00', 'Eleganckie, wielowarstwowe ciasto francuskie z kremem kawowym i czekoladą.', 'https://staticsmaker.iplsc.com/smaker_production_2023_05_12/1ed9d40b3ecae07530995e271435027e-content.jpg', '2024-11-09 18:25:49', 'Ciasta', 1),
(36, 'Pizza Truflowa', '45.00', 'Pizza na cienkim cieście z truflowym kremem, prosciutto i rukolą.', 'https://www.italiapozaszlakiem.com/wp-content/uploads/2022/11/Pizza-z-truflami-Maveat-02.jpeg', '2024-11-09 18:25:49', 'Pizza', 1),
(37, 'Pizza z Krewetkami i Awokado', '40.00', 'Pizza z krewetkami, awokado, rukolą i parmezanem.', 'https://static.fajnegotowanie.pl/media/uploads/media_image/original/przepis/9849/pizza-z-krewetkami.jpeg', '2024-11-09 18:25:49', 'Pizza', 1),
(38, 'Filet Mignon', '70.00', 'Delikatna polędwica wołowa podawana z puree z trufli i sosem z czerwonego wina.', 'https://www.livinglocurto.com/wp-content/uploads/2019/10/Filet-Mignon-Steak-Easy-Recipe-Living-Locurto.jpg', '2024-11-09 18:25:49', 'Dania główne', 1),
(39, 'Łosoś z Sosem Szafranowym', '58.00', 'Łosoś w sosie szafranowym z dodatkiem szparagów i ziemniaków pieczonych.', 'https://www.poezja-smaku.pl/wp-content/uploads/2015/03/losos-z-gnocci-w-sosie-szafranowym-3.jpg', '2024-11-09 18:25:49', 'Dania główne', 1),
(40, 'Benedyktyńskie Jajka z Łososiem', '30.00', 'Jajka po benedyktyńsku z łososiem na pieczywie z sosem holenderskim.', 'https://assets.tmecosys.com/image/upload/t_web767x639/img/recipe/ras/Assets/c395df9ed0b283c646dd6e4298a96a15/Derivates/6fed1dd5f6bef6149c8c0ed67a297cb09f0aa4a1.jpg', '2024-11-09 18:25:49', 'Śniadania', 1),
(41, 'Owsianka z Chia i Jagodami', '25.00', 'Owsianka na mleku kokosowym z chia, borówkami i migdałami.', 'https://www.herbalife.com/dmassets/regional-reusable-assets/emea/images/ri-chia-blueberry-overnight-oats-recipe-emea.jpg', '2024-11-09 18:25:49', 'Śniadania', 1),
(42, 'Cold Brew z Mlekiem Migdałowym', '16.00', 'Orzeźwiająca kawa z mlekiem migdałowym i syropem waniliowym.', 'https://www.przyjacielekawy.pl/app/uploads/2020/10/shutterstock_1081222316.jpg', '2024-11-09 18:25:49', 'Napoje', 1),
(43, 'Koktajl Zielony Detox', '18.00', 'Koktajl z jarmużu, jabłka, ogórka i spiruliny.', 'https://static.gotujmy.pl/FULL_SIZE/przepisy-na-koktajl-z-jarmuzu-571392.jpg', '2024-11-09 18:25:49', 'Napoje', 1),
(44, 'Ostrygi Rockefeller', '35.00', 'Ostrygi zapiekane z masłem, szpinakiem i parmezanem.', 'https://static.wixstatic.com/media/52436e_71667881c43c46ad8cecedffc844a56a~mv2.jpg/v1/fill/w_2500,h_1761,al_c/52436e_71667881c43c46ad8cecedffc844a56a~mv2.jpg', '2024-11-09 18:25:49', 'Przystawki', 1),
(45, 'Foie Gras z Żurawiną', '50.00', 'Pasztet z gęsiej wątróbki z dodatkiem konfitury z żurawiny.', 'https://dziendobry.tvn.pl/_e/i/najnowsze/cdn-zdjecie-5jxoj1-foie-gras-5401519/alternates/WEBP_LANDSCAPE_1280', '2024-11-09 18:25:49', 'Przystawki', 1);

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `RezerwacjeSale`
--

CREATE TABLE `RezerwacjeSale` (
  `RezerwacjaId` int(11) NOT NULL,
  `UzytkownikId` int(11) NOT NULL,
  `ImieNazwisko` varchar(255) NOT NULL,
  `NumerTelefonu` varchar(15) NOT NULL,
  `LiczbaOsob` int(11) NOT NULL,
  `DataRezerwacji` date NOT NULL,
  `GodzinaRozpoczecia` time NOT NULL,
  `Przystawka` varchar(50) NOT NULL,
  `DanieGlowne` varchar(50) NOT NULL,
  `Ciasto` varchar(50) NOT NULL,
  `Status` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Zrzut danych tabeli `RezerwacjeSale`
--

INSERT INTO `RezerwacjeSale` (`RezerwacjaId`, `UzytkownikId`, `ImieNazwisko`, `NumerTelefonu`, `LiczbaOsob`, `DataRezerwacji`, `GodzinaRozpoczecia`, `Przystawka`, `DanieGlowne`, `Ciasto`, `Status`) VALUES
(84, 5, 'dawdwadawdad', '123123123', 24, '2024-12-16', '15:00:00', '31', '15', '5', 1),
(87, 5, 'Kamil Płocki', '486098521', 30, '2024-12-11', '16:00:00', '30', '14', '7', 0),
(89, 5, 'dwadawd awdawd', '213123213', 14, '2025-01-08', '13:00:00', '45', '39', '5', 1),
(90, 5, 'dwadwad awdawd', '312312312', 7, '2025-02-01', '09:00:00', '29', '38', '34', 1),
(91, 30, 'Swiety Mikolaj', '123123123', 33, '2025-01-22', '22:00:00', '33', '18', '8', 0),
(92, 30, 'Swiety Mikolaj', '787652240', 30, '2025-01-20', '19:00:00', '33', '14', '34', 0),
(93, 30, 'Swiety Mikolaj', '123123123', 1, '2025-01-31', '20:00:00', '29', '38', '34', 1);

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `RezerwacjeStoliki`
--

CREATE TABLE `RezerwacjeStoliki` (
  `RezerwacjaId` int(11) NOT NULL,
  `UzytkownikId` int(10) DEFAULT NULL,
  `ImieNazwisko` varchar(255) NOT NULL,
  `NumerTelefonu` varchar(15) NOT NULL,
  `IloscOsob` int(11) NOT NULL CHECK (`IloscOsob` <= 6),
  `DataRezerwacji` date NOT NULL,
  `GodzinaRozpoczecia` time NOT NULL,
  `Status` tinyint(4) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Zrzut danych tabeli `RezerwacjeStoliki`
--

INSERT INTO `RezerwacjeStoliki` (`RezerwacjaId`, `UzytkownikId`, `ImieNazwisko`, `NumerTelefonu`, `IloscOsob`, `DataRezerwacji`, `GodzinaRozpoczecia`, `Status`) VALUES
(22, NULL, '1', '123123123', 6, '2024-12-06', '16:00:00', 1),
(24, NULL, '1', '123123123', 6, '2024-12-06', '16:00:00', 0),
(25, NULL, '1', '123123123', 6, '2024-12-06', '16:00:00', 0),
(29, NULL, 'dwadawdaw dawdawd', '321321321', 3, '2024-12-04', '10:00:00', 0),
(38, NULL, 'dawda awdwad', '242424424', 1, '2024-12-17', '16:00:00', 0),
(39, NULL, 'dawda awdwad', '123213213', 6, '2024-12-27', '16:00:00', 0),
(40, NULL, 'dawda awdwad', '123123123', 3, '2024-12-20', '16:00:00', 0),
(41, NULL, 'dawda awdwad', '123123123', 3, '2024-12-27', '12:00:00', 0),
(42, NULL, 'dawda awdwad', '123123123', 3, '2025-01-02', '12:00:00', 0),
(43, NULL, '123', '123123213', 1, '2024-12-27', '16:00:00', 0),
(44, NULL, 'dawdaw', '234234234', 3, '2024-12-27', '10:00:00', 0),
(49, NULL, 'asd', '123123123', 2, '2024-12-17', '15:00:00', 0),
(50, NULL, 'dwadawd adwa', '123123123', 4, '2024-12-19', '16:00:00', 0),
(59, NULL, 'dwadaw', '123123123', 3, '2025-01-24', '16:00:00', 0),
(60, NULL, 'Kamil Stępień', '123123123', 5, '2025-01-30', '23:00:00', 1),
(61, NULL, 'Kamil Stępień', '123123123', 2, '2025-01-29', '15:00:00', 0),
(62, NULL, 'dwadwad awdawd', '123213213', 5, '2025-01-16', '16:00:00', 1),
(63, NULL, 'dawda awdwad', '123123123', 4, '2025-01-15', '10:00:00', 0),
(64, NULL, 'dawda awdwad', '321312312', 3, '2025-01-15', '09:00:00', 0),
(65, NULL, 'kdwaofjawgfoag', '456456456', 3, '2025-01-15', '10:00:00', 1),
(66, NULL, 'dawda awdwad', '234244223', 3, '2025-01-15', '09:00:00', 0),
(69, 5, 'Kamil Płocki', '586938485', 5, '2025-01-17', '10:00:00', 0);

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `Uzytkownicy`
--

CREATE TABLE `Uzytkownicy` (
  `UzytkownikId` int(11) NOT NULL,
  `Imie` varchar(50) NOT NULL,
  `Nazwisko` varchar(50) NOT NULL,
  `Email` varchar(100) NOT NULL,
  `Haslo` varchar(255) NOT NULL,
  `NumerTelefonu` varchar(15) DEFAULT NULL,
  `Zdjecie` varchar(255) DEFAULT NULL,
  `Rola` enum('user','pracownik','admin') NOT NULL DEFAULT 'user',
  `DataRejestracji` date NOT NULL DEFAULT curdate()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Zrzut danych tabeli `Uzytkownicy`
--

INSERT INTO `Uzytkownicy` (`UzytkownikId`, `Imie`, `Nazwisko`, `Email`, `Haslo`, `NumerTelefonu`, `Zdjecie`, `Rola`, `DataRejestracji`) VALUES
(2, 'admin', 'Admin', 'admin@admin.com', '$2y$10$S7yVFsvMAQ9w/QznJMTSMeIyY7JVjqxVnwmCAcvDZgKNI3OLHb7qy', '999999999', '', 'admin', '2024-12-19'),
(5, 'Kamil', 'Płocki', 'plociu1104@gmail.com', '$2y$10$S7yVFsvMAQ9w/QznJMTSMeIyY7JVjqxVnwmCAcvDZgKNI3OLHb7qy', '123123123', 'assets/uploads/6758cd4e11fd7_polish-cow-cow.gif', 'user', '2024-12-19'),
(6, 'kamil', 'stepien', 'test@op.pl', '$2y$10$iBTUfDGzEdHs9xj.5akkrOJAEAANgagybgwetgPOhICgLV2oUJ0ty', '123123123', '', 'user', '2024-12-19'),
(7, 'Kamil', 'Stępień', 'kwizo@op.pl', '$2y$10$G8ZhecJ1rWEMK0KMMdrOg.Vkt6jCJYqJT/AMtInLzcv4xe9QS6QUq', '222333444', 'assets/uploads/675972baa024e_Absolute-Cinema-meme-8d317n.jpg', 'pracownik', '2024-12-19'),
(13, 'admin', 'admin', 'admin@op.pl', '$2y$10$aXDQw.XS5akkJZU.UohKM.kDjWL/uw4OpKEZEmyb7HW5SWkgnSusm', '123123123', NULL, 'admin', '2024-12-19'),
(21, 'Paweł', 'Jakiś', 'xd@op.pl', '$2y$10$KAaZyZ8wQqvdK2N/yedgNO1bJQjiKRjji2vDkwuqsxrId9D3tcOwO', '888888888', NULL, 'user', '2024-12-19'),
(24, 'Jakieś', 'dave', 'pracownik@gmail.com', '$2y$10$UGwku4kaXNpoC/0bpD.GxeS8yhgbhpQEIcutEOdb2lY9ZEuFGqoje', '772718241', NULL, 'pracownik', '2024-12-19'),
(25, 'user', 'user', 'user@op.pl', '$2y$10$vK2p/OuALFErarIEX0iuuOrhQxNdsBIvdgv/1oxO.BUosiSx6q8uu', '123123123', NULL, 'user', '2024-12-19'),
(27, 'admin', 'adminosky', 'adminreavz@op.pl', '$2y$10$PFuVj0BrLe5XiBBOXEumV.ddKUeWgDJNN7DUt/2DoVovFap.BLd6O', '912351231', NULL, 'admin', '2025-01-13'),
(28, 'pracownik', 'reavz', 'pracownikreavz@op.pl', '$2y$10$NJEJzEIwwukBy/uGDXimYOXhJo1OFGtFwJR3J9K7DDPnOluMvxAi.', '666777222', NULL, 'pracownik', '2025-01-13'),
(29, 'stepien', 'dwa', 'stepiendwa@op.pl', '$2y$10$uawX87wdm06EcUbLTBnzP.F3GYA4LqP8TIWwNW1Kw2gW445rwMfAK', '123345567', NULL, 'user', '2025-01-13'),
(30, 'Swiety', 'Mikolaj', 'swietym@example.com', '$2y$10$/4TjrypfKd9sOT4Oclj8l.BcmPSaHovWUrV3gw3yNKMwJEBj62xHC', '123444110', NULL, 'user', '2025-01-18');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `Wiadomosci`
--

CREATE TABLE `Wiadomosci` (
  `WiadomoscId` int(11) NOT NULL,
  `UserId` int(11) DEFAULT NULL,
  `Imie` varchar(50) NOT NULL,
  `NumerTelefonu` varchar(15) DEFAULT NULL,
  `Email` varchar(100) NOT NULL,
  `Tresc` text NOT NULL,
  `DataCzas` datetime DEFAULT current_timestamp(),
  `Przeczytana` tinyint(1) DEFAULT 0,
  `Odpowiedz` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Zrzut danych tabeli `Wiadomosci`
--

INSERT INTO `Wiadomosci` (`WiadomoscId`, `UserId`, `Imie`, `NumerTelefonu`, `Email`, `Tresc`, `DataCzas`, `Przeczytana`, `Odpowiedz`) VALUES
(34, NULL, 'dawdaw', '123123123', 'kamilplocki13@gmail.com', 'dwadwadawdawdawdawdwadwadawdawdwadaw', '2024-12-10 19:44:24', 0, 'dwadawdwadawdawdawwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwww'),
(35, NULL, 'Kamil', '123123123', 'kamilplocki13@gmail.com', 'wadwadawdwadwafawgawgaw213123 123 21312 32dwad', '2024-12-10 19:49:28', 1, 'xpp'),
(38, 7, 'Kamil', '123123123', 'kwizo@op.pl', 'siemano, hehehehehe', '2024-12-12 10:07:29', 1, '123123123123'),
(39, 7, 'kamil', '123123123', 'stepienmontaz@gmail.com', 'awdawdawfawfwawfgawfasewgaewg', '2024-12-12 10:09:32', 0, '2'),
(40, 7, 'kamil', '123123123', 'stepienmontaz@gmail.com', 'awdawdawfawfwawfgawfasewgaewg', '2024-12-12 10:10:27', 1, 'xpp'),
(41, 7, 'Janek', '455213213', 'admin@admin.pl', 'My scars remind me that I did indeed survive my deepest wounds. That in itself is an accomplishment. And they bring to mind something else, too. They remind me that the damage life has inflicted on me has, in many places, left me stronger and more resilient. What hurt me in the past has actually made me better equipped to face the present.', '2024-12-12 10:11:37', 1, '23'),
(42, 5, 'dawdaw', '123123123', 'kamilplocki13@gmail.com', 'dawdawdwadawdawdawdddwa', '2024-12-13 20:56:24', 1, 'hej'),
(43, NULL, 'Kamil', '213123123', 'dawdaw@dawdawd.pl', 'dawdawdawdawd awd awdawdawd ad awd waddwadwadwawda', '2025-01-07 13:25:57', 0, NULL),
(44, 5, 'Kamil', '321321312', 'kamilplocki13@gmail.com', 'dwadawdawd awdawd wad wawd ad aw daw dwawd a wda wdad wa1111 ', '2025-01-07 13:26:42', 1, '1'),
(45, 30, 'Swiety', '123123123', 'swietym@example.com', 'eloeloeloelo', '2025-01-18 20:01:04', 0, NULL);

--
-- Indeksy dla zrzutów tabel
--

--
-- Indeksy dla tabeli `GodzinyOtwarcia`
--
ALTER TABLE `GodzinyOtwarcia`
  ADD PRIMARY KEY (`DzienTygodnia`);

--
-- Indeksy dla tabeli `Opinie`
--
ALTER TABLE `Opinie`
  ADD PRIMARY KEY (`OpiniaId`),
  ADD KEY `UzytkownikId` (`UzytkownikId`);

--
-- Indeksy dla tabeli `Potrawy`
--
ALTER TABLE `Potrawy`
  ADD PRIMARY KEY (`PotrawaId`);

--
-- Indeksy dla tabeli `RezerwacjeSale`
--
ALTER TABLE `RezerwacjeSale`
  ADD PRIMARY KEY (`RezerwacjaId`),
  ADD KEY `idx_sala_data_godzina` (`DataRezerwacji`,`GodzinaRozpoczecia`),
  ADD KEY `FK_RezerwacjeSale_Uzytkownicy` (`UzytkownikId`);

--
-- Indeksy dla tabeli `RezerwacjeStoliki`
--
ALTER TABLE `RezerwacjeStoliki`
  ADD PRIMARY KEY (`RezerwacjaId`),
  ADD KEY `idx_stolik_data_godzina` (`DataRezerwacji`,`GodzinaRozpoczecia`),
  ADD KEY `idx_uzytkownik_id` (`UzytkownikId`);

--
-- Indeksy dla tabeli `Uzytkownicy`
--
ALTER TABLE `Uzytkownicy`
  ADD PRIMARY KEY (`UzytkownikId`),
  ADD UNIQUE KEY `Email` (`Email`);

--
-- Indeksy dla tabeli `Wiadomosci`
--
ALTER TABLE `Wiadomosci`
  ADD PRIMARY KEY (`WiadomoscId`),
  ADD KEY `fk_UserId` (`UserId`);

--
-- AUTO_INCREMENT dla zrzuconych tabel
--

--
-- AUTO_INCREMENT dla tabeli `Opinie`
--
ALTER TABLE `Opinie`
  MODIFY `OpiniaId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT dla tabeli `Potrawy`
--
ALTER TABLE `Potrawy`
  MODIFY `PotrawaId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=66;

--
-- AUTO_INCREMENT dla tabeli `RezerwacjeSale`
--
ALTER TABLE `RezerwacjeSale`
  MODIFY `RezerwacjaId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=94;

--
-- AUTO_INCREMENT dla tabeli `RezerwacjeStoliki`
--
ALTER TABLE `RezerwacjeStoliki`
  MODIFY `RezerwacjaId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=71;

--
-- AUTO_INCREMENT dla tabeli `Uzytkownicy`
--
ALTER TABLE `Uzytkownicy`
  MODIFY `UzytkownikId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT dla tabeli `Wiadomosci`
--
ALTER TABLE `Wiadomosci`
  MODIFY `WiadomoscId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- Ograniczenia dla zrzutów tabel
--

--
-- Ograniczenia dla tabeli `Opinie`
--
ALTER TABLE `Opinie`
  ADD CONSTRAINT `Opinie_ibfk_1` FOREIGN KEY (`UzytkownikId`) REFERENCES `Uzytkownicy` (`UzytkownikId`);

--
-- Ograniczenia dla tabeli `RezerwacjeSale`
--
ALTER TABLE `RezerwacjeSale`
  ADD CONSTRAINT `FK_RezerwacjeSale_Uzytkownicy` FOREIGN KEY (`UzytkownikId`) REFERENCES `Uzytkownicy` (`UzytkownikId`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ograniczenia dla tabeli `RezerwacjeStoliki`
--
ALTER TABLE `RezerwacjeStoliki`
  ADD CONSTRAINT `fk_rezerwacje_uzytkownicy` FOREIGN KEY (`UzytkownikId`) REFERENCES `Uzytkownicy` (`UzytkownikId`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Ograniczenia dla tabeli `Wiadomosci`
--
ALTER TABLE `Wiadomosci`
  ADD CONSTRAINT `fk_UserId` FOREIGN KEY (`UserId`) REFERENCES `Uzytkownicy` (`UzytkownikId`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
