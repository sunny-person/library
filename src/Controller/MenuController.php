<?php


namespace App\Controller;


use App\Entity\Category;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MenuController extends AbstractController {

    /**
     * @Route("/menu/categories", name="categories_menu")
     */
    public function categoriesMenu(): Response {
        $categories = $this->getDoctrine()->getRepository(Category::class)->getCategories();
        $menu = array();
        /** @var Category $category */
        foreach ($categories as $category) {
            if ((int) $category->getParent() === 0) {
                $menu[$category->getIdCategory()]['parent'] = $category->getNameCategory();
                continue;
            }

            $menu[$category->getParent()]['children'][$category->getIdCategory()] = $category->getNameCategory();
        }

        return $this->render(
            'menu/categories.html.twig',
            array(
                'menu'=>$menu
            )
        );
    }

}