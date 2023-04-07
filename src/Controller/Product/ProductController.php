<?php

namespace App\Controller\Product;

use App\Class\Search;
use App\Entity\Product;
use App\Form\SearchType;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProductController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager
    )
    {

    }

    #[Route('/products', name: 'product_list')]
    public function list(Request $request, PaginatorInterface $paginator): Response
    {
        $search = new Search();
        $form = $this->createForm(SearchType::class, $search);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $products = $paginator->paginate(
                $this->entityManager->getRepository(Product::class)->findWithSearch($search),
                $request->query->getInt('page', 1),
                6);
        } else {
            $products = $paginator->paginate(
                $this->entityManager->getRepository(Product::class)->findProducts(),
                $request->query->getInt('page', 1),
                6);
        }

        return $this->render('product/index.html.twig', [
            'title' => 'Nos articles - '.$this->getParameter('app.brand'),
            'products' => $products,
            'formSearchProduct' => $form->createView()
        ]);
    }

    #[Route('/product/{slug}', name: 'product_show')]
    public function show($slug): Response
    {
        $product = $this->entityManager->getRepository(Product::class)->findOneBy(['slug' => $slug]);
        $products = $this->entityManager->getRepository(Product::class)->findBy(['isTopSeller' => 1], ['id' => 'DESC']);

        if (!$product) {
            return $this->redirectToRoute('product_list');
        }

        return $this->render('product/show.html.twig', [
            'product' => $product,
            'products' => $products
        ]);
    }
}
