<?php

namespace App\Repository;

use App\Entity\Genre;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Genre>
 */
class GenreRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Genre::class);
    }

    public function findByName(string $name): ?Genre
    {
        return $this->createQueryBuilder('genre')
            ->andWhere('genre.name = :name')
            ->setParameter('name', $name)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function store(Genre $genre): Genre
    {
        $this->getEntityManager()->persist($genre);
        $this->getEntityManager()->flush();

        return $genre;
    }
}
