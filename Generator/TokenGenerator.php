<?php

namespace Bigfoot\Bundle\CoreBundle\Generator;

class TokenGenerator
{
    public function generateToken()
    {
        return base_convert(sha1(uniqid(mt_rand(), true)), 16, 36);
    }
}
