<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use App\Trait\TimestampableTrait;
use App\Trait\SluggableTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
#[Vich\Uploadable]
class Product
{
    use TimestampableTrait;
    use SluggableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: 255)]
    #[Assert\NotBlank(message: 'Le nom du produit est obligatoire.')]
    private string $name;

    #[ORM\Column(type: Types::STRING, length: 255)]
    #[Assert\NotBlank(message: 'La référence du produit est obligatoire.')]
    private string $reference;

    #[ORM\Column(type: Types::FLOAT)]
    #[Assert\PositiveOrZero(message: 'Le prix doit être positif ou nul.')]
    private float $price;

    #[Vich\UploadableField(mapping: 'productImage', fileNameProperty: 'imageName')]
    #[Assert\Image(
        maxSize: '1000k',
        mimeTypes: ['image/jpeg', 'image/png'],
        maxHeight: 600,
        maxWidth: 600,
        minHeight: 300,
        minWidth: 300,
        maxSizeMessage: 'L\'image produit ne doit pas dépasser 1000ko.',
        mimeTypesMessage: 'L\'image produit doit être au format JPG ou PNG.',
        maxHeightMessage: 'L\'image produit ne doit pas dépasser 600px de hauteur.',
        maxWidthMessage: 'L\'image produit ne doit pas dépasser 600px de largeur.',
        minHeightMessage: 'L\'image produit doit faire au moins 300px de hauteur.',
        minWidthMessage: 'L\'image produit doit faire au moins 300px de largeur.'
    )]
    private ?File $imageFile = null;

    #[ORM\Column(nullable: true)]
    private ?string $imageName = null;

    #[ORM\ManyToMany(targetEntity: Category::class, inversedBy: 'products')]
    private Collection $categories;

    #[ORM\ManyToOne(inversedBy: 'products')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotBlank(message: 'La société est obligatoire.')]
    private ?Company $company = null;

    public function __construct()
    {
        $this->categories = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getReference(): string
    {
        return $this->reference;
    }

    public function setReference(string $ref): void
    {
        $this->reference = $ref;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): static
    {
        $this->price = $price;

        return $this;
    }

    /**
     * @return Collection<int, Category>
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    public function addCategory(Category $category): static
    {
        if (!$this->categories->contains($category)) {
            $this->categories->add($category);
        }

        return $this;
    }

    public function removeCategory(Category $category): static
    {
        $this->categories->removeElement($category);

        return $this;
    }

    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }

    public function setImageFile(?File $image = null): void
    {
        $this->imageFile = $image;

        if (null !== $image) {
            $this->updatedAt = new \DateTime();
        }
    }

    public function getImageName(): ?string
    {
        return $this->imageName;
    }

    public function setImageName(?string $imageName): void
    {
        $this->imageName = $imageName;
    }

    public function getCompany(): ?Company
    {
        return $this->company;
    }

    public function setCompany(?Company $company): static
    {
        $this->company = $company;

        return $this;
    }


}
