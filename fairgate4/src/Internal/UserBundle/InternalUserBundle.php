<?php

namespace Internal\UserBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class InternalUserBundle extends Bundle
{
    public function getParent()
    {
        return 'FOSUserBundle';
    }
}
