<?php

namespace App\Entity;

use App\Repository\VcontactRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VcontactRepository::class)]
class Vcontact
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $phone1 = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $phone2 = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $phone3 = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $phone4 = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $phone5 = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $email1 = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $email2 = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $email3 = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $email4 = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $email5 = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $address = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $map = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $fb = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $insta = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $tripadv = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $tw = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $yt = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $linkedin = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $telegram = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $sitetitle = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $sitedescription = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $sitekeyword = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $favicon = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $logo1 = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $logo2 = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $logo3 = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $logo4 = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $sitelink = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPhone1(): ?string
    {
        return $this->phone1;
    }

    public function setPhone1(?string $phone1): static
    {
        $this->phone1 = $phone1;

        return $this;
    }

    public function getPhone2(): ?string
    {
        return $this->phone2;
    }

    public function setPhone2(?string $phone2): static
    {
        $this->phone2 = $phone2;

        return $this;
    }

    public function getPhone3(): ?string
    {
        return $this->phone3;
    }

    public function setPhone3(?string $phone3): static
    {
        $this->phone3 = $phone3;

        return $this;
    }

    public function getPhone4(): ?string
    {
        return $this->phone4;
    }

    public function setPhone4(?string $phone4): static
    {
        $this->phone4 = $phone4;

        return $this;
    }

    public function getPhone5(): ?string
    {
        return $this->phone5;
    }

    public function setPhone5(?string $phone5): static
    {
        $this->phone5 = $phone5;

        return $this;
    }

    public function getEmail1(): ?string
    {
        return $this->email1;
    }

    public function setEmail1(?string $email1): static
    {
        $this->email1 = $email1;

        return $this;
    }

    public function getEmail2(): ?string
    {
        return $this->email2;
    }

    public function setEmail2(?string $email2): static
    {
        $this->email2 = $email2;

        return $this;
    }

    public function getEmail3(): ?string
    {
        return $this->email3;
    }

    public function setEmail3(?string $email3): static
    {
        $this->email3 = $email3;

        return $this;
    }

    public function getEmail4(): ?string
    {
        return $this->email4;
    }

    public function setEmail4(?string $email4): static
    {
        $this->email4 = $email4;

        return $this;
    }

    public function getEmail5(): ?string
    {
        return $this->email5;
    }

    public function setEmail5(?string $email5): static
    {
        $this->email5 = $email5;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): static
    {
        $this->address = $address;

        return $this;
    }

    public function getMap(): ?string
    {
        return $this->map;
    }

    public function setMap(?string $map): static
    {
        $this->map = $map;

        return $this;
    }

    public function getFb(): ?string
    {
        return $this->fb;
    }

    public function setFb(?string $fb): static
    {
        $this->fb = $fb;

        return $this;
    }

    public function getInsta(): ?string
    {
        return $this->insta;
    }

    public function setInsta(?string $insta): static
    {
        $this->insta = $insta;

        return $this;
    }

    public function getTripadv(): ?string
    {
        return $this->tripadv;
    }

    public function setTripadv(?string $tripadv): static
    {
        $this->tripadv = $tripadv;

        return $this;
    }

    public function getTw(): ?string
    {
        return $this->tw;
    }

    public function setTw(?string $tw): static
    {
        $this->tw = $tw;

        return $this;
    }

    public function getYt(): ?string
    {
        return $this->yt;
    }

    public function setYt(?string $yt): static
    {
        $this->yt = $yt;

        return $this;
    }

    public function getLinkedin(): ?string
    {
        return $this->linkedin;
    }

    public function setLinkedin(?string $linkedin): static
    {
        $this->linkedin = $linkedin;

        return $this;
    }

    public function getTelegram(): ?string
    {
        return $this->telegram;
    }

    public function setTelegram(?string $telegram): static
    {
        $this->telegram = $telegram;

        return $this;
    }

    public function getSitetitle(): ?string
    {
        return $this->sitetitle;
    }

    public function setSitetitle(?string $sitetitle): static
    {
        $this->sitetitle = $sitetitle;

        return $this;
    }

    public function getSitedescription(): ?string
    {
        return $this->sitedescription;
    }

    public function setSitedescription(?string $sitedescription): static
    {
        $this->sitedescription = $sitedescription;

        return $this;
    }

    public function getSitekeyword(): ?string
    {
        return $this->sitekeyword;
    }

    public function setSitekeyword(?string $sitekeyword): static
    {
        $this->sitekeyword = $sitekeyword;

        return $this;
    }

    public function getFavicon(): ?string
    {
        return $this->favicon;
    }

    public function setFavicon(?string $favicon): static
    {
        $this->favicon = $favicon;

        return $this;
    }

    public function getLogo1(): ?string
    {
        return $this->logo1;
    }

    public function setLogo1(?string $logo1): static
    {
        $this->logo1 = $logo1;

        return $this;
    }

    public function getLogo2(): ?string
    {
        return $this->logo2;
    }

    public function setLogo2(?string $logo2): static
    {
        $this->logo2 = $logo2;

        return $this;
    }

    public function getLogo3(): ?string
    {
        return $this->logo3;
    }

    public function setLogo3(?string $logo3): static
    {
        $this->logo3 = $logo3;

        return $this;
    }

    public function getLogo4(): ?string
    {
        return $this->logo4;
    }

    public function setLogo4(?string $logo4): static
    {
        $this->logo4 = $logo4;

        return $this;
    }

    public function getSitelink(): ?string
    {
        return $this->sitelink;
    }

    public function setSitelink(?string $sitelink): static
    {
        $this->sitelink = $sitelink;

        return $this;
    }
}
