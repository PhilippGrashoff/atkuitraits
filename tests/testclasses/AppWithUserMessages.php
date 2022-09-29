<?php declare(strict_types=1);

namespace atkuitraits\tests\testclasses;

use Atk4\Ui\App;
use atkuitraits\UserMessages;

class AppWithUserMessages extends App
{

    public UserMessages $userMessages;
    public $always_run = false;

    public function __construct($defaults = [])
    {
        parent::__construct($defaults);
        $this->userMessages = new UserMessages();
    }
}
