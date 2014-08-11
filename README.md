# xRssImport3

RSS Nachrichten-Import für Contao ab 3.2.x


## Hinweise

1. Beiträge können nur gelöscht werden, wenn diese auch im Feed nicht mehr existieren, ansonsten werden sie wieder neu bezogen. Besser ist des, den Beitrag zu deaktivieren.
1. Ein Import findet übder den Command-Scheduler jede Stunde statt. Darüber hinaus kann auch mit der cron-Erweiterung importiert werden. Dazu muss im Job als Pfad ``system/modules/xRssImport3/jobs/importAllNews.php`` eingetragen werden.
1. Die Einbettung von Bildern funktioniert nur dann, wenn diese als Anlage (enclosure) mitgeliefert werden.
