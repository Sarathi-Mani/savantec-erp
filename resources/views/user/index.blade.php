@extends('layouts.admin')
@php
   // $profile=asset(Storage::url('uploads/avatar/'));
    $profile=\App\Models\Utility::get_file('uploads/avatar');
@endphp
@section('page-title')
    {{__('Manage User')}}
@endsection
@push('script-page')
@endpush
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item">{{__('User')}}</li>
@endsection
@section('action-btn')
    <div class="float-end">
        @if (\Auth::user()->type == 'company' || \Auth::user()->type == 'HR')
            <a href="{{ route('user.userlog') }}" class="btn btn-primary btn-sm {{ Request::segment(1) == 'user' }}"
                   data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('User Logs History') }}"><i class="ti ti-user-check"></i>
            </a>
        @endif
        @can('create user')
            <a href="{{ route('users.create') }}" data-bs-toggle="tooltip" title="{{__('Create')}}"  class="btn btn-sm btn-primary">
                <i class="ti ti-plus"></i>
            </a>
        @endcan
    </div>
@endsection
@section('content')
    <div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table datatable">
                <thead>
                    <tr>
                        <th>{{__('Name')}}</th>
                        <th>{{__('Email')}}</th>
                        <th>{{__('Type')}}</th>
                        <th>{{__('Last Login')}}</th>
                        <th>{{__('Status')}}</th>
                        @if(\Auth::user()->type == 'super admin')
                            <th>{{__('Plan')}}</th>
                            <th>{{__('Plan Expired')}}</th>
                        @endif
                        <th>{{__('Action')}}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <img src="{{(!empty($user->avatar))? asset(Storage::url("uploads/avatar/".$user->avatar)): asset(Storage::url("uploads/avatar/avatar.png"))}}" 
                                     class="rounded-circle me-2" width="30" height="30">
                                {{ $user->name }}
                            </div>
                        </td>
                        <td>{{ $user->email }}</td>
                        <td>
                            <div class="badge bg-primary p-2 px-3 rounded">
                                {{ ucfirst($user->type) }}
                            </div>
                        </td>
                        <td>{{ (!empty($user->last_login_at)) ? $user->last_login_at : '' }}</td>
                        <td>
                            @if($user->delete_status==0)
                                <span class="badge bg-danger">{{__('Inactive')}}</span>
                            @else
                                <span class="badge bg-success">{{__('Active')}}</span>
                            @endif
                        </td>
                        @if(\Auth::user()->type == 'super admin')
                            <td>{{!empty($user->currentPlan)?$user->currentPlan->name:''}}</td>
                            <td>{{!empty($user->plan_expire_date) ? \Auth::user()->dateFormat($user->plan_expire_date): __('Lifetime')}}</td>
                        @endif
                        <td>
                            @if(Gate::check('edit user') || Gate::check('delete user'))
                                @if($user->is_active == 1)
                                    <div class="d-flex">
                                        @can('edit user')
                                            <div class="action-btn bg-light-secondary ms-2">
                                                <a href="{{ route('users.edit', $user->id) }}" class="mx-3 btn btn-sm d-inline-flex align-items-center" data-bs-toggle="tooltip" title="{{__('Edit')}}">
                                                    <i class="ti ti-pencil text-dark"></i>
                                                </a>
                                            </div>
                                        @endcan

                                        @can('delete user')
                                            <div class="action-btn bg-light-secondary ms-2">
                                                {!! Form::open(['method' => 'DELETE', 'route' => ['users.destroy', $user['id']],'id'=>'delete-form-'.$user['id']]) !!}
                                                <a href="#!" class="mx-3 btn btn-sm d-inline-flex align-items-center bs-pass-para" data-bs-toggle="tooltip" title="@if($user->delete_status!=0){{__('Delete')}} @else {{__('Restore')}}@endif" data-confirm="{{__('Are You Sure?')}}" data-text="{{__('This action can not be undone. Do you want to continue?')}}" data-confirm-yes="delete-form-{{$user['id']}}">
                                                    <i class="ti ti-archive text-dark"></i>
                                                </a>
                                                {!! Form::close() !!}
                                            </div>
                                        @endcan

                                        <div class="action-btn bg-light-secondary ms-2">
                                            <a href="#!" data-url="{{route('users.reset',\Crypt::encrypt($user->id))}}" data-ajax-popup="true" data-size="md" class="mx-3 btn btn-sm d-inline-flex align-items-center" data-bs-toggle="tooltip" title="{{__('Reset Password')}}">
                                                <i class="ti ti-adjustments text-dark"></i>
                                            </a>
                                        </div>
                                    </div>
                                @else
                                    <a href="#" class="btn btn-sm btn-light-secondary"><i class="ti ti-lock"></i></a>
                                @endif
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Add click event for delete/restore buttons
    document.addEventListener('DOMContentLoaded', function() {
        const passButtons = document.querySelectorAll('.bs-pass-para');
        passButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const formId = this.getAttribute('data-confirm-yes');
                const confirmText = this.getAttribute('data-text');
                const confirmMessage = this.getAttribute('data-confirm');
                
                if (confirm(confirmMessage + '\n\n' + confirmText)) {
                    document.getElementById(formId).submit();
                }
            });
        });
    });
</script>
@endpush