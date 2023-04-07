<?php

namespace App\Entity;

use App\Repository\OrderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrderRepository::class)]
#[ORM\Table(name: 'orders')]
class Order
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'orders')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(length: 255)]
    private ?string $carrierName = null;

    #[ORM\Column]
    private ?float $carrierPrice = null;


    #[ORM\OneToMany(mappedBy: 'myOrder', targetEntity: OrderDetails::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $orderDetails;

    #[ORM\Column]
    private ?bool $isPaid = null;

    #[ORM\Column(length: 255)]
    private ?string $deliveryFirstname = null;

    #[ORM\Column(length: 255)]
    private ?string $deliveryLastname = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $deliveryCompany = null;

    #[ORM\Column(length: 255)]
    private ?string $deliveryAddress1 = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $deliveryAddress2 = null;

    #[ORM\Column(length: 255)]
    private ?string $deliveryPostalCode = null;

    #[ORM\Column(length: 255)]
    private ?string $deliveryCity = null;

    #[ORM\Column(length: 255)]
    private ?string $deliveryCountry = null;

    #[ORM\Column(length: 255)]
    private ?string $deliveryPhone = null;

    private ?float $totalPrice = null;

    #[ORM\Column(length: 255)]
    private ?string $reference = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $stripeSessionId = null;

    #[ORM\Column]
    private ?int $deliveryState = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->orderDetails = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->getId();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

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

    public function getCarrierName(): ?string
    {
        return $this->carrierName;
    }

    public function setCarrierName(string $carrierName): self
    {
        $this->carrierName = $carrierName;

        return $this;
    }

    public function getCarrierPrice(): ?float
    {
        return $this->carrierPrice;
    }

    public function setCarrierPrice(float $carrierPrice): self
    {
        $this->carrierPrice = $carrierPrice;

        return $this;
    }

    /**
     * @return Collection<int, OrderDetails>
     */
    public function getOrderDetails(): Collection
    {
        return $this->orderDetails;
    }

    public function addOrderDetail(OrderDetails $orderDetail): self
    {
        if (!$this->orderDetails->contains($orderDetail)) {
            $this->orderDetails->add($orderDetail);
            $orderDetail->setMyOrder($this);
        }

        return $this;
    }

    public function removeOrderDetail(OrderDetails $orderDetail): self
    {
        if ($this->orderDetails->removeElement($orderDetail)) {
            // set the owning side to null (unless already changed)
            if ($orderDetail->getMyOrder() === $this) {
                $orderDetail->setMyOrder(null);
            }
        }

        return $this;
    }

    public function isIsPaid(): ?bool
    {
        return $this->isPaid;
    }

    public function setIsPaid(bool $isPaid): self
    {
        $this->isPaid = $isPaid;

        return $this;
    }

    public function getDeliveryFirstname(): ?string
    {
        return $this->deliveryFirstname;
    }

    public function setDeliveryFirstname(string $deliveryFirstname): self
    {
        $this->deliveryFirstname = $deliveryFirstname;

        return $this;
    }

    public function getFullDeliveryCustomer(): string
    {
        if ($this->getDeliveryCompany()) {
            return
                $this->getDeliveryCompany() . ' - '. $this->getDeliveryFirstname() . ' ' . $this->getDeliveryLastname();
        }else {
            return
                $this->deliveryFirstname . ' ' . $this->deliveryLastname;
        }
    }

    public function getDeliveryLastname(): ?string
    {
        return $this->deliveryLastname;
    }

    public function setDeliveryLastname(string $deliveryLastname): self
    {
        $this->deliveryLastname = $deliveryLastname;

        return $this;
    }

    public function getDeliveryCompany(): ?string
    {
        return $this->deliveryCompany;
    }

    public function setDeliveryCompany(string $deliveryCompany): self
    {
        $this->deliveryCompany = $deliveryCompany;

        return $this;
    }

    public function getDeliveryAddress1(): ?string
    {
        return $this->deliveryAddress1;
    }

    public function setDeliveryAddress1(string $deliveryAddress1): self
    {
        $this->deliveryAddress1 = $deliveryAddress1;

        return $this;
    }

    public function getDeliveryAddress2(): ?string
    {
        return $this->deliveryAddress2;
    }

    public function setDeliveryAddress2(?string $deliveryAddress2): self
    {
        $this->deliveryAddress2 = $deliveryAddress2;

        return $this;
    }

    public function getDeliveryPostalCode(): ?string
    {
        return $this->deliveryPostalCode;
    }

    public function setDeliveryPostalCode(string $deliveryPostalCode): self
    {
        $this->deliveryPostalCode = $deliveryPostalCode;

        return $this;
    }

    public function getDeliveryCity(): ?string
    {
        return $this->deliveryCity;
    }

    public function setDeliveryCity(string $deliveryCity): self
    {
        $this->deliveryCity = $deliveryCity;

        return $this;
    }

    public function getDeliveryCountry(): ?string
    {
        return $this->deliveryCountry;
    }

    public function setDeliveryCountry(string $deliveryCountry): self
    {
        $this->deliveryCountry = $deliveryCountry;

        return $this;
    }

    public function getDeliveryPhone(): ?string
    {
        return $this->deliveryPhone;
    }

    public function setDeliveryPhone(string $deliveryPhone): self
    {
        $this->deliveryPhone = $deliveryPhone;

        return $this;
    }

    public function getFullDeliveryAddress(): string
    {
        if ($_SERVER['QUERY_STRING'] && preg_match('/crudAction=edit/', $_SERVER['QUERY_STRING'])) {
            if ($this->deliveryAddress2) {
                return
                    $this->deliveryAddress1 . ' ' .
                    $this->deliveryAddress2. ' ' .
                    $this->deliveryPostalCode . ' ' . $this->deliveryCity . ' ' .
                    $this->deliveryCountry;
            } else {
                return
                    $this->deliveryAddress1 . ' '.
                    $this->deliveryPostalCode . ' ' . $this->deliveryCity . ' ' .
                    $this->deliveryCountry;
            }
        }

        if ($this->deliveryAddress2) {
            return
                $this->deliveryAddress1 . '<br>' .
                $this->deliveryAddress2. '<br>' .
                $this->deliveryPostalCode . ' ' . $this->deliveryCity . '<br>' .
                $this->deliveryCountry;
        } else {
            return
                $this->deliveryAddress1 . '<br>'.
                $this->deliveryPostalCode . ' ' . $this->deliveryCity . '<br>' .
                $this->deliveryCountry;
        }
    }

    public function getTotalOrder(): string
    {
        $this->totalPrice = 0;

        foreach ($this->getOrderDetails()->getValues() as $item) {
            $this->totalPrice += ($item->getPrice()*$item->getQuantity());
        }
        $this->totalPrice += $this->carrierPrice;

        /*return number_format($this->totalPrice, 2, ',', ' ');*/
        return $this->totalPrice;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(string $reference): self
    {
        $this->reference = $reference;

        return $this;
    }

    public function getStripeSessionId(): ?string
    {
        return $this->stripeSessionId;
    }

    public function setStripeSessionId(?string $stripeSessionId): self
    {
        $this->stripeSessionId = $stripeSessionId;

        return $this;
    }

    public function getDeliveryState(): ?int
    {
        return $this->deliveryState;
    }

    public function setDeliveryState(int $deliveryState): self
    {
        //dd($deliveryState);
        $this->deliveryState = $deliveryState;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

}
