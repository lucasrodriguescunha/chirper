@props(['chirp'])
@php
    $message = (string) $chirp->message;
    $users = $chirp->mentionedUsers()->keyBy(fn ($u) => strtolower($u->username));

    $rendered = e($message);

    $rendered = preg_replace_callback(
        \App\Models\Chirp::HASHTAG_REGEX,
        function ($m) {
            $slug = strtolower($m[1]);
            $url = e(route('tags.show', $slug));
            $label = e($m[0]);

            return '<a href="'.$url.'" class="link link-secondary font-medium">'.$label.'</a>';
        },
        $rendered,
    );

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
        $rendered,
    );
@endphp

<p {{ $attributes->merge(['class' => 'mt-1 break-words']) }}>{!! $rendered !!}</p>
