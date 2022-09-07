<?php declare(strict_types=1);

namespace atkuitraits\tests;

use Atk4\Core\AtkPhpunit\TestCase;
use Atk4\Data\ValidationException;
use atk4\ui\jsToast;
use atkuitraits\UserMessageTrait;

class TestClassForUserMessageTrait {
    use UserMessageTrait;
}

class UserMessageTraitTest extends TestCase {

    public function testAddMessage() {
        $v = new TestClassForUserMessageTrait();
        $v->addUserMessage('TestMessage1', 'success');
        self::assertEquals($v->userMessages[0]['message'], 'TestMessage1');
        $v->addUserMessage('TestMessage2');
        $v->addUserMessage('TestMessage3', 'error');
        $v->addUserMessage('TestMessage4', 'warning');
        self::assertEquals(count($v->getUserMessagesAsJsToast()), 4);
        self::assertTrue($v->getUserMessagesAsJsToast()[0] instanceOf jsToast);
        $htmlstring = $v->getUserMessagesAsHTML();
        self::assertTrue(strpos($htmlstring, 'class="ui message') !== false);
        $inlinehtml = $v->getUserMessagesAsHTML(true);
        self::assertTrue(strpos($inlinehtml, 'style="color:') !== false);
    }

    public function testSetDuration() {
        $v = new TestClassForUserMessageTrait();
        $v->addUserMessage('TestMessage1', 'success', 2000);
        $res = $v->getUserMessagesAsJsToast();
        self::assertEquals(2000, $res[0]->settings['displayTime']);

        $v->addUserMessage('TestMessage1', 'success', 0);
        $res = $v->getUserMessagesAsJsToast();
        self::assertEquals(0, $res[1]->settings['displayTime']);

        $v->addUserMessage('TestMessage1', 'success');
        $res = $v->getUserMessagesAsJsToast();
        self::assertEquals(3000, $res[2]->settings['displayTime']);

        $v->addUserMessage('TestMessage1', 'warning');
        $res = $v->getUserMessagesAsJsToast();
        self::assertEquals(8000, $res[3]->settings['displayTime']);

        $v->addUserMessage('TestMessage1', 'error');
        $res = $v->getUserMessagesAsJsToast();
        self::assertEquals(8000, $res[4]->settings['displayTime']);
    }

    public function testOutputExceptionTraitDataException() {
        $app = new App(['nologin'], ['always_run' => false]);
        $app->initLayout([Admin::class]);
        $v   = $app->add(new TestViewForOutputException());
        try {
            throw new \atk4\data\Exception('Some Error');
        }
        catch(\Exception $e) {
            $res = $v->outputException($e);
            self::assertTrue(strpos($res[0], 'Ein technischer Fehler ist aufgetreten') !== false);
        }
    }

    public function testOutputExceptionTraitUserException() {
        $app = new App(['nologin'], ['always_run' => false]);
        $app->initLayout([Admin::class]);
        $v   = $app->add(new TestViewForOutputException());
        try {
            throw new UserException('Some Error Duggu');
        }
        catch(\Exception $e) {
            $res = $v->outputException($e);
            self::assertTrue(strpos($res[0], 'Some Error Duggu') !== false);
        }
    }

    public function testOutputExceptionTraitSingleValidationException() {
        $app = new App(['nologin'], ['always_run' => false]);
        $app->initLayout([Admin::class]);
        $v   = $app->add(new TestViewForOutputException());
        try {
            throw new ValidationException(['Some Error']);
        }
        catch(\Exception $e) {
            $res = $v->outputException($e);
            self::assertTrue(strpos($res[0], 'Some Error') !== false);
        }
    }

    public function testOutputExceptionTraitMultipleValidationException() {
        $app = new App(['nologin'], ['always_run' => false]);
        $app->initLayout([Admin::class]);
        $v   = $app->add(new TestViewForOutputException());
        try {
            throw new ValidationException(['Some Error1', 'Some Error2']);
        }
        catch(\Exception $e) {
            $res = $v->outputException($e);
            self::assertTrue(strpos($res[0], 'Some Error1') !== false);
            self::assertTrue(strpos($res[1], 'Some Error2') !== false);
        }
    }

    public function testOutputExceptionTraitReturnAsNotifyException() {
        $app = new App(['nologin'], ['always_run' => false]);
        $app->initLayout([Admin::class]);
        $v   = $app->add(new TestViewForOutputException());
        try {
            throw new ValidationException(['Some Error1', 'Some Error2']);
        }
        catch(\Exception $e) {
            $res = $v->outputExceptionAsJsNotify($e);
            self::assertEquals(2, count($res));
        }
    }
}