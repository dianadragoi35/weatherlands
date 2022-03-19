<?php

namespace App\Controller;

use App\Entity\Prediction;
use App\Entity\PredictionTest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;


class ApiController extends AbstractController
{
    /**
     * @Route("/", name="homepage")
     */
    public function index()
    {
        $cities = $this->getCities();

        $data = file_get_contents('../tests/Resources/predictions.json');
        $data = json_decode($data, true);

        $predictions = Prediction::denormalize($data);

        $predictions = array_filter($predictions, function ( $obj ) {
            return ($obj->getCity() == 'Amsterdam' || $obj->getCity() == 'Rotterdam');
        });       

        return $this->render('api.html.twig', [
            'cities'      => $cities,
            'predictions' => $predictions,
        ]);
    }

    /**
     * @return array cities
     */
    public function getCities()
    {
        $cities = ['Amsterdam', 'Rotterdam', 'The Hague', 'Einhoven'];      

        return $cities;
    }

   /**
     * @Route("/city/{city}", name="city")
     */
    public function getPredictionsByCity(Request $request, $city)
    {
        $cities = $this->getCities();

        $data = file_get_contents('../tests/Resources/predictions.json');
        $data = json_decode($data, true);

        $predictions = Prediction::denormalize($data);

        $predictions = array_filter($predictions, function ( $obj ) use ($city) {
            return (strtolower($obj->getCity()) == $city);
        });       

        return $this->render('api.html.twig', [
            'cities'      => $cities,
            'predictions' => $predictions,
        ]);
    }

    /**
     * @Route("/scale/{scale}", name="scale")
     */
    public function getPredictionsByScale(Request $request, $scale)
    {
        $cities = $this->getCities();

        $data = file_get_contents('../tests/Resources/predictions.json');
        $data = json_decode($data, true);

        $predictions = Prediction::denormalize($data);

        $predictions = array_filter($predictions, function ( $obj ) use ($scale) {
            return (strtolower($obj->getScale()) == $scale);
        });       

        return $this->render('api.html.twig', [
            'cities'      => $cities,
            'predictions' => $predictions,
        ]);
    }

    /**
     * @Route("/date/{date}", name="date")
     */
    public function getPredictionsByDate(Request $request, $date)
    {
        $cities = $this->getCities();

        $data = file_get_contents('../tests/Resources/predictions.json');
        $data = json_decode($data, true);

        $predictions = Prediction::denormalize($data);

        $predictions = array_filter($predictions, function ( $obj ) use ($date) {
            return (strtolower($obj->getDate()) == $date);
        });       

        return $this->render('api.html.twig', [
            'cities'      => $cities,
            'predictions' => $predictions,
        ]);
    }

    /**
     * @Route("/test", name="test")
     */
    public function test()
    {
        return $this->render('test.html.twig', [
            'predictions' => null,
        ]);
    }

    /**
     * @return data provided in JSON format
     *  */
    public function getJson()
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://eface2b6-d896-4e7a-8856-b714cb5ec1c5.mock.pstmn.io/json');
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/json')); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $response = curl_exec($ch);
        $data     = json_decode($response);

        return $data;
    }
}