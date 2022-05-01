<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class ProductController extends AbstractController
{
    // list products
    #[Route('/api/products', name: 'app_get_products', methods: ['GET'])]
    public function getProducts(ProductRepository $productRepository): Response
    {
        $products = $productRepository->findAll();

        $encoders = [new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];
        $serializer = new Serializer($normalizers, $encoders);

        $jsonContent = $serializer->serialize($products, 'json');

        $response = new Response($jsonContent);
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    // retrieve specific product
    #[Route('/api/products/{productId}', name: 'app_get_one_product', methods: ['GET'])]
    public function getOneProduct(ManagerRegistry $doctrine, int $productId): Response
    {
        $product = $doctrine->getRepository(Product::class)->find($productId);

        if (!$product) {
            $response = new Response('{"error": "No matching product found."}');
            $response->headers->set('Content-type', 'application/json');
            return $response;
        } else {
            $encoders = [new JsonEncoder()];
            $normalizers = [new ObjectNormalizer()];
            $serializer = new Serializer($normalizers, $encoders);

            $jsonContent = $serializer->serialize($product, 'json');

            $response = new Response($jsonContent);
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        }
    }


    // add one product
    #[Route('/api/product', name: 'app_add_one_product', methods: ['POST'])]
    public function addOneProduct(ManagerRegistry $doctrine, Request $request)
    {

        $entityManager = $doctrine->getManager();

        $data = json_decode($request->getContent(), true);
        $product = new Product();
        // name, description, photo, price
        $product->setName($data['name']);
        $product->setDescription($data['description']);
        $product->setPhoto($data['photo']);
        $product->setPrice($data['price']);
        $entityManager->persist($product);
        $entityManager->flush();

        # return new Response('Saved new product with id ' . $product->getId());
        $response = new Response('{"message": "Saved new product with id ' . $product->getId() . '."}');
        $response->headers->set('Content-type', 'application/json');
        return $response;
    }

    // delete one product
    #[Route('api/product/{productId}', name: 'app_delete_one_product', methods: ['DELETE'])]
    public function deleteOnProduct(ProductRepository $productRepository, ManagerRegistry $doctrine, Request $request, int $productId)
    {
        $product = $productRepository->findOneBy(['id' => $productId]);
        if (!$product) {
            $response = new Response('{"error": "No matching product found."}');
            $response->headers->set('Content-type', 'application/json');
            return $response;
        } else {
            $entityManager = $doctrine->getManager();
            $entityManager->remove($product);
            $entityManager->flush();
            $response = new Response('{"message": "Product with id ' . $productId . ' has been deleted."}');
            $response->headers->set('Content-type', 'application/json');
            return $response;
        }
    }

    // update one product
    #[Route('api/product/{productId}', name: 'app_update_one_product', methods: ['PUT'])]
    public function updateOneProduct(ManagerRegistry $doctrine, int $productId, Request $request)
    {
        $entityManager = $doctrine->getManager();
        $product = $entityManager->getRepository(Product::class)->find($productId);

        if (!$product) {
            $response = new Response('{"error": "No matching product found."}');
            $response->headers->set('Content-type', 'application/json');
            return $response;
        } else {
            $data = json_decode($request->getContent(), true);
            $product->setName($data['name']);
            $product->setDescription($data['description']);
            $product->setPhoto($data['photo']);
            $product->setPrice($data['price']);
            $entityManager->flush();
            $response = new Response('{"message": "Product with id ' . $productId . ' has been updated."}');
            $response->headers->set('Content-type', 'application/json');
            return $response;
        }
    }
}
