<?php

namespace App\Service;

use App\Entity\Vcontact;
use Doctrine\ORM\EntityManagerInterface;
use Twig\Environment;

class EmailService
{
    private EntityManagerInterface $entityManager;
    private Environment $twig;

    public function __construct(EntityManagerInterface $entityManager, Environment $twig)
    {
        $this->entityManager = $entityManager;
        $this->twig = $twig;
    }

    private function getContactInfo(): ?Vcontact
    {
        return $this->entityManager
            ->getRepository(Vcontact::class)
            ->findOneBy([]);
    }

    public function renderBookingConfirmation(array $bookingData): string
    {
        $contact = $this->getContactInfo();

        return $this->twig->render('emails/booking_confirmation.html.twig', array_merge($bookingData, [
            'contact' => $contact
        ]));
    }

    public function renderTourBooking(array $bookingData): string
    {
        $contact = $this->getContactInfo();

        return $this->twig->render('emails/tour_booking.html.twig', array_merge($bookingData, [
            'contact' => $contact
        ]));
    }
}
