<?php


namespace Ferus\EventBundle\Services;

use Doctrine\ORM\EntityManager;
use Ferus\EventBundle\Entity\Event;

class CsvGenerator 
{
    /**
     * @var EntityManager
     */
    private $em;
    public function __construct(EntityManager $entityManager)
    {
        $this->em = $entityManager;
    }

    public function generateParking(Event $event)
    {
        $data = $this->em->getRepository('FerusEventBundle:CarRequest')->findFromEvent($event);

        if(count($data) == 0) return '';

        $headers = array_keys($data[0]);

        return implode(';', $headers)."\n".implode("\n", array_map(function($t){return implode(';', $t);}, $data));
    }

    public function generate(Event $event)
    {
        $data = $this->em->getRepository('FerusEventBundle:Participation')->findFromEvent($event);

        if(count($data) == 0) return '';

        $headers = array('Id', 'Nom', 'Email', 'Payé');

        foreach($event->getExtraFields() as $field)
            $headers[] = $field;

        foreach($event->getOptions() as $option)
            $headers[] = $option;

        return implode(';', $headers)."\n".implode("\n", array_map(function($t){return implode(';', $t);}, $data));
    }
} 