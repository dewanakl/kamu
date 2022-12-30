<?php

namespace Core\File;

use Core\Facades\App;
use Core\Http\Stream;

/**
 * Storage manipulation
 *
 * @class Storage
 * @package \Core\File
 */
final class Storage
{
    /**
     * Folder name
     * 
     * @var string $folderName
     */
    private static string $folderName = 'shared';

    /**
     * Base location
     * 
     * @var string $location
     */
    private static string $location = __DIR__ . '/../../';

    /**
     * Set folder name
     * 
     * @param string $name
     * @return void
     */
    public static function setFolderName(string $name): void
    {
        self::$folderName = $name;
    }

    /**
     * Get name location
     * 
     * @return string
     */
    public static function getLocation(): string
    {
        return self::$location . self::$folderName . '/';
    }

    /**
     * Tampilkan filenya
     * 
     * @param string $filename
     * @return Stream
     */
    public static function stream(string $filename): Stream
    {
        return App::get()->singleton(Stream::class)->send(self::getLocation() . $filename);
    }

    /**
     * Download filenya
     * 
     * @param string $filename
     * @return Stream
     */
    public static function download(string $filename): Stream
    {
        return self::stream($filename)->download();
    }

    /**
     * Ukuran filenya
     * 
     * @param string $filename
     * @return int|false
     */
    public static function size(string $filename): int|false
    {
        return filesize(self::getLocation() . $filename);
    }

    /**
     * Filenya ada ?
     * 
     * @param string $filename
     * @return bool
     */
    public static function exists(string $filename): bool
    {
        return file_exists(self::getLocation() . $filename);
    }

    /**
     * Terakhir diubah
     * 
     * @param string $filename
     * @return int|false
     */
    public static function lastModified(string $filename): int|false
    {
        return filemtime(self::getLocation() .  $filename);
    }

    /**
     * Get extension
     * 
     * @param string $filename
     * @return string|false
     */
    public static function extension(string $filename): string|false
    {
        if (self::exists($filename)) {
            return pathinfo(self::getLocation() . $filename, PATHINFO_EXTENSION);
        }

        return false;
    }

    /**
     * Get name
     * 
     * @param string $filename
     * @return string|false
     */
    public static function name(string $filename): string|false
    {
        if (self::exists($filename)) {
            return pathinfo(self::getLocation() . $filename, PATHINFO_FILENAME);
        }

        return false;
    }

    /**
     * Get mimeType
     * 
     * @param string $filename
     * @return string|false
     */
    public static function mimeType(string $filename): string|false
    {
        return mime_content_type(self::getLocation() . $filename);
    }

    /**
     * Copy filenya ?
     * 
     * @param string $from
     * @param string $to
     * @return bool
     */
    public static function copy(string $from, string $to): bool
    {
        return copy(self::getLocation() . $from, self::getLocation() . $to);
    }

    /**
     * Ganti namanya
     * 
     * @param string $from
     * @param string $to
     * @return bool
     */
    public static function rename(string $from, string $to): bool
    {
        return rename(self::getLocation() . $from, self::getLocation() . $to);
    }

    /**
     * Hapus filenya
     * 
     * @param string $filename
     * @return bool
     */
    public static function delete(string $filename): bool
    {
        return unlink(self::getLocation() . $filename);
    }

    /**
     * Baca file dalam folder
     * 
     * @return array
     */
    public static function files(): array
    {
        $files = scandir(self::getLocation(), 1);
        return array_diff($files, array('..', '.', '.gitignore'));
    }

    /**
     * Pindahkan file
     * 
     * @param string $from
     * @param string $to
     * @return bool
     */
    public static function move(string $from, string $to): bool
    {
        return rename(self::getLocation() . $from, self::$location . $to . '/' . $from);
    }
}
