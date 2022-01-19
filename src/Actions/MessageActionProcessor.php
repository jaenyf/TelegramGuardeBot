<?php

namespace TelegramGuardeBot\Actions;

use TelegramGuardeBot\Actions\LogToFileMessageAction;

/**
 * Process an update through the appropriate MessageAction
 */
class MessageActionProcessor
{
    public function process($messagesActions, $update)
    {
        foreach($messagesActions as $messageAction)
        {
            switch (strtolower($messageAction->action))
            {
                case 'logtofile':
                    if(!isset($messageAction->chatId) || (isset($update->message) && isset($update->message->chat) && isset($update->message->chat->id) && $update->message->chat->id == $messageAction->chatId))
                    {
                        switch(strtolower($messageAction->onType))
                        {
                            case 'message':
                                $messageAction = new LogToFileMessageAction($messageAction->fileName);
                                $messageAction->act($update->message->text);
                                break;
                        }
                    }
                    break;
            }
        }
    }
}
