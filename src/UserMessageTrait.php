<?php declare(strict_types=1);

namespace atkuitraits;

use atk4\ui\JsToast;

/**
 * usually added to app. Data layer can add messages to app which ui or other
 * can pick up and display
 */
trait UserMessageTrait
{

    public $userMessages = [];


    /*
     * This works as message storage. Data level can add messages here as well as
     * Ui. Ui can pick these messages and display to user
     */
    public function addUserMessage(string $message, string $class = '', int $displayTime = null)
    {
        $this->userMessages[] = ['message' => $message, 'class' => $class, 'displayTime' => $displayTime];
    }


    /*
     * renders messages as HTML.
     * Default is that FUI's .ui.message is used. If $inline is set to true,
     * it will use inline styling, e.g. for an Email where FUI CSS is not
     * available.
     */
    public function getUserMessagesAsHTML(bool $inline = false): string
    {
        $return = '';
        foreach ($this->userMessages as $message) {
            if ($inline) {
                $return .= '<div style="color:#' . $this->_getColorForUserMessageClass(
                        $message['class']
                    ) . '">' . $message['message'] . '</div>';
            } else {
                $return .= '<div class="ui message ' . $message['class'] . '">' . $message['message'] . '</div>';
            }
        }

        return $return;
    }


    /*
     * returns the messages as an array of jsExpressions opening a toast for
     * each message.
     * Usable e.g. in Form onSubmit returns
     */
    public function getUserMessagesAsJsToast(): array
    {
        $return = [];
        foreach ($this->userMessages as $message) {
            $return[] = new JsToast(
                [
                    'message' => $message['message'],
                    'position' => 'bottom right',
                    'class' => $message['class'],
                    'showProgress' => 'bottom',
                    'displayTime' => $message['displayTime'] ?? ($message['class'] == 'success' ? 3000 : 8000)
                ]
            );
        }

        return $return;
    }


    /*
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


    /*
     *
     */
    public function outputExceptionAsJsNotify(Throwable $e, string $text_before = ''): array
    {
        $return = [];
        foreach ($this->outputException($e, $text_before) as $message) {
            $return[] = $this->failNotify($message);
        }

        return $return;
    }


    /*
     *
     */
    public function outputException(Throwable $e, string $text_before = ''): array
    {
        $return = [];

        //ValidationException should render each message
        if ($e instanceof ValidationException) {
            //more than one field has bad value
            if (isset($e->errors)
                && is_array($e->errors)) {
                foreach ($e->errors as $error) {
                    $return[] = $text_before . ': ' . $error;
                }
            } //single error
            else {
                $return[] = $text_before . ': ' . $e->getMessage();
            }
        } //other exception meant for user
        elseif (
            $e instanceof UserException
            || $e instanceof \traitsforatkdata\UserException
        ) {
            $return[] = $text_before . ': ' . $e->getMessage();
        } //any other Exception renders as technical error
        else {
            $return[] = $text_before . ': Ein technischer Fehler ist aufgetreten. Bitte versuche es erneut. Der Administrator wurde informiert.';
        }

        return $return;
    }
}