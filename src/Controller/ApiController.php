<?php

namespace App\Controller;

use App\Entity\Prediction;
use App\Entity\PredictionTest;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;


class ApiController extends AbstractController
{
    /**
     * @Route("/", name="homepage")
     */
    public function index()
    {
        $cities = $this->getCities();
        $data = '
        {
            "predictions": [
            {
              "scale": "Fahrenheit",
              "city": "Amsterdam",
              "date": "20220319",
              "prediction": [
                {
                  "time": "00:00",
                  "value": "31"
                },
                {
                  "time": "01:00",
                  "value": "32"
                }
              ]
            },
            {
              "scale": "Fahrenheit",
              "city": "Rotterdam",
              "date": "20220320",
              "prediction": [
                {
                  "time": "00:00",
                  "value": "10"
                },
                {
                  "time": "01:00",
                  "value": "12"
                }
              ]
            }
            ]
        }';     
        
        $data = json_decode($data, true);

        $predictions = PredictionTest::denormalize($data);

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
     * @Route("/test", name="json")
     */
    public function test()
    {
        
        $data = '
        {
            "predictions": [
            {
              "scale": "Fahrenheit",
              "city": "Amsterdam",
              "date": "20220319",
              "prediction": [
                {
                  "time": "00:00",
                  "value": "31"
                },
                {
                  "time": "01:00",
                  "value": "32"
                }
              ]
            },
            {
              "scale": "Fahrenheit",
              "city": "Rotterdam",
              "date": "20220320",
              "prediction": [
                {
                  "time": "00:00",
                  "value": "10"
                },
                {
                  "time": "01:00",
                  "value": "12"
                }
              ]
            }
            ]
        }';     
        
        $data = json_decode($data, true);

        $predictions = PredictionTest::denormalize($data);

        $predictions = array_filter($predictions, function ( $obj ) {
            return $obj->getCity() == 'Amsterdam';
        });

        return $this->render('test.html.twig', [
            'predictions' => $predictions,
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