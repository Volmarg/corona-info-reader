<?php

namespace App\Controller;

use App\Entity\RobertKochInstitute;
use App\Repository\RobertKochInstituteRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class RobertKochInstituteController extends AbstractController
{
    /**
     * @var RobertKochInstituteRepository $robertKochInstituteRepository
     */
    private RobertKochInstituteRepository $robertKochInstituteRepository;

    public function __construct(RobertKochInstituteRepository $robertKochInstituteRepository)
    {
        $this->robertKochInstituteRepository = $robertKochInstituteRepository;
    }

    /**
     * Will save new entity in repository or update the existing one
     *
     * @param RobertKochInstitute $institute
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function save(RobertKochInstitute $institute){
        $this->robertKochInstituteRepository->save($institute);
    }

    /**
     * Will return last created entity or null if none was created so far
     *
     * @return RobertKochInstitute|null
     */
    public function getLastCreatedEntity(): ?RobertKochInstitute
    {
        return $this->robertKochInstituteRepository->getLastCreatedEntity();
    }
}
