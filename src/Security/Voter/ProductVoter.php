<?php

namespace App\Security\Voter;

use App\Entity\Product;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class ProductVoter extends Voter{
    const EDIT = 'PRODUCT_EDIT';
    const DELETE = 'PRODUCT_DELETE';

    private $security;

    public function __construct(Security $security){
        $this->security = $security;
    }

    protected function supports(string $attribute, $product) : bool {
        return
            in_array($attribute, [self::EDIT, self::DELETE]) &&
            $product instanceof Product
        ;
    }

    protected function voteOnAttribute(
        $attribute,
        $product,
        TokenInterface $token
    ) : bool {
        # Get user with its token
        $user = $token->getUser();
        if(!$user instanceof UserInterface){return false;};

        # Checking if user is an administrator
        if($this->security->isGranted('ROLE_ADMIN')){return true;};

        # Checking user's permissions
        switch($attribute){
            case self::EDIT :{
                return $this->security->isGranted('ROLE_PRODUCT_ADMIN');
                break;
            };
            case self::DELETE :{
                return $this->security->isGranted('ROLE_PRODUCT_ADMIN');
                break;
            };
            default :{
                return false;
                break;
            };
        };
    }
}