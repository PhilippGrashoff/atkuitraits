<?php declare(strict_types=1);

namespace atkuitraits;

use Atk4\Data\ValidationException;
use Atk4\Ui\JsToast;
use Throwable;
use traitsforatkdata\UserException;

/**
 * This class can for example added to the single App instance. This way, any Views within the render tree can add
 * (small) Messages. These can be output as e.g. as Toasts both in full page rendering as well as in JS requests.
 */
class UserMessages
{

    protected array $messages = [];

    public $displayTimeSuccess = 3000;
    public $displayTimeWarning = 8000;
    public $displayTimeFailure = 10000;

    public string $defaultTextTechError = 'Ein technischer Fehler ist aufgetreten. Bitte versuche es erneut. Der Administrator wurde informiert.';


    protected function addMessage(string $message, string $class = ''): self
    {
        $this->messages[] = ['message' => $message, 'class' => $class];
        return $this;
    }

    public function addSuccessMessage(string $message): self
    {
        return $this->addMessage($message, 'success');
    }

    public function addWarningMessage(string $message): self
    {
        return $this->addMessage($message, 'warning');
    }

    public function addErrorMessage(string $message): self
    {
        return $this->addMessage($message, 'error');
    }

    /**
     * renders messages as HTML.
     * Default is that FUI's .ui.message is used.
     * If $inline is set to true,
     * it will use inline styling, e.g. for an Email where FUI CSS is not
     * available.
     */
    public function getAsHtml(bool $inline = false): string
    {
        $return = '';
        foreach ($this->messages as $message) {
            if ($inline) {
                $return .= '<div style="color:'
                    . $this->_getColorForUserMessageClass($message['class']) . '">' . $message['message'] . '</div>';
            } else {
                $return .= '<div class="ui message ' . $message['class'] . '">' . $message['message'] . '</div>';
            }
        }

        return $return;
    }


    /**
     * returns the messages as an array of jsExpressions opening a toast for each message.
     * Usable e.g. in Form onSubmit returns
     */
    public function getAsJsToasts(): array
    {
        $return = [];
        foreach ($this->messages as $message) {
            $return[] = new JsToast(
                [
                    'message' => $message['message'],
                    'position' => 'bottom right',
                    'class' => $message['class'],
                    'showProgress' => 'bottom',
                    'displayTime' => $this->getDisplayTimeForClass($message['class'])
                ]
            );
        }

        return $return;
    }

    protected function getDisplayTimeForClass(string $class): int
    {
        switch ($class) {
            case "success":
                return $this->displayTimeSuccess;
            case "warning":
                return $this->displayTimeWarning;
            case "error":
                return $this->displayTimeFailure;
        }

        return 3000;
    }

    /**
     * returns html color codes for different message classes for inline styling
     */
    protected function _getColorForUserMessageClass(string $class)
    {
        if ($class == 'success') {
            return '005723';
        } elseif ($class == 'warning') {
            return 'ff9900';
        } elseif ($class == 'error') {
            return 'dd0000';
        }

        //default black
        return '000000';
    }

    /**
     * Handy shortcut to catch an Exception and add it as error user Message
     */
    public function addException(Throwable $e, string $textBefore = ''): self
    {
        foreach ($this->outputException($e, $textBefore) as $messageText) {
            $this->addMessage($messageText, 'error');
        }

        return $this;
    }

    /**
     * Helper to handle different exception types differently
     */
    protected function outputException(Throwable $e, string $textBefore = ''): array
    {
        $return = [];

        //ValidationException should render each message
        if ($e instanceof ValidationException) {
            //more than one field has bad value
            if (
                isset($e->errors)
                && is_array($e->errors)
            ) {
                foreach ($e->errors as $error) {
                    $return[] = $textBefore . ': ' . $error;
                }
            } //single error
            else {
                $return[] = $textBefore . ': ' . $e->getMessage();
            }
        } //other exception meant for user
        elseif ($e instanceof UserException) {
            $return[] = $textBefore . ': ' . $e->getMessage();
        } //any other Exception renders as technical error
        else {
            $return[] = $textBefore . $this->defaultTextTechError;
        }

        return $return;
    }
}