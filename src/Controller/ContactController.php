<?php

namespace App\Controller;

use App\Entity\Contact;
use App\Form\ContactType;
use App\Form\UserType;
use App\Repository\ContactRepository;
use App\Repository\UserRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/contact')]
class ContactController extends AbstractController
{

    #[Route('/new', name: 'contact_new', methods: ['GET','POST'])]
    public function new(Request $request, MailerInterface $mailer): Response
    {
        $contact = new Contact();

        $form = $this->createForm(ContactType::class, $contact);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($contact);
            $entityManager->flush();

            $email = (new TemplatedEmail())
                ->from($form->get('mail')->getData())
                ->to('benjirodrigues23@hotmail.fr')
                ->subject('contact au sujet de : "'. $contact->getObjet(). '"')
                ->htmlTemplate('contact/show.html.twig')
                ->context([
                    'contact' =>$contact,
                    'mail'=>$contact->getMail(),
                    'objet'=>$contact->getObjet(),
                    'message'=> $contact->getMessage()
                ]);
            $mailer->send($email);
            $this->addFlash('message','Email envoyer');
            return $this->redirectToRoute('mainPage', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('contact/new.html.twig', [
            'contact' => $contact,
            'form' => $form,
        ]);
    }


}
