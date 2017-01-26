<?php

namespace App\Listeners;

use App\Events\CommentCreated;
use App\Models\Annotation;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class CommentCreatedNotification implements ShouldQueue
{
    /**
     * Handle the event.
     *
     * @param  CommentCreated  $event
     * @return void
     */
    public function handle(CommentCreated $event)
    {
        // TODO: not working currently
        // if the comment is in reply to something, signal a notification to
        // the parent user
        // if ($event->parent instanceof Annotation) {
        //     if ($event->parent->isNote()) {
        //         $parentType = 'note';
        //     } else {
        //         $parentType = 'comment';
        //     }

        //     $data = [
        //         'subcomment' => $event->comment,
        //         'parent' => $event->parent,
        //         'parentType' => $parentType,
        //     ];
        //     $recipient = $event->parent->user;

        //     $this->notifier->queue('notification.comment.reply-html', $data, function ($message) use ($recipient) {
        //         $message->setSubject('Activity on something of yours');
        //         $message->setRecipients($recipient);
        //     }, $event);
        // }
    }
}
