@component('mail::message')
# {{ trans('email.newreply-header') }}: {{ $topic->name }}

**{{ trans('email.newreply-message') }}:** <a href="{{ route('profile', ['id' => $user->id]) }}">{{ $user->username }}</a> {{ strtolower(trans('email.newreply-replied')) }}
<a href="{{ route('forum_topic', ['id' => $topic->id]) }}">{{ $topic->name }}</a>

@component('mail::button', ['url' => route('forum_topic', ['id' => $topic->id]), 'color' => 'blue'])
{{ trans('email.newreply-view') }}
@endcomponent

@endcomponent
