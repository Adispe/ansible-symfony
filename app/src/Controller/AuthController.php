<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AuthController extends AbstractController
{
    // Register
    #[Route('/api/register', name: 'app_auth_register', methods: ['POST'])]
    public function register(Request $request, UserPasswordHasherInterface $passwordHasher, ManagerRegistry $doctrine): Response
    {

        $data = json_decode($request->getContent(), true);
        $password =  $data['password'];
        $email = $data['email'];
        $firstname = $data['firstname'];
        $lastname = $data['lastname'];
        // search if email adress does already exist
        // $entityManager = $doctrine->getManager();
        // $heExists = $doctrine->getRepository('UserBundle:User')->findBy(array('email'=> $email));
        // if (!$heExists) {
        //     return new Response('The address ' . $email . ' is already used.');
        // } else {
        $user = new User();
        $hashedPassword = $passwordHasher->hashPassword($user, $password);
        $user->setPassword($hashedPassword);
        $user->setEmail($email);
        $user->setFirstname($firstname);
        $user->setLastname($lastname);
        $user->setUsername($email);

        $entityManager = $doctrine->getManager();
        $entityManager->persist($user);
        $entityManager->flush();

        $response = new Response('{"message": "The user ' . $firstname . ' ' . $lastname . ' has been created."}');
        $response->headers->set('Content-type', 'application/json');
        return $response;
        // }
    }

    #[Route('/api/login', name: 'app_auth_login', methods: ['POST'])]
    public function login(Request $request, UserRepository $userRepository, UserPasswordHasherInterface $userPassHasher)
    {

        $data = json_decode($request->getContent(), true);

        $user = $userRepository->findOneBy(['email' => $data['email']]);

        if (!$user || !$userPassHasher->isPasswordValid($user, $data['password'])) {
            $response = new Response('{"error": "invalid authentication data."}');
            $response->headers->set('Content-type', 'application/json');
            return $response;
        } else {
            $response = new Response('{"message": "logged in user"}');
            $response->headers->set('Content-type', 'application/json');
            return $response;
        }
    }
}
