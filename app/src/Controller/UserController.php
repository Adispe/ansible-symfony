<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    // display current user informations
    #[Route('/api/users', name: 'app_display_user_info', methods: ['GET'])]
    public function getUserInfo(Request $request, UserRepository $userRepository)
    {
        $bearer = substr($request->headers->get('Authorization'), 43, -683);
        $payload = json_decode(base64_decode($bearer));
        #dd(json_decode($payload));
        $username = $payload->username;

        $user = $userRepository->findOneBy(['username' => $username]);
        if (!$user) {
            return new response('Authentication failed.');
        } else {
            $jsonContent = json_encode([
                'firstname' => $user->getFirstname(),
                'lastname' => $user->getLastname(),
                'username' => $user->getUsername(),
                'email' => $user->getEmail(),
                'password' => 'XXX'
            ]);
            $response = new Response($jsonContent);
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        }
    }

    // update current user informations
    #[Route('/api/users', name: 'app_update_user_info', methods: ['PUT'])]
    public function updateUserInfo(Request $request, UserRepository $userRepository, ManagerRegistry $doctrine)
    {
        $bearer = substr($request->headers->get('Authorization'), 43, -683);
        $payload = json_decode(base64_decode($bearer));
        #dd(json_decode($payload));
        $username = $payload->username;

        $user = $userRepository->findOneBy(['username' => $username]);
        if (!$user) {
            return new Response('Authentication failed.');
        } else {
            $data = json_decode($request->getContent(), true);
            $entityManager = $doctrine->getManager();
            $user = $entityManager->getRepository(User::class)->find($user->getId());
            $user->setFirstname($data['firstname']);
            $user->setLastname($data['lastname']);
            $user->setUsername($data['email']);
            $entityManager->flush();
            return new Response('User with id ' . $user->getId() . ' has been updated.');
        }
    }
}
