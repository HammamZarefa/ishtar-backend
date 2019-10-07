<?php


namespace App\Service;

use DateTime;
use FilesystemIterator;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class UploadFileService
{
    const PATH = "http://ishtar-art.de/ImageUploads";

    private $rootPath;

    public function __construct(string $rootPath)
    {
        $this->rootPath = $rootPath;
    }

    public function upload(UploadedFile $file, $mainFolder)
    {
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $originalFilename; //todo add more strong file renaming
        $fileName = $safeFilename.'-'.uniqid().'.'.$file->guessExtension();

        try
        {
            $file->move($this->Destination($mainFolder), $fileName);
        }
        catch (FileException $e)
        {
            $response = 'failed to upload image: ' . $e->getMessage();
            //throw new FileException('Failed to upload file');
            return $response;
        }

        return self::PATH.$this->GetFilesAndFolder($mainFolder)."/".$fileName;
    }

    public function GetFilesAndFolder($directory) {
        /*file want to be escaped*/
        $EscapedFiles = [
            '.',
            '..'
        ];

        $FilesAndFolders = [];
        /*Scan Files and Directory*/
        $FilesAndDirectoryList = scandir($this->rootPath.$directory);
        foreach ($FilesAndDirectoryList as $SingleFile) {
            if (in_array($SingleFile, $EscapedFiles)){
                continue;
            }
            /*Store the Files with Modification Time to an Array*/
            $FilesAndFolders[$SingleFile] = filemtime($this->rootPath.$directory . '/' . $SingleFile);
        }
        /*Sort the result*/
        arsort($FilesAndFolders);
        $FilesAndFolders = array_keys($FilesAndFolders);

        return $directory.reset($FilesAndFolders);
    }

    public function CountFiles($directoryPath)
    {
        $fi = new FilesystemIterator($this->GetFilesAndFolder($directoryPath), FilesystemIterator::SKIP_DOTS);
        return iterator_count($fi);
    }

    public function Destination($mainFolder)
    {
        //todo: if there is no folder

        $count = $this->CountFiles($this->rootPath.$mainFolder);

        if ($count >= 10)
        {
            $datetime = new DateTime();
            $folderName = $datetime->format('Y-m-d_H:i:s');
            $destination = $this->rootPath.$mainFolder.$folderName;
            return $destination;
        }
        else
        {
            return $this->rootPath.$this->GetFilesAndFolder($mainFolder);
        }
    }
}