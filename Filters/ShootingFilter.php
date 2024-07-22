<?php

namespace App\ApiPlatform\CurrentUserExtension\Filters;

use App\ApiPlatform\CurrentUserExtension\FilterEntityInterface;
use App\Entity\User;
use App\Repository\SiteRepository;
use Doctrine\ORM\QueryBuilder;
use Exception;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class ShootingFilter implements FilterEntityInterface
{

    public function __construct(private readonly SiteRepository $siteRepository)
    {
    }

    public function needsFullUser(): bool
    {
        return true;
    }

    public function applyFilter(QueryBuilder $queryBuilder, Security $security): void
    {
        if ($security->isGranted("ROLE_GLOBAL_SITE_MANAGER")) return;
        
        $user = $security->getUser();
        assert($user instanceof \App\Entity\User, "Invalid user provided to CustomUserExtension filter");
        $rootAlias = $queryBuilder->getRootAliases()[0];

        $sites = $this->siteRepository->findBy(['company' => $user->getUserGroup()]);

        $idSites = [];

        foreach ($sites as $site) {
            $idSites[] = $site->getId();
        }

        if(count($idSites) === 0) {
            throw new AccessDeniedHttpException('You do not have access to this resource.');
        }

        $queryBuilder->andWhere($queryBuilder->expr()
                    ->in(sprintf('%s.site', $rootAlias), $idSites));
    }
}