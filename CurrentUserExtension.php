<?php

namespace App\ApiPlatform\CurrentUserExtension;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\User;
use App\ApiPlatform\CurrentUserExtension\FilterEntityFactory;
use App\Repository\UserRepository;
use Doctrine\ORM\QueryBuilder;
use Exception;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;  

class CurrentUserExtension implements QueryCollectionExtensionInterface, QueryItemExtensionInterface
{
    public function __construct(
        private readonly Security $security,
        private readonly UserRepository $userRepository,
        private readonly FilterEntityFactory $filterEntityFactory
    )
    {

    }

    private function loadFullUserToSecurity(): bool
    {
        $currentUser = $this->security->getUser();
        if (!$currentUser instanceof User)
            return false;

        $token = $this->security->getToken();
        if (!$token instanceof AbstractToken) 
            return false;


        $newUser = $this->userRepository->findOneBy(['email' => $currentUser->getEmail()]);
        if ($newUser === null) 
            return false;

        $token->setUser($newUser);
        return true;
    }    

    public function applyToCollection(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, Operation $operation = null, array $context = []): void
    {       
        $filter = $this->filterEntityFactory->createFilter($resourceClass);
        if($filter === null) return; //Factory doesn't know about ressourceClass thus no additional check is required for user access

        if (!(!$filter->needsFullUser() || $this->loadFullUserToSecurity())) {
            throw new Exception("Can't retreive security user");
        }   

        $filter->applyFilter($queryBuilder, $this->security);
    }

    public function applyToItem(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, array $identifiers, Operation $operation = null, array $context = []): void
    {
        $filter = $this->filterEntityFactory->createFilter($resourceClass);
        if ($filter === null) return; //Factory doesn't know about ressourceClass thus no additional check is required for user access

        if (!(!$filter->needsFullUser() || $this->loadFullUserToSecurity())) {
            throw new Exception("Can't retreive security user");
        }

        $filter->applyFilter($queryBuilder, $this->security);
    }
}