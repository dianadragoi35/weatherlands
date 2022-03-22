<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class PageNotFoundController extends AbstractController
{

    public function pageNotFound()
    {
        return $this->render('home.html.twig',[
            'predictions' => null,
        ]);
    }

}