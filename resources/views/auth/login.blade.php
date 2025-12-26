@extends('auth.layouts.app')

@section('section')
<div class="container min-vh-100 d-flex align-items-center">
    <div class="row w-100 justify-content-center">

        <div class="col-12 col-sm-10 col-md-6 col-lg-4">
            <form class="p-4 border rounded shadow bg-white" method="POST" route={{ 'login' }}>
                @csrf
                <h4 class="text-center mb-4">Login</h4>

                <div class="mb-3">
                    <label>Email address</label>
                    <input type="email" class="form-control" placeholder="Enter email" name="email">
                </div>

                <div class="mb-3">
                    <label>Password</label>
                    <input type="password" class="form-control" placeholder="Password" name="password">
                </div>

                <button class="btn btn-primary w-100">Login</button>

            </form>
        </div>
    </div>
</div>
@endsection
