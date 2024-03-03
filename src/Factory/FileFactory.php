<?php

namespace App\Factory;

use Symfony\Component\Filesystem\Filesystem;
use Vich\UploaderBundle\Entity\File;
use Zenstruck\Foundry\ModelFactory;

/**
 * @extends ModelFactory<File>
 */
final class FileFactory extends ModelFactory
{
    public function __construct(
        private readonly Filesystem $filesystem,
        private readonly string $uploadPath,
    ) {
        parent::__construct();
    }

    protected function getDefaults(): array
    {
        $fileName = str_replace('.', '', uniqid('', true)).'.pdf';
        $filePath = $this->uploadPath.$fileName;

        $this->filesystem->copy($this->modelPath, $filePath);

        return [
            'filePath' => $filePath,
            'originalName' => self::faker()->word().'.pdf',
            'size' => $this->faker->numberBetween(1000, 9999),
        ];
    }

    protected function initialize(): self
    {
        return $this
            // ->afterInstantiate(function(File $file): void {})
        ;
    }

    protected static function getClass(): string
    {
        return File::class;
    }
}
