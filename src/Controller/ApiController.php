<?php

namespace App\Controller;

use App\Entity\Prediction;
use App\Entity\PredictionTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class ApiController extends AbstractController
{
    /**
     * @Route("/", name="homepage")
     */
    public function index()
    {       
        return $this->render('home.html.twig',[
            'predictions' => null,
            'providers'   => null,
        ]);
    }
   
    /**
     * @Route("/predictions/{city}-{date}-{scale}", name="predictions")
     */
    public function getPredictionsUi($city, $date, $scale)
    {
        $cities   = $this->getCities();
        $scales   = $this->getScales();
        $nextDays = $this->getNext10Days();

        if(!$date || $date < date('Ymd') || $date > str_replace('-', '', $nextDays[count($nextDays) - 1])){
            $date = date('Ymd');
        }
        
        /* Read data from JSON */
        $providersPrediction = array();
        $jsonProviders       = $this->getFilesByPattern('*.json');

        foreach ($jsonProviders as $provider) {
            $aux = file_get_contents($provider);
            $aux = json_decode($aux, true);        

            $providersPrediction[] = Prediction::denormalizeJSON($aux);
        }

        /* Read data from XML */
        $xmlProviders = $this->getFilesByPattern('*.xml');

        foreach ($xmlProviders as $provider) {
            $aux  = file_get_contents($provider);
            $xml  = simplexml_load_string($aux, "SimpleXMLElement", LIBXML_NOCDATA);
            $json = json_encode($xml);
            $aux  = json_decode($json,TRUE);
            $providersPrediction[] = Prediction::denormalizeXml($aux);
        }

        /* Read data from CSV */
        $csvProviders = $this->getFilesByPattern('*.csv');

        foreach ($csvProviders as $provider) {
            $aux   = file_get_contents($provider);            
            $array = array_map("str_getcsv", explode("\n", $aux));           
            $providersPrediction[] = Prediction::denormalizeCsv($array);
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

        $predictions = $this->getAveragePrediction($timePredictions);
        
        return $this->render('api.html.twig', [
            'cities'      => $cities,
            'scales'      => $scales,
            'nextDays'    => $nextDays,
            'city'        => $city,
            'scale'       => $scale,
            'date'        => $date,
            'predictions' => $predictions,
        ]);
    }

   /**
     * @Route("/view/{city}-{date}-{scale}", name="view")
     */
    public function getPredictionsApi($city, $date, $scale)
    {           
        $cities   = $this->getCities();
        $scales   = $this->getScales();
        $nextDays = $this->getNext10Days();

        if(!$date || $date < date('Ymd') || $date > str_replace('-', '', $nextDays[count($nextDays) - 1])){
            $date = date('Ymd');
        }

        /* Read data from JSON */
        $providersPrediction = array();
        $jsonProviders       = $this->getFilesByPattern('*.json');

        foreach ($jsonProviders as $provider) {
            $aux = file_get_contents($provider);
            $aux = json_decode($aux, true);        

            $providersPrediction[] = Prediction::denormalizeJSON($aux);
        }

        /* Read data from XML */
        $xmlProviders = $this->getFilesByPattern('*.xml');

        foreach ($xmlProviders as $provider) {
            $aux  = file_get_contents($provider);
            $xml  = simplexml_load_string($aux, "SimpleXMLElement", LIBXML_NOCDATA);
            $json = json_encode($xml);
            $aux  = json_decode($json,TRUE);
            $providersPrediction[] = Prediction::denormalizeXml($aux);
        }

        /* Read data from CSV */
        $csvProviders = $this->getFilesByPattern('*.csv');

        foreach ($csvProviders as $provider) {
            $aux   = file_get_contents($provider);            
            $array = array_map("str_getcsv", explode("\n", $aux));           
            $providersPrediction[] = Prediction::denormalizeCsv($array);
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

        $predictions = $this->getAveragePrediction($timePredictions);
        
        return $this->render('home.html.twig', [
            'cities'      => $cities,
            'scales'      => $scales,
            'nextDays'    => $nextDays,
            'city'        => $city,
            'scale'       => $scale,
            'date'        => $date,
            'predictions' => $predictions,
            'providers'   => null,
        ]);
        
    }

    /**
     * @Route("/view-provider/{city}-{date}-{scale}", name="view-provider")
     */
    public function getPredictionsPerProvider($city, $date, $scale)
    {           
        $cities   = $this->getCities();
        $scales   = $this->getScales();
        $nextDays = $this->getNext10Days();

        if(!$date || $date < date('Ymd') || $date > str_replace('-', '', $nextDays[count($nextDays) - 1])){
            $date = date('Ymd');
        }

        $providersPrediction = array();
        $jsonProviders       = $this->getFilesByPattern('*.json');

         /* Read data from JSON */
        foreach ($jsonProviders as $provider) {
            $aux = file_get_contents($provider);
            $aux = json_decode($aux, true);        

            $providersPrediction[] = Prediction::denormalizeJSON($aux);
        }

        /* Read data from XML */
        $xmlProviders = $this->getFilesByPattern('*.xml');

        foreach ($xmlProviders as $provider) {
            $aux  = file_get_contents($provider);
            $xml  = simplexml_load_string($aux, "SimpleXMLElement", LIBXML_NOCDATA);
            $json = json_encode($xml);
            $aux  = json_decode($json,TRUE);
            $providersPrediction[] = Prediction::denormalizeXml($aux);
        }

        /* Read data from CSV */
        $csvProviders = $this->getFilesByPattern('*.csv');

        foreach ($csvProviders as $provider) {
            $aux   = file_get_contents($provider);            
            $array = array_map("str_getcsv", explode("\n", $aux));           
            $providersPrediction[] = Prediction::denormalizeCsv($array);
        }

        $predictions = array();
        foreach ($providersPrediction as $providerPrediction) {
            $predictions[] = array_filter($providerPrediction, function ( $obj ) use ($city, $scale, $date) {
                return (strtolower($obj->getCity()) == strtolower($city) && strtolower($obj->getScale()) == strtolower($scale) && $obj->getDate() == $date);
            }); 
        }
        
        return $this->render('home.html.twig', [
            'cities'      => $cities,
            'scales'      => $scales,
            'nextDays'    => $nextDays,
            'city'        => $city,
            'scale'       => $scale,
            'date'        => $date,
            'predictions' => null,
            'providers'   => $predictions,
        ]);
        
    }

    /**
     * @return array PredictionTime
     * Average predictions from different providers
     */
    private function getAveragePrediction(array $timePredictions){ 

        $averagePrediction = array();
        if(isset($timePredictions[0])){
        
            foreach ($timePredictions[0] as $avg) {
                $new = new PredictionTime;
                $new->setValue(0);
                $new->setTime($avg->getTime());
                $averagePrediction[] = $new;
            }
            foreach ($timePredictions as $key => $prediction) {
                foreach($prediction as $key2 => $aux){
                    $value = $averagePrediction[$key2]->getValue();
                    $averagePrediction[$key2]->setValue($value + $prediction[$key2]->getValue());
                }
            
            }
            
            foreach ($averagePrediction as $key => $prediction) {
                $averagePrediction[$key]->setValue(($averagePrediction[$key]->getValue())/count($timePredictions));
            }

        }

        return $averagePrediction;

    }
    
    /**
     * @return data provided in JSON format
     *  */
    public function getJsonFromMockServer()
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://eface2b6-d896-4e7a-8856-b714cb5ec1c5.mock.pstmn.io/json');
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/json')); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $response = curl_exec($ch);
        $data     = json_decode($response);

        return $data;
    }

    /**
     * @return data provided in XML format
     *  */
    public function getXmlFromMockServer()
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://eface2b6-d896-4e7a-8856-b714cb5ec1c5.mock.pstmn.io/xml');
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/json')); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $response = curl_exec($ch);
        $data     = simplexml_load_string($response, "SimpleXMLElement", LIBXML_NOCDATA);

        return $data;
    }

    /**
     * @return data provided in CSV format
     *  */
    public function getCsvFromMockServer()
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://eface2b6-d896-4e7a-8856-b714cb5ec1c5.mock.pstmn.io/csv');
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/json')); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $response = curl_exec($ch);
        $data     = array_map("str_getcsv", explode("\n", $response)); 

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
        $providersPrediction = array();
        $cities = array();

        $jsonProviders   = $this->getFilesByPattern('*.json');

        foreach ($jsonProviders as $provider) {
            $aux = file_get_contents($provider);
            $aux = json_decode($aux, true);        

            $providersPrediction[] = Prediction::denormalizeJSON($aux);
        }

        foreach ($providersPrediction as $prediction) {
            foreach ($prediction as $value) {
                $cities[] = $value->getCity();
            }
        }

        $cities = array_unique($cities);

        return $cities;
    }

    /**
     * @return array scales
     */
    public function getScales()
    {
        $scales = ['Fahrenheit','Celsius'];      

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

    /**
     * @return int $f
     */
    public function convertCelsiusToFahrenheit($c)
    {
        $f = ($c * 9.5) + 32;

        return $f;
    }

    /**
     * @return int $c
     */
    public function convertFahrenheitToCelsius($f)
    {
        $c = ($f - 32) / 1.8;
        return $c;
    }
}