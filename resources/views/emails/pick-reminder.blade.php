<!DOCTYPE html>
<html>
<body style="font-family: system-ui, sans-serif; color:#222; font-size:16px; line-height:1.5;">
    <p>Hi {{ $user->name }},</p>

    <p>
        You have <strong>{{ $count }}</strong> outstanding {{ \Illuminate\Support\Str::plural('pick', $count) }}
        &mdash; days when it was your turn but no cause has been chosen yet.
    </p>

    <p>
        <a href="{{ $siteUrl }}"
           style="display:inline-block; padding:10px 18px; background:#27ae60; color:#fff; text-decoration:none; border-radius:6px;">
            Choose your causes
        </a>
    </p>

    <hr style="border:0; border-top:1px solid #ddd; margin-top:2rem;">
    <p style="font-size:13px; color:#777;">
        You'll get at most one of these reminders a week.
        <a href="{{ $unsubscribeUrl }}">Stop sending me reminders</a>.
    </p>
</body>
</html>
