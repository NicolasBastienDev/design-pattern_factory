<?php

namespace App\ApiPlatform\CurrentUserExtension;

use App\Entity\User;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\SecurityBundle\Security;

interface FilterEntityInterface
{
    public function needsFullUser(): bool;
    public function applyFilter(QueryBuilder $queryBuilder, Security $security): void;
}