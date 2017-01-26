<?php

namespace App\Http\Controllers;

use App\Models\NotificationPreference;
use App\Models\User;
use App\Http\Requests\User as Requests;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Instantiate a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function editSettings(Requests\Edit $request, User $user)
    {
        return redirect()->route('users.settings.account.edit', $user->id);
    }

    public function editSettingsAccount(Requests\Edit $request, User $user)
    {
        return view('users.settings.account', compact('user'));
    }

    public function editSettingsPassword(Requests\Edit $request, User $user)
    {
        return view('users.settings.password', compact('user'));
    }

    public function editSettingsNotifications(Requests\Edit $request, User $user)
    {
        // Retrieve all valid notifications for user
        $validNotifications = array_keys(NotificationPreference::getValidNotificationsForUser($user));

        // Retrieve all notification preferences that are set for user
        $currentNotifications = $user
            ->notificationPreferences()
            ->whereIn('event', $validNotifications)
            ->pluck('event')
            ->flip();

        // Build array of notifications and their selected status
        $notificationPreferences = [];
        foreach ($validNotifications as $notificationName) {
            $notificationPreferences[$notificationName] = isset($currentNotifications[$notificationName]);
        }

        return view('users.settings.notifications', compact('user', 'notificationPreferences'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateSettingsAccount(Requests\Settings\UpdateAccount $request, User $user)
    {
        $user->fname = $request->input('fname');
        $user->lname = $request->input('lname');
        $user->email = $request->input('email') ?: null;
        $user->address1 = $request->input('address1') ?: null;
        $user->address2 = $request->input('address2') ?: null;
        $user->city = $request->input('city') ?: null;
        $user->state = $request->input('state') ?: null;
        $user->postal_code = $request->input('postal_code') ?: null;
        $user->phone = $request->input('phone') ?: null;
        $user->url = $request->input('url') ?: null;

        if ($user->save()) {
            flash(trans('messages.updated'));
            return back();
        } else {
            flash(trans('messages.update_failed'));
            return back()->withInput();
        }
    }

    public function updateSettingsPassword(Requests\Settings\UpdatePassword $request, User $user)
    {
        if ($request->input('new_password')) {
            $user->password = $request->input('new_password');
        }

        if ($user->save()) {
            flash(trans('messages.updated'));
        } else {
            flash(trans('messages.update_failed'));
        }

        return back();
    }

    public function updateSettingsNotifications(Requests\Settings\UpdateNotifications $request, User $user)
    {
        $validNotifications = array_keys(NotificationPreference::getValidNotificationsForUser($user));

        foreach ($validNotifications as $notificationName) {
            $notificationParamName = str_replace('.', '_', $notificationName);
            $newValue = !empty($request->input($notificationParamName));

            // Grab this notification from the database
            $pref = $user
                ->notificationPreferences()
                ->where('event', $notificationName)
                ->first();

            // If we don't want that notification (and it exists), delete it
            if (!$newValue && !empty($pref)) {
                $pref->delete();
            } else {
                // If the entry doesn't already exist, create it.
                if (!isset($pref)) {
                    $user->notificationPreferences()->create([
                        'event' => $notificationName,
                        'type' => NotificationPreference::TYPE_EMAIL,
                    ]);
                }
                // Otherwise, ignore (there was no change)
            }
        }

        flash(trans('messages.updated'));
        return back();
    }
}
