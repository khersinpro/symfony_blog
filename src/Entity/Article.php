<?php

namespace App\Entity;

use App\Repository\ArticleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ArticleRepository::class)]
class Article
{
    const categoryConstraint = ['politique', 'science', 'technologie', 'ecologie'];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Assert\NotBlank(message: 'Ce champ doit être remplis')]
    #[Assert\Length(min: 5, max: 60,
    minMessage: 'Le titre doit faire au moin {{ limit }} caractères.',
    maxMessage:'Le titre ne doit pas faire plus de {{ limit }} caractères.'
    )]
    #[ORM\Column(length: 255)]
    private ?string $title = null;
    
    #[Assert\NotBlank(message: 'Ce champ doit être remplis')]
    #[Assert\Length(min: 100, minMessage: 'Le contenu de l\'article doit faire au moins {{ limit }} caractères.')]
    #[ORM\Column(type: Types::TEXT)]
    private ?string $content = null;
    

    #[ORM\Column(length: 255)]
    private ?string $image = null;

    #[ORM\ManyToOne(inversedBy: 'articles')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $author = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?int $likes = null;

    #[ORM\OneToMany(mappedBy: 'article', targetEntity: Like::class, orphanRemoval: true)]
    private Collection $userLiked;

    #[ORM\OneToMany(mappedBy: 'article', targetEntity: Comment::class, orphanRemoval: true)]
    private Collection $comments;

    #[Assert\NotBlank(message: 'Ce champ doit être remplis')]
    #[Assert\Choice(choices: Article::categoryConstraint, message: 'Ce choix est incorrect')]
    #[ORM\Column(length: 50)]
    private ?string $category = null;

    public function __construct()
    {
        $this->userLiked = new ArrayCollection();
        $this->comments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(string $image): self
    {
        $this->image = $image;

        return $this;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): self
    {
        $this->author = $author;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getLikes(): ?int
    {
        return $this->likes;
    }

    public function setLikes(int $likes): self
    {
        $this->likes = $likes;

        return $this;
    }

    /**
     * @return Collection<int, Like>
     */
    public function getUserLiked(): Collection
    {
        return $this->userLiked;
    }

    public function addUserLiked(Like $userLiked): self
    {
        if (!$this->userLiked->contains($userLiked)) {
            $this->userLiked->add($userLiked);
            $userLiked->setArticle($this);
        }

        return $this;
    }

    public function removeUserLiked(Like $userLiked): self
    {
        if ($this->userLiked->removeElement($userLiked)) {
            // set the owning side to null (unless already changed)
            if ($userLiked->getArticle() === $this) {
                $userLiked->setArticle(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Comment>
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
            $comment->setArticle($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getArticle() === $this) {
                $comment->setArticle(null);
            }
        }

        return $this;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(string $category): self
    {
        $this->category = $category;

        return $this;
    }
}
