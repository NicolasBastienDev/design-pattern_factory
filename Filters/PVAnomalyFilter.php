<?php

namespace App\ApiPlatform\CurrentUserExtension\Filters;

use App\ApiPlatform\CurrentUserExtension\FilterEntityInterface;
use App\Entity\User;
use App\Repository\ShootingRepository;
use App\Repository\SiteRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\SecurityBundle\Security;

class PVAnomalyFilter implements FilterEntityInterface
{

    public function __construct(
        private readonly ShootingRepository $shootingRepository,
        private readonly SiteRepository $siteRepository
    )
    {
    }

    public function needsFullUser(): bool
    {
        return true;
    }

    public function applyFilter(QueryBuilder $queryBuilder, Security $security): void
    {
        $user = $security->getUser();
        assert($user instanceof \App\Entity\User, "Invalid user provided to CustomUserExtension filter");

        $rootAlias = $queryBuilder->getRootAliases()[0];

        $userSites = $this->siteRepository->findBy(['company' => $user->getUserGroup()]);
        $siteIds = array_map(fn($site) => $site->getId(), $userSites);

        $userShootings = $this->shootingRepository->findBy(['site' => $siteIds]);
        $shootingIds = array_map(fn($shooting) => $shooting->getId(), $userShootings);

        $queryBuilder->andWhere(
            $queryBuilder->expr()->in(
                sprintf('%s.shooting', $rootAlias),
                $shootingIds
            )
        );

    }
}