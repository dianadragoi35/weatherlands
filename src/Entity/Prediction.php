<?php

namespace App\Entity;

use Symfony\Component\Serializer\Normalizer\DenormalizableInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\PredictionDateRepository;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\PredictionTime;

use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;


/**
 * @ApiResource()
 * @ORM\Entity(repositoryClass=PredictionRepository::class)
 */
class Prediction /* implements DenormalizableInterface */
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $scale;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $city;

    /**
     * @ORM\Column(type="string", length=8)
     */
    private $date;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\PredictionTime", mappedBy="prediction")
     */
    private $predictions;

    /**
     * @return mixed
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getScale(): ?string
    {
        return $this->scale;
    }

    /**
     * @param mixed $scale
     */
    public function setScale(string $scale): self
    {
        $this->scale = $scale;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCity(): ?string
    {
        return $this->city;
    }

    /**
     * @param mixed $city
     */
    public function setCity(string $city): self
    {
        $this->city = $city;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDate(): ?string
    {
        return $this->date;
    }

    /**
     * @param mixed $date
     */
    public function setDate(string $date): self
    {
        $this->date = $date;

        return $this;
    }

    /**
     * @return array $predictions
     */
    public function getPredictions()
    {
        return $this->predictions;
    }

    public function addPrediction(PredictionTime $prediction): self
    {
        $this->predictions[] = $prediction;           
        
        return $this;
    }   

    /* 
     * @param $data type JSON
     **/
    public static function denormalizeJSON($data) {

        $predictions = array();
        foreach($data['predictions'] as $predictionDate){
            $prediction = new Prediction;
            if (isset($predictionDate['scale'])) {
                $prediction->setScale($predictionDate['scale']);
            }
            if (isset($predictionDate['city'])) {
                $prediction->setCity($predictionDate['city']);
            }
            if (isset($predictionDate['date'])) {
                $prediction->setDate($predictionDate['date']);  
            }
            foreach($predictionDate['prediction'] as $predictionTime){
                $pt = new PredictionTime;
                $pt->setTime($predictionTime['time']);
                $pt->setValue($predictionTime['value']);
                $prediction->addPrediction($pt);  
            }

            $predictions[] = $prediction;
        }
        

        return $predictions;
    }  

    /* 
     * @param $data type XML
     **/
    public static function denormalizeXml($data) {

        $predictions = array();
        foreach($data['predictions'] as $predictionDate){
            $prediction = new Prediction;
            if (isset($predictionDate['@attributes']['scale'])) {
                $prediction->setScale($predictionDate['@attributes']['scale']);
            }
            if (isset($predictionDate['city'])) {
                $prediction->setCity($predictionDate['city']);
            }
            if (isset($predictionDate['date'])) {
                $prediction->setDate($predictionDate['date']);  
            }
            foreach($predictionDate['prediction'] as $predictionTime){
                $pt = new PredictionTime;
                $pt->setTime($predictionTime['time']);
                $pt->setValue($predictionTime['value']);
                $prediction->addPrediction($pt);  
            }

            $predictions[] = $prediction;
        }        

        return $predictions;
    }  

    /* 
     * @param $data type CSV
     **/
    public static function denormalizeCsv($data) {

        $predictions = array();
        $prediction = new Prediction;
        foreach($data as $key => $predictionDate){
            
            if ($key == 1) {                
                $prediction->setScale($predictionDate[0]);
                $prediction->setCity($predictionDate[1]);
                $prediction->setDate($predictionDate[2]);    
                
                $pt = new PredictionTime;
                $pt->setTime($predictionDate[3]);
                $pt->setValue($predictionDate[4]);
                $prediction->addPrediction($pt); 
            }
            else if ($key > 1) {
                $pt = new PredictionTime;
                $pt->setTime($predictionDate[3]);
                $pt->setValue($predictionDate[4]);
                $prediction->addPrediction($pt); 
            }            

            
        } 
        $predictions[] = $prediction;       

        return $predictions;
    } 
}
