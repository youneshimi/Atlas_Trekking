<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\LoginFormType;
use App\Form\RegistrationFormType;
use App\Form\RequestVerifyUserEmailFormType;
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class RegistrationController extends AbstractController
{
    private EmailVerifier $emailVerifier;

    /**
     * @param EmailVerifier $emailVerifier
     */
    public function __construct(EmailVerifier $emailVerifier)
    {
        $this->emailVerifier = $emailVerifier;
    }

    /**
     * @param Request                     $request
     * @param UserPasswordHasherInterface $userPasswordHasher
     * @param EntityManagerInterface      $entityManager
     * @return Response
     * @throws TransportExceptionInterface
     */
    #[Route('/inscription', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $user->setPassword(
            $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );
            $images = ['user.png', 'user2.png', 'user3.png'];
            $user->setImage($images[array_rand($images)]);

            $entityManager->persist($user);
            $entityManager->flush();

            $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
                (new TemplatedEmail())
                    ->from(new Address('ledu.kilian@gmail.com', 'Kilian LE DU'))
                    ->to($user->getEmail())
                    ->subject('Atlas Trekking : Confirmation de votre adresse mail')
                    ->htmlTemplate('registration/confirmation_email.html.twig')
            );

            $this->addFlash('success', 'Votre compte compte a bien été créé, veuillez confirmer votre adresse mail');

            return $this->redirectToRoute('show_index');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    /**
     * @param Request        $request
     * @param UserRepository $userRepository
     * @return Response
     */
    #[Route('/verifier/email', name: 'app_verify_email')]
    public function verifyUserEmail(Request $request, UserRepository $userRepository): Response
    {
        $id = $request->get('id');

        if (null === $id) {
            return $this->redirectToRoute('app_register');
        }

        $user = $userRepository->find($id);

        if (null === $user) {
            return $this->redirectToRoute('app_register');
        }

        try {
            $this->emailVerifier->handleEmailConfirmation($request, $user);
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('verify_email_error', $exception->getReason());

            return $this->redirectToRoute('app_register');
        }

        $this->addFlash('success', 'Votre adresse mail a bien été confirmée.');

        return $this->redirectToRoute('show_index');
    }


    /**
     * @param Request        $request
     * @param UserRepository $userRepository
     * @return Response
     * @throws TransportExceptionInterface
     */
    #[Route('/renvoyer-confirmation', name: 'app_request_verify_email')]
    public function requestVerifyUserEmail(
        Request $request,
        UserRepository $userRepository
    ): Response {

        if ($this->getUser()) {
            return $this->redirectToRoute('show_index');
        }

        $form = $this->createForm(RequestVerifyUserEmailFormType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $user =  $userRepository->findOneByEmail($form->get('email')->getData());
            if ($user) {
                $this->emailVerifier->sendEmailConfirmation(
                    'app_verify_email',
                    $user,
                    (new TemplatedEmail())
                        ->from(new Address('ounesshimi@gmail.com@gmail.com', 'Younes Shimi'))
                        ->to($user->getEmail())
                        ->subject('Atlas Trekking : Confirmation de votre adresse mail')
                        ->htmlTemplate('registration/confirmation_email.html.twig')
                );
                $this->addFlash('success', 'Un mail de confirmation a bien été envoyé à l\'adresse mail associée au compte.');
                return $this->redirectToRoute('show_index');
            } else {
                $this->addFlash('error',  'Email inconnu.');
            }
        }
        return $this->render('registration/request_link.html.twig', [
            'requestForm' => $form->createView(),
        ]);
    }



}
