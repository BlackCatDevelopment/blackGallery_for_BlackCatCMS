<?php

/**
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 3 of the License, or (at
 *   your option) any later version.
 *
 *   This program is distributed in the hope that it will be useful, but
 *   WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 *   General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with this program; if not, see <http://www.gnu.org/licenses/>.
 *
 *   @author          BlackBird Webprogrammierung
 *   @copyright       2014, BlackBird Webprogrammierung
 *   @link            http://www.webbird.de
 *   @license         http://www.gnu.org/licenses/gpl.html
 *   @category        CAT_Modules
 *   @package         blackGallery
 *
 */

if (defined('CAT_PATH')) {
    if (defined('CAT_VERSION')) include(CAT_PATH.'/framework/class.secure.php');
} elseif (file_exists($_SERVER['DOCUMENT_ROOT'].'/framework/class.secure.php')) {
    include($_SERVER['DOCUMENT_ROOT'].'/framework/class.secure.php');
} else {
    $subs = explode('/', dirname($_SERVER['SCRIPT_NAME']));    $dir = $_SERVER['DOCUMENT_ROOT'];
    $inc = false;
    foreach ($subs as $sub) {
        if (empty($sub)) continue; $dir .= '/'.$sub;
        if (file_exists($dir.'/framework/class.secure.php')) {
            include($dir.'/framework/class.secure.php'); $inc = true;    break;
        }
    }
    if (!$inc) trigger_error(sprintf("[ <b>%s</b> ] Can't include class.secure.php!", $_SERVER['SCRIPT_NAME']), E_USER_ERROR);
}

$LANG = array(
    '[keep original]'       => 'Original beibehalten',
    'Active'                => 'Aktiv',
    'Add files'             => 'Dateien hinzufügen',
    'Add jQuery plugin'     => 'jQuery Plugin hinzufügen',
    'Added'                 => 'Hinzugefügt: ',
    'Allow frontend upload' => 'Frontend Upload erlauben',
    'Allow overwrite'       => 'Überschreiben erlauben',
    'Allowed mime types'    => 'Erlaubte Dateitypen',
    'available'             => 'vorhanden',
    'Cancel upload'         => 'Upload abbrechen',
    'Categories'            => 'Alben',
    'Category'              => 'Album',
    'Category name'         => 'Albumname',
    'Category options'      => 'Album Einstellungen',
    'Category picture'      => 'Album Bild',
    'Categories title'      => 'Albumüberschrift',
    'Choose category'       => 'Album wählen',
    'Configure Lightbox'    => 'Lightbox konfigurieren',
    'CSS files'             => 'CSS Dateien',
    'custom'                => 'angepaßt',
    'default'               => 'standard',
    'Default backend tab'   => 'Standard-Reiter im Backend',
    'Exclude dirs'          => 'Verzeichnisse ausnehmen',
    'File name'             => 'Dateiname',
    'first'                 => 'erstes',
    'Folder name'           => 'Verzeichnisname',
    'Global title'          => 'Hauptüberschrift',
    'Globally available'    => 'Global verfügbar',
    'Image'                 => 'Bild',
    'Images'                => 'Bilder',
    'image(s)'              => 'Bild(er)',
    'Image caption'         => 'Bildüberschrift',
    'Image dimensions'      => 'Bildgrößen',
    'Images title'          => 'Überschrift Bilderliste',
    'Javascript code'       => 'Javascript Code',
    'Javascript files'      => 'Javascript Dateien',
    'Keep original size'    => 'Originalgröße beibehalten',
    'last'                  => 'letztes',
    'Lightbox name'         => 'Lightbox Name',
    'No categories found'   => 'Keine Alben gefunden',
    'No changes found'      => 'Keine Veränderungen gefunden',
    'No images (try Sync)'  => 'Keine Bilder (Sync probieren)',
    'No new images found'   => 'Keine neuen Bilder gefunden',
    'No picture(s)'         => 'Kein(e) Bild(er)',
    'not'                   => 'nicht',
    'Other settings'        => 'Sonstige Optionen',
    'Output template'       => 'Ausgabetemplate',
    'Path settings'         => 'Verzeichnisoptionen',
    'Pictures'              => 'Bilder',
    'Preview'               => 'Vorschau',
    'random'                => 'zufällig',
    'Reorder images'        => 'Bilder sortieren',
    'Remove (no upload)'    => 'Entfernen (nicht hochladen)',
    'Removed'               => 'Entfernt',
    'Remove this plugin'    => 'Dieses Plugin entfernen',
    'Reset all'             => 'Für alle übernehmen',
    'Resize method'         => 'Methode für Größenänderung',
    'Resize to'             => 'Größe ändern in',
    'Root folder'           => 'Basisverzeichnis',
    'specify'               => 'wählen',
    'Start upload'          => 'Upload starten',
    'Subfolders'            => 'Unterverz.',
    'Success'               => 'Erfolgreich',
    'Sync all images'       => 'Alle Bilder synchronisieren',
    'Sync categories'       => 'Alben synchronisieren',
    'Sync images'           => 'Bilder synchronisieren',
    'Sync thumbs'           => 'Thumbnails synchronisieren',
    'Thumb foldername'      => 'Thumbnail Verzeichnisname',
    'Thumb height'          => 'Thumbnail Höhe',
    'Thumb width'           => 'Thumbnail Breite',
    'Thumbnail image'       => 'Thumbnail Bild',
    'Thumbnail settings'    => 'Thumbnail Optionen',
    'Upload options'        => 'Upload Optionen',
    'use global default'    => 'Globale Einstellung verwenden',
    'Use skin'              => 'Skin',
    'Yes'                   => 'Ja',
    'Activate as current Lightbox'    => 'Als aktuell verwendete Lightbox aktivieren',
    'Currently selected root folder'  => 'Zur Zeit gewähltes Basisverzeichnis',
    'Default category / folder'       => 'Standard-Kategorie / Verzeichnis',
    'Drop files here or click to add' => 'Dateien hierher ziehen oder zum manuellen Hinzufügen hier klicken',
    'Error re-ordering images'        => 'Fehler beim Sortieren der Bilder',
    'Files successfully uploaded'     => 'Dateien erfolgreich übertragen',
    'Please note!'                    => 'Bitte beachten!',
    'Re-ordered successfully'         => 'Erfolgreich sortiert',
    'Show empty categories'           => 'Leere Alben anzeigen',
    'This category is deactivated!'   => 'Diese Kategorie ist deaktiviert!',
    'Thumb background color'          => 'Thumbnail Hintergrundfarbe',
    'Thumb creation method'           => 'Methode für Thumb-Erzeugung',
    'Thumb overlay color'             => 'Thumbnail Overlay Farbe',
    'Title for categories overview'   => 'Überschrift für Kategorieliste',
    'Use default output template'      => 'Standard-Ausgabetemplate benutzen',
    'Yes, but backend users only'     => 'Ja, aber nur Backend Benutzer',
    'Yes, everyone (attention!)'      => 'Ja, jeder (Vorsicht!)',
    'Allows guest users to add images. Please use with care!' => 'Erlaubt Besuchern das Hochladen eigener Bilder. Bitte mit Bedacht verwenden!',
    'Are you sure that you wish to delete this image? Please note: This will also remove the file from the server!' => 'Soll dieses Bild wirklich entfernt werden? Hinweis: Die Datei wird auch vom Server gelöscht!',
    'Crops image to fill the area while keeping original aspect ratio' => 'Schneidet das Bild passend zu, erhält das Seitenverhältnis',
    'Do you really want to remove this Lightbox?' => 'Soll diese Lightbox wirklich entfernt werden?',
    'Double click on a list item to edit. Drag & drop to sort.' => 'Doppelklick auf eine Zeile zum Editieren. Drag & Drop zum Sortieren.',
    'Fits image into the area without taking care of any ratios. Expect your image to get deformed.' => 'Paßt das Bild in Breite und Höhe an, ohne das Seitenverhältnis zu erhalten. Das Bild wird sehr wahrscheinlich verzerrt.',
    'Fits image into width and height while keeping original aspect ratio' => 'Paßt das Bild in Breite und Höhe an, erhält das Seitenverhältnis',
    'Global category; you may override this per image below' => 'Standard-Kategorie; kann pro Bild geändert werden',
    'can be used in all blackGallery sections' => 'kann in allen blackGallery Sektionen verwendet werden',
    'Height in Pixel for thumbnail images' => 'Höhe in Pixel für Thumbnail Images',
    'If set to No, images with the same name as existing images will be renamed automatically.' => 'Bei Nein werden Bilder mit gleichem Namen wie bereits existierende beim Upload automatisch umbenannt.',
    'If you need more customization options, please create your own skin!' => 'Sofern weitere Anpassungsmöglichkeiten benötigt werden, bitte einen eigenen Skin anlegen!',
    'If you need to add a jQuery Plugin first, please use the jQuery Plugin Manager Admin Tool.' => 'Um zunächst ein neues Plugin zu installieren, bitte das jQuery Plugin Manager Admin Tool verwenden.',
    'Image to be shown as placeholder for category (navigation view)' => 'Das Bild, welches als Platzhalter für die Kategorie angezeigt wird. (Navigation)',
    'In most cases, empty categories (not having sub folders and/or images) should be hidden' => 'In den meisten Fällen sollten leere Alben (weder Unterverzeichnisse noch Bilder vorhanden) versteckt werden',
    'Lightbox plugin to use; you can add and configure the Lightbox plugins on the Lightbox tab' => 'Das zu verwendende Lightbox Plugin. Lightboxen können auf dem Reiter Lightbox hinzufügt und konfiguriert werden.',
    'No categories found, try [Sync categories] button!' => 'Keine Alben vorhanden, [Alben synchronisieren] Schaltfläche verwenden',
    'Only if original is larger' => 'Nur wenn Original größer',
    'Please choose an item from the list' => 'Bitte einen Eintrag aus der Liste wählen',
    'Please make sure to select a root folder first!' => 'Bitte vorher das gewünschte Basisverzeichnis einstellen!',
    'Please note: You will have to re-sync all thumbs in all categories if you make any changes here!' => 'Hinweis: Wird hier etwas geändert, müssen alle Thumbnails in allen Alben neu erzeugt werden!',
    'Tab to open by default when the page is edited' => 'Dieser Reiter wird standardmäßig aktiviert, wenn die Seite bearbeitet wird',
    'The background color will not be included in the images, it is used in the StyleSheet.' => 'Die Hintergrundfarbe wird nicht in den Bildern selbst verwendet, sondern im StyleSheet.',
    'The categories are based upon the folder hierarchy in the root folder, so it is not possible to add or delete categories here.' => 'Die Alben basieren auf der Verzeichnisstruktur unterhalb des Basisverzeichnisses, daher können Alben hier weder angelegt noch gelöscht werden.',
    'The suffix list is derived from the global settings of the CMS' => 'Die Liste der Dateiendungen wird aus den globalen Einstellungen des CMS abgeleitet',
    'The skin is a set of output templates. By default, only one skin is installed, but you can create your own.' => 'Ein Skin ist eine Zusammenstellung von Ausgabetemplates. Standardmäßig ist nur ein Skin installiert, es können aber eigene erstellt werden.',
    'The resize method defines how to handle overflow' => 'Die Methode bestimmt, wie überstehende Bildteile behandelt werden',
    'This is the default category (shown when the page is opened the first time)' => 'Die Standardkategorie wird beim ersten Aufruf der Seite angezeigt',
    'This is the folder to start with' => 'Startverzeichnis',
    'This is the overall title, shown on all pages' => 'Hauptüberschrift für alle Seiten',
    'Thumbnail images are created in this subfolder. The subfolder will always be hidden.' => 'Thumbnail-Grafiken werden in diesem Unterverzeichnis erzeugt. Das Verzeichnis ist immer versteckt.',
    'Thumbnails are reduced-size versions of pictures. The Gallery will auto-create them when new images are uploaded or missing thumbs are identified.' => 'Als Thumbnails werden verkleinerte Versionen der Bilder bezeichnet. Die Galerie erstellt diese automatisch beim Hochladen neuer Bilder oder wenn fehlende Thumbnails festgestellt werden.',
    'Title for images overview; leave empty if you don\'t want to have one' => 'Überschrift für Bilderliste; leer lassen um keinen zu verwenden',
    'Uploaded images are resized automatically if you give a size here.' => 'Hochgeladene Bilder werden automatisch auf die hier angegebene Größe zugeschnitten',
    'Width in Pixel for thumbnail images' => 'Breite in Pixel für Thumbnail Images',
    'You can add files by drag & drop or by clicking on the [Add files] button.' => 'Dateien können per Drag & Drop oder durch Klick auf [Dateien hinzufügen] hinzugefügt werden.',
    'You may deactivate subfolders that are not to be used for the gallery.' => 'Unterverzeichnisse, die nicht für die Galerie genutzt werden sollen, können deaktiviert werden.',
    'You may exclude subfolders from being listed here; the thumbs subfolder will be excluded automatically.' => 'Hier können bestimmte Unterverzeichnisse ausgeschlossen werden; das Thumbs-Unterverzeichnis wird automatisch ausgenommen.',
    'You will loose all category and picture settings if you change the root directory!' => 'Alle Kategorie- und Bildeinstellungen gehen verloren, wenn das Basisverzeichnis geändert wird!',
);