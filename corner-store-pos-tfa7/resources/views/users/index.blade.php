@extends('layouts.app')

@section('title', 'User Accounts')

@section('content')
    <section class="page-heading heading-actions">
        <div>
            <p class="eyebrow">The team behind the counter</p>
        <h1>User Accounts</h1>
        <p class="lead">A quick look at the store team's names and usernames.</p>
        </div>
        <a class="button" href="{{ route('users.create') }}">Create User</a>
    </section>

    <section class="table-panel" aria-labelledby="user-list-heading">
        <div class="table-heading">
            <h2 id="user-list-heading">User directory</h2>
            <span class="record-count">{{ $users->count() }} records</span>
        </div>
        <div class="table-scroll" tabindex="0" role="region" aria-label="User directory table, scroll horizontally on small screens">
            <table>
                <caption class="sr-only">Store user accounts and usernames</caption>
                <thead>
                    <tr>
                        <th scope="col">ID</th>
                        <th scope="col">Profile picture</th>
                        <th scope="col">Full name</th>
                        <th scope="col">Username</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $user)
                        <tr>
                            <td class="account-id">U{{ str_pad($user->id, 3, '0', STR_PAD_LEFT) }}</td>
                            <td><img class="avatar" width="48" height="48" src="{{ $user->avatar ? asset('storage/'.$user->avatar) : asset('images/avatar-placeholder.svg') }}" alt="Profile picture for {{ $user->full_name }}"></td>
                            <th scope="row">{{ $user->full_name }}</th>
                            <td>{{ $user->username }}</td>
                            <td><a class="text-link" href="{{ route('users.edit', $user) }}" aria-label="Edit {{ $user->full_name }}">Edit</a></td>
                        </tr>
                    @endforeach

                    @if ($users->isEmpty())
                        <tr><td colspan="5">No user accounts found.</td></tr>
                    @endif
                </tbody>
            </table>
        </div>
        <p class="table-note">User accounts saved in the store directory.</p>
    </section>
@endsection
