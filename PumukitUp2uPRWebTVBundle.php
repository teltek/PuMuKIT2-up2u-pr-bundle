<?php

namespace Pumukit\Up2u\PR\WebTVBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class PumukitUp2uPRWebTVBundle extends Bundle
{
  const VERSION = '1.0.0-dev';
  public function getParent()
  {
    return 'PumukitWebTVBundle';
  }
}
