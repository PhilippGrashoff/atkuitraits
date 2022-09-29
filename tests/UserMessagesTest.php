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

    public function testAddMessage(): void
    {
        $userMessages = new UserMessages();
        $userMessages->addMessage('TestMessage1', 'success');
        $userMessages->addMessage('TestMessage2');
        $userMessages->addMessage('TestMessage3', 'error');
        $userMessages->addMessage('TestMessage4', 'warning');
        self::assertEquals(count($userMessages->getAsJsToasts()), 4);
        self::assertTrue($userMessages->getAsJsToasts()[0] instanceof jsToast);
        $htmlstring = $userMessages->getAsHtml();
        self::assertTrue(strpos($htmlstring, 'class="ui message') !== false);
        $inlinehtml = $userMessages->getAsHtml(true);
        self::assertTrue(strpos($inlinehtml, 'style="color:') !== false);
    }

    public function testSetDuration(): void
    {
        $userMessages = new UserMessages();
        $userMessages->addMessage('TestMessage1', 'success', 2000);
        $res = $userMessages->getAsJsToasts();
        self::assertEquals(2000, $res[0]->settings['displayTime']);

        $userMessages->addMessage('TestMessage1', 'success', 0);
        $res = $userMessages->getAsJsToasts();
        self::assertEquals(0, $res[1]->settings['displayTime']);

        $userMessages->addMessage('TestMessage1', 'success');
        $res = $userMessages->getAsJsToasts();
        self::assertEquals(3000, $res[2]->settings['displayTime']);

        $userMessages->addMessage('TestMessage1', 'warning');
        $res = $userMessages->getAsJsToasts();
        self::assertEquals(8000, $res[3]->settings['displayTime']);

        $userMessages->addMessage('TestMessage1', 'error');
        $res = $userMessages->getAsJsToasts();
        self::assertEquals(8000, $res[4]->settings['displayTime']);
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