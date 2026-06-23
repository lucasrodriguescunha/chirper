@props(['chirp'])
@php
    $message = (string) $chirp->message;
    $users = $chirp->mentionedUsers()->keyBy(fn ($u) => strtolower($u->username));

    $rendered = preg_replace_callback(
        \App\Models\Chirp::MENTION_REGEX,
        function ($m) use ($users) {
            $handle = strtolower($m[1]);
            $original = e($m[0]);

            if ($user = $users->get($handle)) {
                $url = e(route('users.show', $user));
                return '<a href="'.$url.'" class="link link-primary font-medium">'.$original.'</a>';
            }

            return $original;
        },
        e($message),
    );
@endphp

<p {{ $attributes->merge(['class' => 'mt-1 break-words']) }}>{!! $rendered !!}</p>
