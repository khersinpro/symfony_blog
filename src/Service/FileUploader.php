<?php

namespace App\Service;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class FileUploader
{
    public function __construct( private $profileAvatarFolder, private $profileAvatarPublicPath, private $defaultProfileAvatar, 
    Filesystem $fs, SluggerInterface $slugger)
    {
        $this->slugger = $slugger;
    }

    public function uploadOneFile(UploadedFile $file, string $targetDirectory): string
    {
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $newFilename = $safeFilename.'-'.uniqid().'.'.$file->guessExtension();

        try {
            $file->move($targetDirectory, $newFilename);
        } catch(FileException $e) {
            throw new FileException('Le fichier n\'a pas pu etre sauvegardé.');
        }

        return $newFilename;
    }
}