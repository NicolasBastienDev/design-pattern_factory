<?php

namespace App\ApiPlatform\CurrentUserExtension\Filters;

use App\ApiPlatform\CurrentUserExtension\FilterEntityInterface;
use App\Entity\User;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\SecurityBundle\Security;

class UserFilter implements FilterEntityInterface
{
    public function __construct()
    {
    }

    public function needsFullUser(): bool
    {
        return true;
    }

    public function applyFilter(QueryBuilder $queryBuilder, Security $security): void
    {
        if($security->isGranted("ROLE_GLOBAL_USER_MANAGER")) return;

        $user = $security->getUser();
        assert($user instanceof \App\Entity\User, "Invalid user provided to CustomUserExtension filter");

        $rootAlias = $queryBuilder->getRootAliases()[0];
        $queryBuilder->andWhere(sprintf('%s.userGroup = :userGroup', $rootAlias))->setParameter('userGroup', $user->getUserGroup());
    }
}