<?php

namespace App\ApiPlatform\CurrentUserExtension;

use App\ApiPlatform\CurrentUserExtension\FilterEntityInterface;

use App\ApiPlatform\CurrentUserExtension\Filters\PVAnomalyFilter;
use App\ApiPlatform\CurrentUserExtension\Filters\ShootingFilter;
use App\ApiPlatform\CurrentUserExtension\Filters\SiteFilter;
use App\ApiPlatform\CurrentUserExtension\Filters\UserFilter;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\PVAnomaly;
use App\Entity\Shooting;
use App\Entity\Site;
use App\Entity\User;

class FilterEntityFactory
{
  private array $mappingEntity = [
    User::class => UserFilter::class,
    Site::class => SiteFilter::class,
    PVAnomaly::class => PVAnomalyFilter::class,
    Shooting::class => ShootingFilter::class
  ];

  public function __construct(
    private readonly EntityManagerInterface $entityManager
  ) {
  }

  public function createFilter($resourceClass): ?FilterEntityInterface
  {
    //TODO: Faire en sorte de récupérer les dependances(repo) dynamiquement
    if (array_key_exists($resourceClass, $this->mappingEntity)) {
      $filterClass = $this->mappingEntity[$resourceClass];

      switch ($resourceClass) {
        case Shooting::class:
          return new $filterClass($this->entityManager->getRepository(Site::class));
        case PVAnomaly::class:
          return new $filterClass($this->entityManager->getRepository(Shooting::class), $this->entityManager->getRepository(Site::class));
      }

      return new $filterClass();
    }

    return null;
  }
}
