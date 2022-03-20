<?php

namespace App\Controller;

use App\Entity\Prediction;
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

        $data = file_get_contents('../tests/Resources/provider1.json');
        $data = json_decode($data, true);

        $predictions = Prediction::denormalize($data);

        $predictions = array_filter($predictions, function ( $obj ) {
            return ($obj->getCity() == 'Amsterdam' || $obj->getCity() == 'Rotterdam');
        });       

        return $this->render('home.html.twig', [
            'cities'      => $cities,
            'predictions' => $predictions,
        ]);
    }

   
    /**
     * @Route("/predictions/{city}-{scale}-{date}", name="predictions")
     */
    public function getPredictions($city, $scale, $date)
    {
        $cities   = $this->getCities();
        $scales   = $this->getScales();
        $nextDays = $this->getNext10Days();

        if(!$date){
            $date = date('Ymd');
        }

        $providersPrediction = array();
        $jsonProviders   = $this->getFilesByPattern('*.json');

        foreach ($jsonProviders as $provider) {
            $aux = file_get_contents($provider);
            $aux = json_decode($aux, true);        

            $providersPrediction[] = Prediction::denormalize($aux);
        }

        $predictions = array();
        foreach ($providersPrediction as $providerPrediction) {
            $predictions[] = array_filter($providerPrediction, function ( $obj ) use ($city, $scale, $date) {
                return (strtolower($obj->getCity()) == strtolower($city) && strtolower($obj->getScale()) == strtolower($scale) && $obj->getDate() == $date);
            }); 
        }

        $timePredictions = array();
        foreach ($predictions as $prediction) {
            foreach ($prediction as $value) {
                $timePredictions[] = $value->getPredictions();    
            }
        }
        
        // $data = file_get_contents('../tests/Resources/provider1.json');
        // $data = json_decode($data, true);        

        // $predictions = Prediction::denormalize($data);

        // $predictions = array_filter($predictions, function ( $obj ) use ($city, $scale, $date) {
        //     return (strtolower($obj->getCity()) == strtolower($city) && strtolower($obj->getScale()) == strtolower($scale) && $obj->getDate() == $date);
        // });       

        //$timePredictions = array();
        // foreach ($predictions as $value) {
        //     $timePredictions[] = $value->getPredictions();    
        // }

        return $this->render('api.html.twig', [
            'cities'      => $cities,
            'scales'      => $scales,
            'nextDays'    => $nextDays,
            'city'        => $city,
            'scale'       => $scale,
            'date'        => $date,
            'predictions' => $timePredictions,
            'providerPrediction' => $providersPrediction,
        ]);
    }

   /**
     * @Route("/city/{city}", name="city")
     */
    public function getPredictionsByCity($city)
    {
        $cities = $this->getCities();

        $data = file_get_contents('../tests/Resources/provider1.json');
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

    private function getAveragePrediction(array $predictions){ 

        $averagePrediction = array();
        foreach ($predictions as $prediction) {
            
        }

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

    private function getFilesByPattern($pattern)
    {   
       return glob("../tests/Resources/".$pattern);
    }

     /**
     * @return array cities
     */
    public function getCities()
    {
        $cities = ['Amsterdam', 'Rotterdam', 'Einhoven'];      

        return $cities;
    }

    /**
     * @return array scales
     */
    public function getScales()
    {
        $scales = ['Fahrenheit','Clesius'];      

        return $scales;
    }

    /**
     * @return array days
     */
    public function getNext10Days()
    {
        $date     = date('Y-m-d');
        $nextDays = array();
        for($i =1; $i <= 10; $i++){
            $nextDays[] = date('Y-m-d', strtotime($date));
            $date = date('Y-m-d', strtotime('+1 day', strtotime($date)));
        }
        return $nextDays;
    }
}