<?php

namespace App\Repository;

use App\Entity\RobertKochInstitute;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method RobertKochInstitute|null find($id, $lockMode = null, $lockVersion = null)
 * @method RobertKochInstitute|null findOneBy(array $criteria, array $orderBy = null)
 * @method RobertKochInstitute[]    findAll()
 * @method RobertKochInstitute[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RobertKochInstituteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RobertKochInstitute::class);
    }

    /**
     * Will save new entity in repository or update the existing one
     *
     * @param RobertKochInstitute $institute
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function save(RobertKochInstitute $institute){
        $this->_em->persist($institute);
        $this->_em->flush();;
    }

    /**
     * Will return last created entity or null if none was created so far
     *
     * @return RobertKochInstitute|null
     */
    public function getLastCreatedEntity(): ?RobertKochInstitute
    {
        return $this->findOneBy([],[
            RobertKochInstitute::FIELD_ENTITY_CREATE_DATE_TIME => "DESC"
        ]);
    }
}
