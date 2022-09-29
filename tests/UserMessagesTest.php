<?php declare(strict_types=1);

namespace atkuitraits\tests;

use Atk4\Core\AtkPhpunit\TestCase;
use Atk4\Data\Exception;
use Atk4\Data\ValidationException;
use Atk4\Ui\App;
use atk4\ui\jsToast;
use atkuitraits\tests\testclasses\AppWithUserMessages;
use atkuitraits\UserMessages;
use traitsforatkdata\UserException;

class UserMessagesTest extends TestCase
{

    private App $app;

    protected function setUp(): void
    {
        parent::setUp();
        $this->app = new AppWithUserMessages();
    }

    public function testChaining(): void
    {
        $userMessages = new UserMessages();
        $userMessages->addSuccessMessage('1')->addSuccessMessage('2');
        self::assertSame(2, count($userMessages->getAsJsToasts()));
    }


    public function testDuration(): void
    {
        $userMessages = new UserMessages();
        $userMessages->addSuccessMessage('TestMessage1');
        $userMessages->addWarningMessage('TestMessage1');
        $userMessages->addErrorMessage('TestMessage1');
        $res = $userMessages->getAsJsToasts();
        self::assertEquals(3000, $res[0]->settings['displayTime']);
        self::assertEquals(8000, $res[1]->settings['displayTime']);
        self::assertEquals(10000, $res[2]->settings['displayTime']);
    }

    public function testAddExceptionDataException(): void
    {
        try {
            throw new Exception('Some Error');
        } catch (Exception $e) {
            $this->app->userMessages->addException($e);
            $jsToasts = $this->app->userMessages->getAsJsToasts();
            self::assertStringContainsString(
                'Ein technischer Fehler ist aufgetreten',
                $jsToasts[0]->settings['message']
            );
        }
    }

    public function testAddExceptionUserException(): void
    {
        try {
            throw new UserException('Some Error Duggu');
        } catch (UserException $e) {
            $this->app->userMessages->addException($e);
            $jsToasts = $this->app->userMessages->getAsJsToasts();
            self::assertStringContainsString(
                'Some Error Duggu',
                $jsToasts[0]->settings['message']
            );
        }
    }

    public function testAddExceptionSingleValidationException(): void
    {
        try {
            throw new ValidationException(['Some Error']);
        } catch (ValidationException $e) {
            $this->app->userMessages->addException($e);
            $jsToasts = $this->app->userMessages->getAsJsToasts();
            self::assertStringContainsString(
                'Some Error',
                $jsToasts[0]->settings['message']
            );
        }
    }

    public function testAddExceptionMultipleValidationException(): void
    {
        try {
            throw new ValidationException(['Some Error1', 'Some Error2']);
        } catch (ValidationException $e) {
            $this->app->userMessages->addException($e);
            $jsToasts = $this->app->userMessages->getAsJsToasts();
            self::assertStringContainsString(
                'Some Error1',
                $jsToasts[0]->settings['message']
            );
            self::assertStringContainsString(
                'Some Error2',
                $jsToasts[1]->settings['message']
            );
        }
    }
}