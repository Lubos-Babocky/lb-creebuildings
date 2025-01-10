<?php

namespace LB\CreeBuildings\Utils;

/**
 * Description of FileUtility
 *
 * @author Lubos
 */
class FileUtility {

    /**
     * Returns the WordPress upload directory path.
     *
     * @return string
     * @throws \Exception
     */
    private function getWpUploadDirPath(): string {
        $wpUploadDirPath = wp_upload_dir()['path'] ?? throw new \Exception('WP upload dir not defined!');
        if (!is_dir($wpUploadDirPath)) {
            if (!mkdir($wpUploadDirPath, 0755, true) && !is_dir($wpUploadDirPath)) {
                throw new \Exception('Failed to create WP upload directory!');
            }
        }
        return $wpUploadDirPath;
    }

    /**
     * Creates a local copy of an image from the given URL.
     *
     * @param string $imageName Name to save the image as.
     * @param string $imageUrl URL of the image to download.
     * @return string Full path to the image on disk.
     * @throws \Exception If the file is not a valid image or saving fails.
     */
    public function createImageFromUrl(string $imageName, string $imageUrl): string {
        $imageContent = file_get_contents($imageUrl) ?? throw new \Exception(sprintf('Nothing found in path %s', $imageUrl));
        $matches = [];
        preg_match('/(\w+)\/(\w+)/m', $mimeType = $this->getFileMimeTypeFromFileContent($imageContent), $matches);
        if(empty($matches) || count($matches) !== 3) {
            throw new \Exception(sprintf('Unknown mime-type: %s', $mimeType));
        } elseif ($matches[1] !== 'image') {
            throw new \Exception(sprintf('Not an image! [%s]', $mimeType));
        }
        $imagePath = sprintf('%s/%s.%s', rtrim($this->getWpUploadDirPath(), '/'), trim($imageName, '/ '), $matches[2]);
        file_put_contents($imagePath, $imageContent) ?? throw new \Exception('Image not created!');
        return $imagePath;
    }

    /**
     * @param string $fileContent
     * @return string
     */
    private function getFileMimeTypeFromFileContent(
            string $fileContent
    ): string {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_buffer($finfo, $fileContent);
        finfo_close($finfo);
        return $mimeType;
    }
}
