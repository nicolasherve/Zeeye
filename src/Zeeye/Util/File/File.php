<?php

namespace Zeeye\Util\File;

use Zeeye\Util\Date\DateTime;
use \DirectoryIterator;
use Zeeye\Util\Date\Date;
use Zeeye\Util\Request\Request;

/**
 * Class used to manage operations on files and directories
 * 
 * @author     Nicolas Hervé <nherve@gmail.com>
 * @license    http://opensource.org/licenses/mit-license.php
 */
class File {

    /**
     * A list of files extensions with their corresponding MIME type
     * 
     * @var array
     */
    private static $_mimeTypes = array(
        'ai' => 'application/postscript', 'bcpio' => 'application/x-bcpio', 'bin' => 'application/octet-stream',
        'ccad' => 'application/clariscad', 'cdf' => 'application/x-netcdf', 'class' => 'application/octet-stream',
        'cpio' => 'application/x-cpio', 'cpt' => 'application/mac-compactpro', 'csh' => 'application/x-csh',
        'csv' => 'application/csv', 'dcr' => 'application/x-director', 'dir' => 'application/x-director',
        'dms' => 'application/octet-stream', 'doc' => 'application/msword', 'docx' => 'application/msword', 'drw' => 'application/drafting',
        'dvi' => 'application/x-dvi', 'dwg' => 'application/acad', 'dxf' => 'application/dxf', 'dxr' => 'application/x-director',
        'eps' => 'application/postscript', 'exe' => 'application/octet-stream', 'ez' => 'application/andrew-inset',
        'flv' => 'video/x-flv', 'gtar' => 'application/x-gtar', 'gz' => 'application/x-gzip', 'hdf' => 'application/x-hdf',
        'hqx' => 'application/mac-binhex40', 'ips' => 'application/x-ipscript', 'ipx' => 'application/x-ipix',
        'js' => 'application/x-javascript', 'latex' => 'application/x-latex', 'lha' => 'application/octet-stream',
        'lsp' => 'application/x-lisp', 'lzh' => 'application/octet-stream', 'man' => 'application/x-troff-man',
        'me' => 'application/x-troff-me', 'mif' => 'application/vnd.mif', 'ms' => 'application/x-troff-ms',
        'nc' => 'application/x-netcdf', 'oda' => 'application/oda', 'pdf' => 'application/pdf',
        'pgn' => 'application/x-chess-pgn', 'pot' => 'application/mspowerpoint', 'pps' => 'application/mspowerpoint',
        'ppt' => 'application/mspowerpoint', 'ppz' => 'application/mspowerpoint', 'pre' => 'application/x-freelance',
        'prt' => 'application/pro_eng', 'ps' => 'application/postscript', 'roff' => 'application/x-troff',
        'scm' => 'application/x-lotusscreencam', 'set' => 'application/set', 'sh' => 'application/x-sh',
        'shar' => 'application/x-shar', 'sit' => 'application/x-stuffit', 'skd' => 'application/x-koan',
        'skm' => 'application/x-koan', 'skp' => 'application/x-koan', 'skt' => 'application/x-koan',
        'smi' => 'application/smil', 'smil' => 'application/smil', 'sol' => 'application/solids',
        'spl' => 'application/x-futuresplash', 'src' => 'application/x-wais-source', 'step' => 'application/STEP',
        'stl' => 'application/SLA', 'stp' => 'application/STEP', 'sv4cpio' => 'application/x-sv4cpio',
        'sv4crc' => 'application/x-sv4crc', 'swf' => 'application/x-shockwave-flash', 't' => 'application/x-troff',
        'tar' => 'application/x-tar', 'tcl' => 'application/x-tcl', 'tex' => 'application/x-tex',
        'texi' => 'application/x-texinfo', 'texinfo' => 'application/x-texinfo', 'tr' => 'application/x-troff',
        'tsp' => 'application/dsptype', 'unv' => 'application/i-deas', 'ustar' => 'application/x-ustar',
        'vcd' => 'application/x-cdlink', 'vda' => 'application/vda', 'xlc' => 'application/vnd.ms-excel',
        'xll' => 'application/vnd.ms-excel', 'xlm' => 'application/vnd.ms-excel', 'xls' => 'application/vnd.ms-excel',
        'xlw' => 'application/vnd.ms-excel', 'zip' => 'application/zip', 'aif' => 'audio/x-aiff', 'aifc' => 'audio/x-aiff',
        'aiff' => 'audio/x-aiff', 'au' => 'audio/basic', 'kar' => 'audio/midi', 'mid' => 'audio/midi',
        'midi' => 'audio/midi', 'mp2' => 'audio/mpeg', 'mp3' => 'audio/mpeg', 'mpga' => 'audio/mpeg',
        'ra' => 'audio/x-realaudio', 'ram' => 'audio/x-pn-realaudio', 'rm' => 'audio/x-pn-realaudio',
        'rpm' => 'audio/x-pn-realaudio-plugin', 'snd' => 'audio/basic', 'tsi' => 'audio/TSP-audio', 'wav' => 'audio/x-wav',
        'asc' => 'text/plain', 'c' => 'text/plain', 'cc' => 'text/plain', 'css' => 'text/css', 'etx' => 'text/x-setext',
        'f' => 'text/plain', 'f90' => 'text/plain', 'h' => 'text/plain', 'hh' => 'text/plain', 'htm' => 'text/html',
        'html' => 'text/html', 'm' => 'text/plain', 'rtf' => 'text/rtf', 'rtx' => 'text/richtext', 'sgm' => 'text/sgml',
        'sgml' => 'text/sgml', 'tsv' => 'text/tab-separated-values', 'tpl' => 'text/template', 'txt' => 'text/plain',
        'xml' => 'text/xml', 'avi' => 'video/x-msvideo', 'fli' => 'video/x-fli', 'mov' => 'video/quicktime',
        'movie' => 'video/x-sgi-movie', 'mpe' => 'video/mpeg', 'mpeg' => 'video/mpeg', 'mpg' => 'video/mpeg',
        'qt' => 'video/quicktime', 'viv' => 'video/vnd.vivo', 'vivo' => 'video/vnd.vivo', 'gif' => 'image/gif',
        'ief' => 'image/ief', 'jpe' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'jpg' => 'image/jpeg',
        'pbm' => 'image/x-portable-bitmap', 'pgm' => 'image/x-portable-graymap', 'png' => 'image/png',
        'pnm' => 'image/x-portable-anymap', 'ppm' => 'image/x-portable-pixmap', 'ras' => 'image/cmu-raster',
        'rgb' => 'image/x-rgb', 'tif' => 'image/tiff', 'tiff' => 'image/tiff', 'xbm' => 'image/x-xbitmap',
        'xpm' => 'image/x-xpixmap', 'xwd' => 'image/x-xwindowdump', 'ice' => 'x-conference/x-cooltalk',
        'iges' => 'model/iges', 'igs' => 'model/iges', 'mesh' => 'model/mesh', 'msh' => 'model/mesh',
        'silo' => 'model/mesh', 'vrml' => 'model/vrml', 'wrl' => 'model/vrml',
        'mime' => 'www/mime', 'pdb' => 'chemical/x-pdb', 'xyz' => 'chemical/x-pdb'
    );

    /**
     * Creates the directories contained in the given path
     * 
     * @param string $dirPath directories path
     */
    public static function createDirectory($dirPath) {
        if (!file_exists($dirPath)) {
            self::createDirectory(dirname($dirPath));
            mkdir($dirPath, 0664);
        }
    }

    /**
     * Clean the given file name and returns it
     * 
     * @param string $fileName name to clean
     * @return string
     */
    public static function cleanName($fileName) {
        return preg_replace('/[^\w\.]+/', '_', $fileName);
    }

    /**
     * Checks if the given file path exists and is writable
     * 
     * @param string $filePath path of the file
     */
    private static function _checkFileBeforeWriting($filePath) {
        // We get the directory path to the file
        $dirPath = dirname($filePath);
        // We create the needed directories
        self::createDirectory($dirPath);
        // Checks if the directory exists
        if (!is_dir($dirPath)) {
            throw new FileException('The directory path [' . $dirPath . '] is not correct');
        }
        // Checks if the directory is writeable
        if (!is_writable($dirPath)) {
            throw new FileException('The directory path [' . $dirPath . '] is not writeable');
        }
    }

    /**
     * Writes the given content in the given file
     * 
     * @param string $filePath path of the file to write in
     * @param string $content content to write in the file
     * @param string $mode open mode of the file
     */
    public static function write($filePath, $content, $mode = 'wb') {
        self::_checkFileBeforeWriting($filePath);
        if (!$fp = fopen($filePath, $mode)) {
            throw new FileException('The access to file [' . $filePath . '] with mode [' . $mode . '] cannot be executed');
        }
        flock($fp, LOCK_EX);
        fwrite($fp, $content);
        flock($fp, LOCK_UN);
        fclose($fp);
    }

    /**
     * Append the given content to the given file
     * 
     * @param string $filePath path of the file to write in
     * @param string $content content to append to the file
     */
    public static function appendToFile($filePath, $content) {
        self::write($filePath, $content, 'a');
    }

    /**
     * Prepend the given content to the given file
     * 
     * @param string $filePath path of the file to write in
     * @param string $content content to prepend to the file
     */
    public static function prependToFile($filePath, $content) {
        self::write($filePath, $content, 'r+');
    }

    /**
     * Gets the file name of the given file path
     * 
     * @param string $filePath path of the file
     * @param boolean $isExtensionStripped indicates whether the file extension must be stripped or not
     * @return string
     */
    public static function getFileName($filePath, $isExtensionStripped = false) {
        $fileName = basename($filePath);
        if (!$isExtensionStripped) {
            return $fileName;
        }
        return substr($fileName, 0, strpos($fileName, '.'));
    }

    /**
     * Gets the modification time of the given file
     * 
     * @param string $filePath path of the file
     * @return Date|null
     */
    public static function getModificationTime($filePath) {
        $date = filemtime($filePath);
        if ($date === false) {
            return null;
        }
        return Date::create($date);
    }

    /**
     * Gets the creation time of the given file
     * 
     * @param string $filePath path of the file
     * @return DateTime|null
     */
    public static function getCreationTime($filePath) {
        $date = filectime($filePath);
        if ($date === false) {
            return null;
        }
        return Date::create($date);
    }

    /**
     * Returns the last directory from the given path
     * 
     * @param string $path path
     * @return string
     */
    public static function getLastDirectoryFromPath($path) {
        $sep = null;
        if (strpos($path, '/') !== false) {
            $sep = '/';
        } elseif (strpos($path, '\\') !== false) {
            $sep = '\\';
        } else {
            return '';
        }
        $tmp = explode($sep, $path);
        $lastIndex = count($tmp) - 1;
        if (!empty($tmp[$lastIndex])) {
            return $tmp[$lastIndex];
        }
        return $tmp[$lastIndex - 1];
    }

    public static function checkFilePath($filePath) {
        if (!self::exists($filePath)) {
            throw new FileException('The given file path [' . $filePath . '] does not exist');
        }
    }

    /**
     * Indicates whether the given file path exists or not
     * 
     * @param string $filePath the file's path
     * @return boolean
     */
    public static function exists($filePath) {
        return file_exists($filePath);
    }

    /**
     * Returns the content of the given file
     * 
     * @param string $filePath path of the file
     * @return string
     */
    public static function read($filePath) {
        $content = file_get_contents($filePath);
        if ($content === false) {
            throw new FileException('The file [' . $filePath . '] cannot be read');
        }
        return $content;
    }

    /**
     * Deletes the given directory
     * 
     * @param string $directoryPath path of the directory
     * @param boolean $keepSelf indicates whether the directory itself must be kept or not
     */
    public static function deleteDirectory($directoryPath, $keepSelf = false) {
        $dir = new DirectoryIterator($directoryPath);
        foreach ($dir as $dirContent) {
            if ($dirContent->isFile()) {
                unlink($dirContent->getPathName());
            } elseif (!$dirContent->isDot() && $dirContent->isDir()) {
                self::removeDir($dirContent->getPathName(), true);
            }
        }
        if (!$keepSelf) {
            rmdir($directoryPath);
        }
    }

    /**
     * Returns the list of directories' paths in the given path
     * 
     * @param string $directoryPath the directory path
     * @param boolean indicates whether the returned list must be recursive or not
     * @return array
     */
    public static function getDirectoriesPaths($directoryPath, $isRecursive = false) {
        $directories = array();
        $directory = new DirectoryIterator($directoryPath);
        foreach ($directory as $file) {
            if (!$file->isDot() && $file->isDir()) {
                if ($isRecursive) {
                    $directories[] = self::getDirectoriesPaths($file->getPathName(), $isRecursive);
                }
                $directories[] = $file->getPathName();
            }
        }
        return $directories;
    }

    /**
     * Returns the list of directories' names in the given path
     *
     * @param string $directoryPath the directory path
     * @param boolean indicates whether the returned list must be recursive or not
     * @return array
     */
    public static function getDirectoriesNames($directoryPath, $isRecursive = false) {
        $directories = array();
        $directory = new DirectoryIterator($directoryPath);
        foreach ($directory as $file) {
            if (!$file->isDot() && $file->isDir()) {
                if ($isRecursive) {
                    $directories[] = self::getDirectoriesNames($file->getFileName(), $isRecursive);
                }
                $directories[] = $file->getFileName();
            }
        }
        return $directories;
    }

    /**
     * Returns the list of files in the given path
     * 
     * @param string $directoryPath the directory path
     * @param boolean indicates whether the returned list must be recursive or not
     * @return array
     */
    public static function getFiles($directoryPath, $isRecursive = false) {
        $files = array();
        $directory = new DirectoryIterator($directoryPath);
        foreach ($directory as $file) {
            if (!$file->isDot() && !$file->isDir()) {
                if ($isRecursive) {
                    $files[] = self::getFiles($file->getPathName(), $isRecursive);
                }
                $files[] = $file->getPathName();
            }
        }
        return $files;
    }

    /**
     * Indicates whether the given file name exists under the given directory path
     * 
     * @param string $fileName name of the file to search
     * @param string $directoryPath path of the directory to investigate
     * @param boolean $isDirectChild indicates whether the file is expected as a direct child of the given directory or not
     * @return boolean
     */
    public static function isLocatedInDirectory($fileName, $directoryPath, $isDirectChild = false) {
        // If the file exists as a direct child of the directory
        if (self::exists($directoryPath . $fileName)) {
            return true;
        }

        // If the file is expected as a direct child
        if ($isDirectChild) {
            return false;
        }

        // Foreach subdirectory
        foreach (self::getDirectoriesPaths($directoryPath, true) as $directory) {
            if (self::isLocatedInDirectory($fileName, $directory)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Checks if the given file is of one of the given MIME types
     * 
     * If the actual MIME type cannot be found, null will be returned.
     * 
     * @param string $filePath the path of the file
     * @param string|array $types the type or the list of types that will be checked
     * @return null|boolean
     */
    public static function checkMimeType($filePath, $types) {
        $file_type = self::getMimeType($filePath);
        if (empty($file_type)) {
            return null;
        }
        if (!((is_array($types) && in_array($file_type, $types)) || $file_type == $types)) {
            return false;
        }
        return true;
    }

    /**
     * Returns the MIME type of the given file
     * 
     * If the actual MIME type cannot be found, null will be returned.
     * 
     * @param string $filePath the path of the file
     * @return null|string
     */
    public static function getMimeType($filePath) {
        if (function_exists('\finfo_open')) {
            $finfo_resource = finfo_open(FILEINFO_MIME);
            return finfo_file($finfo_resource, $filePath);
        }
        if (function_exists('\mime_content_type')) {
            return mime_content_type($filePath);
        }
        // TODO check if this part could be deleted:
        $extension = self::getExtension($filePath, false, true);
        if (isset(self::$_mimeTypes[$extension])) {
            return self::$_mimeTypes[$extension];
        }
        return null;
    }

    /**
     * Indicates if the size of the given file does not exceed the given size value
     * 
     * @param string $filePath path of the file
     * @param integer $size size that will be used to check
     * @return boolean
     */
    public static function checkSize($filePath, $size) {
        return self::getSize($filePath) <= intval($size);
    }

    /**
     * Gets the size of the given file
     * 
     * @param string $filePath path of the file
     * @return integer
     */
    public static function getSize($filePath) {
        return filesize($filePath);
    }

    /**
     * Deletes the given file
     * 
     * @param string $filePath the path of the file to delete
     */
    public static function deleteFile($filePath) {
        unlink($filePath);
    }

    /**
     * Checks if the given file has one of the given extensions
     * 
     * @param string $filePath path of the file
     * @param string|array $targetExtensions extension or list of extensions to check
     * @param boolean $isCaseSensitive indicates whether the check mus be case sensitive or not
     * @return boolean
     */
    public static function checkExtension($filePath, $targetExtensions, $isCaseSensitive = false) {
        $splittedExtensions = explode('.', self::getExtension($filePath, true));
        $extensions = array();
        $nb = count($splittedExtensions);
        for ($i = 0; $i < $nb; $i++) {
            $prepend = '';
            if ($i > 0) {
                $prepend = $extensions[$i - 1] . '.';
            }
            if ($isCaseSensitive) {
                $extensions[$i] = strtolower($prepend . $splittedExtensions[$i]);
            } else {
                $extensions[$i] = $prepend . $splittedExtensions[$i];
            }
        }
        if (!is_array($targetExtensions)) {
            $targetExtensions = array($targetExtensions);
        }

        foreach ($targetExtensions as $targetExtension) {
            if (!$isCaseSensitive) {
                $targetExtension = strtolower($targetExtension);
            }
            if ($targetExtension[0] != '.') {
                $targetExtension = '.' . $targetExtension;
            }
            foreach ($extensions as $extension) {
                if (!$isCaseSensitive) {
                    $extension = strtolower($extension);
                }
                if (strcmp($extension, $targetExtension) === 0) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Gets the extension of the given file
     * 
     * @param string $filePath path of the file
     * @param boolean $isComplete indicates whether the returned extension must be complete or not
     * @param boolean $isFirstDotStripped indicates whether the first dot must be stripped of the returned extension or not
     * @return string
     */
    public static function getExtension($filePath, $isComplete = false, $isFirstDotStripped = false) {
        $fileName = self::getFileName($filePath);
        $dotPosition = strpos($fileName, '.');
        if ($dotPosition === false) {
            return '';
        }
        if (!$isComplete) {
            $dotPosition = strrpos($fileName, '.');
        }
        if ($isFirstDotStripped) {
            return substr($fileName, $dotPosition + 1);
        }
        return substr($fileName, $dotPosition);
    }

    /**
     * Upload the file corresponding to the given fieldname into the given path
     * 
     * An existing file with the same name in the destination path will be overwritten.
     * 
     * @param string $fieldName name of the form field which contains the file to upload
     * @param string $destinationPath the directory path where the file will be uploaded
     * @param string $destinationName the new name of the file after the upload
     * @return boolean indicates whether the operation succeeded or not
     */
    public static function upload($fieldName, $destinationPath, $destinationName = null) {
        $fileInfos = Request::getUploadedFile($fieldName);
        if (empty($destinationName)) {
            $destinationName = $fileInfos['name'];
        }
        return move_uploaded_file($fileInfos['tmp_name'], $destinationPath . $destinationName);
    }

    /**
     * Moves the given file into the given directory
     * 
     * @param string $filePath the file to move
     * @param string $destinationPath the directory path where the file will be moved
     * @param string $destinationName the new name of the file after the move
     * @param boolean $isOverwritten indicates whether the operation can overwrites an existing file or not
     * @return boolean indicates whether the operation succeeded or not
     */
    public static function move($filePath, $destinationPath, $destinationName = null, $isOverwritten = true) {
        $destinationName = '';
        if (!empty($destinationName)) {
            $destinationName = $destinationName;
        } else {
            $destinationName = self::getFileName($filePath);
        }
        if (!$isOverwritten && file_exists($destinationPath . $destinationName)) {
            throw new FileException('The file cannot be moved to [' . $destinationPath . $destinationName . '] because a file with the same name already exists');
        }
        return rename($filePath, $destinationPath . $destinationName);
    }

    /**
     * Renames the given file with the given name
     * 
     * @param string $filePath the file to rename
     * @param string $newName the new name of the file
     * @return boolean indicates whether the operation succeeded or not
     */
    public static function rename($filePath, $newName) {
        return self::move($filePath, dirname($filePath) . '/', $newName);
    }

    /**
     * Creates a copy of the given file into the given path
     * 
     * @param string $filePath the file to copy
     * @param string $destinationPath the directory path where the file will be copied
     * @param string $destinationName the new name of the file after the copy
     * @param boolean $isOverwritten indicates whether the operation can overwrites an existing file or not
     * @return boolean indicates whether the operation succeeded or not
     */
    public static function copy($filePath, $destinationPath, $destinationName = null, $isOverwritten = true) {
        $finalDestinationName = '';
        if (!empty($destinationName)) {
            $finalDestinationName = $destinationName;
        } else {
            $finalDestinationName = self::getFileName($filePath);
        }
        if (!$isOverwritten && file_exists($destinationPath . $destinationName)) {
            throw new FileException('The file cannot be moved to [' . $destinationPath . $destinationName . '] because a file with the same name already exists');
        }
        return copy($filePath, $destinationPath . $finalDestinationName);
    }

    public static function tail($filepath, $lines = 1) {

        // Open file
        $f = @fopen($filepath, "rb");
        if ($f === false) {
            return false;
        }

        // Sets buffer size
        $buffer = ($lines < 2 ? 64 : ($lines < 10 ? 512 : 4096));

        // Jump to last character
        fseek($f, -1, SEEK_END);

        // Read it and adjust line number if necessary
        // (Otherwise the result would be wrong if file doesn't end with a blank line)
        if (fread($f, 1) != PHP_EOL) {
            $lines--;
        }

        // Start reading
        $output = '';
        $chunk = '';

        // While we would like more
        while (ftell($f) > 0 && $lines >= 0) {

            // Figure out how far back we should jump
            $seek = min(ftell($f), $buffer);

            // Do the jump (backwards, relative to where we are)
            fseek($f, -$seek, SEEK_CUR);

            // Read a chunk and prepend it to our output
            $output = ($chunk = fread($f, $seek)) . $output;

            // Jump back to where we started reading
            fseek($f, -mb_strlen($chunk, '8bit'), SEEK_CUR);

            // Decrease our line counter
            $lines -= substr_count($chunk, PHP_EOL);
        }

        // While we have too many lines
        // (Because of buffer size we might have read too many)
        while ($lines < 0) {

            // Find first newline and remove all text before that
            $output = substr($output, strpos($output, PHP_EOL) + 1);

            $lines++;
        }

        // Close file
        fclose($f);

        return trim($output);
    }

}
